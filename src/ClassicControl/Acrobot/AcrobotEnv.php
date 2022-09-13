<?php
/*
"""classic Acrobot task"""
    Acrobot is a 2-link pendulum with only the second joint actuated.
    Initially, both links point downwards. The goal is to swing the
    end-effector at a height at least the length of one link above the base.
    Both links can swing freely and can pass by each other, i.e., they don't
    collide when they have the same angle.
    **STATE:**
    The state consists of the sin() and cos() of the two rotational joint
    angles and the joint angular velocities :
    [cos(theta1) sin(theta1) cos(theta2) sin(theta2) thetaDot1 thetaDot2].
    For the first link, an angle of 0 corresponds to the link pointing downwards.
    The angle of the second link is relative to the angle of the first link.
    An angle of 0 corresponds to having the same angle between the two links.
    A state of [1, 0, 1, 0, ..., ...] means that both links point downwards.
    **ACTIONS:**
    The action is either applying +1, 0 or -1 torque on the joint between
    the two pendulum links.
    .. note::
        The dynamics equations were missing some terms in the NIPS paper which
        are present in the book. R. Sutton confirmed in personal correspondence
        that the experimental results shown in the paper and the book were
        generated with the equations shown in the book.
        However, there is the option to run the domain with the paper equations
        by setting book_or_nips = 'nips'
    **REFERENCE:**
    .. seealso::
        R. Sutton: Generalization in Reinforcement Learning:
        Successful Examples Using Sparse Coarse Coding (NIPS 1996)
    .. seealso::
        R. Sutton and A. G. Barto:
        Reinforcement learning: An introduction.
        Cambridge: MIT press, 1998.
    .. warning::
        This version of the domain uses the Runge-Kutta method for integrating
        the system dynamics and is more realistic, but also considerably harder
        than the original version which employs Euler integration,
        see the AcrobotLegacy class.

__copyright__ = "Copyright 2013, RLPy http://acl.mit.edu/RLPy"
__credits__ = [
    "Alborz Geramifard",
    "Robert H. Klein",
    "Christoph Dann",
    "William Dabney",
    "Jonathan P. How",
]
__license__ = "BSD 3-Clause"
__author__ = "Christoph Dann <cdann@cdann.de>"

# SOURCE:
# https://github.com/rlpy/rlpy/blob/master/rlpy/Domains/Acrobot.py

*/
namespace Rindow\RL\Gym\ClassicControl\Acrobot;

use RuntimeException;
use Interop\Polite\Math\Matrix\NDArray;
use Rindow\RL\Gym\Core\AbstractEnv;
use Rindow\RL\Gym\Core\Spaces\Discrete;
use Rindow\RL\Gym\Core\Spaces\Box;
use Rindow\RL\Gym\ClassicControl\Rendering\RenderFactory;

class AcrobotEnv extends AbstractEnv
{
    protected $metadata = ["render.modes"=> ["human", "rgb_array"], "video.frames_per_second"=> 15];

    public $dt = 0.2;
    const LINK_LENGTH_1 = 1.0;  # [m]
    const LINK_LENGTH_2 = 1.0;  # [m]
    const LINK_MASS_1 = 1.0;    #: [kg] mass of link 1
    const LINK_MASS_2 = 1.0;    #: [kg] mass of link 2
    const LINK_COM_POS_1 = 0.5; #: [m] position of the center of mass of link 1
    const LINK_COM_POS_2 = 0.5; #: [m] position of the center of mass of link 2
    const LINK_MOI = 1.0;       #: moments of inertia for both links

    const MAX_VEL_1 = 4 * M_PI;
    const MAX_VEL_2 = 9 * M_PI;

    protected $AVAIL_TORQUE = [-1.0, 0.0, +1];

    public $torque_noise_max = 0.0;

    #: use dynamics equations from the nips paper or the book
    public $book_or_nips = "book";
    public $action_arrow = null;
    public $domain_fig = null;
    public $actions_num = 3;

    protected $state;

    protected $renderingFactory;

    public function __construct(object $la, array $metadata=null, object $renderer=null)
    {
        parent::__construct($la);
        if($metadata) {
            $this->mergeMetadata($metadata);
        }
        if($renderer===null) {
            $renderer = new RenderFactory($la,'gd',$this->metadata);
        }
        $this->renderingFactory = $renderer;
        $high = $la->array(
            [1.0, 1.0, 1.0, 1.0, self::MAX_VEL_1, self::MAX_VEL_2], NDArray::float32
        );
        $low = $la->scal(-1.0,$la->copy($high));
        $this->setObservationSpace(new Box($la,$low,$high));
        $this->setActionSpace(new Discrete($la,2));
        $this->state = null;
        $this->seed();
    }

    /**
    * @return Any $observation
    **/
    protected function doReset()
    {
        $la = $this->la;
        $this->state = $la->randomUniform([4],$low=-0.1, $high=0.1);
        return $this->get_ob();
    }

    /**
    * @param Any $action
    * @return Set(Any $observation, Any $rewards, bool $done, Dict $info)
    */
    public function doStep($a) : array
    {
        $la = $this->la;
        $s = $this->state;
        $torque = $this->AVAIL_TORQUE[$a];

        # Add noise to the force action
        if($this->torque_noise_max > 0) {
            $torque += $la->randomUniform([1],
                -$this->torque_noise_max, $this->torque_noise_max
            )[0];
        }
        # Now, augment the state with our force action so it can be passed to
        # _dsdt
        
        // append torque to s
        $slen = count($s);
        $s_augmented = $la->alloc([$slen+1]);
        $la->copy($s,$s_augmented[[0,$slen-1]]);
        $s_augmented[$slen] = $torque;

        $ns = $this->rk4([$this,'_dsdt'], $s_augmented, [0, $this->dt]);

        $ns[0] = $this->wrap($ns[0], -M_PI, M_PI);
        $ns[1] = $this->wrap($ns[1], -M_PI, M_PI);
        $ns[2] = $this->bound($ns[2], -self::MAX_VEL_1, self::MAX_VEL_1);
        $ns[3] = $this->bound($ns[3], -self::MAX_VEL_2, self::MAX_VEL_2);
        $this->state = $ns;
        $terminal = $this->terminal();
        $reward = ($terminal)? 0.0 : -1.0;
        return [$this->get_ob(), $reward, $terminal, []];
    }

    protected function get_ob()
    {
        $la = $this->la;
        $s = $this->state;
        return $la->array(
            [cos($s[0]), sin($s[0]), cos($s[1]), sin($s[1]), $s[2], $s[3]], NDArray::float32
        );
    }

    protected function terminal() : bool
    {
        $s = $this->state;
        return (-cos($s[0]) - cos($s[1] + $s[0]) > 1.0);
    }

    public function _dsdt($s_augmented)
    {
        $la = $this->la;
        $m1 = self::LINK_MASS_1;
        $m2 = self::LINK_MASS_2;
        $l1 = self::LINK_LENGTH_1;
        $lc1 = self::LINK_COM_POS_1;
        $lc2 = self::LINK_COM_POS_2;
        $I1 = self::LINK_MOI;
        $I2 = self::LINK_MOI;
        $g = 9.8;
        $a = $s_augmented[4];
        $s = $la->copy($s_augmented[[0,3]]);
        $theta1 = $s[0];
        $theta2 = $s[1];
        $dtheta1 = $s[2];
        $dtheta2 = $s[3];
        $d1 = (
            $m1 * $lc1 ** 2
            + $m2 * ($l1 ** 2 + $lc2 ** 2 + 2 * $l1 * $lc2 * cos($theta2))
            + $I1
            + $I2
        );
        $d2 = $m2 * ($lc2 ** 2 + $l1 * $lc2 * cos($theta2)) + $I2;
        $phi2 = $m2 * $lc2 * $g * cos($theta1 + $theta2 - M_PI / 2.0);
        $phi1 = (
            -$m2 * $l1 * $lc2 * $dtheta2 ** 2 * sin($theta2)
            - 2 * $m2 * $l1 * $lc2 * $dtheta2 * $dtheta1 * sin($theta2)
            + ($m1 * $lc1 + $m2 * $l1) * $g * cos($theta1 - M_PI / 2)
            + $phi2
        );
        if($this->book_or_nips == "nips") {
            # the following line is consistent with the description in the
            # paper
            $ddtheta2 = ($a + $d2 / $d1 * $phi1 - $phi2) / ($m2 * $lc2 ** 2 + $I2 - $d2 ** 2 / $d1);
        } else {
            # the following line is consistent with the java implementation and the
            # book
            $ddtheta2 = (
                $a + $d2 / $d1 * $phi1 - $m2 * $l1 * $lc2 * $dtheta1 ** 2 * sin($theta2) - $phi2
            ) / ($m2 * $lc2 ** 2 + $I2 - $d2 ** 2 / $d1);
        }
        $ddtheta1 = -($d2 * $ddtheta2 + $phi1) / $d1;
        return [$dtheta1, $dtheta2, $ddtheta1, $ddtheta2, 0.0];
    }

    public function render($mode="human") : mixed
    {
        //from gym.envs.classic_control import rendering
        $la = $this->la;

        $s = $this->state;

        if($this->viewer===null) {
            $rendering = $this->renderingFactory->factory();
            $this->viewer = $rendering->Viewer(500, 500);
            $bound = self::LINK_LENGTH_1 + self::LINK_LENGTH_2 + 0.2;  # 2.2 for default
            $this->viewer->set_bounds(-$bound, $bound, -$bound, $bound);
        } else {
            $rendering = $this->viewer->rendering();
        }

        if($s===null) {
            return null;
        }

        $p1 = [
             self::LINK_LENGTH_1 * sin($s[0]),  // y
            -self::LINK_LENGTH_1 * cos($s[0]),  // x
        ];

        $p2 = [
            $p1[0] + self::LINK_LENGTH_2 * sin($s[0] + $s[1]), // y
            $p1[1] - self::LINK_LENGTH_2 * cos($s[0] + $s[1]), // x
        ];

        $xys = [[0, 0], $p1];  //, $p2];
        $thetas = [$s[0] - M_PI / 2, $s[0] + $s[1] - M_PI / 2];
        $link_lengths = [self::LINK_LENGTH_1, self::LINK_LENGTH_2];

        $this->viewer->draw_line([-2.2, 1], [2.2, 1]);
        foreach(array_map(null,$xys, $thetas, $link_lengths) as [[$x, $y], $th, $llen]) {
            [$l, $r, $t, $b] = [0, $llen, 0.1, -0.1];
            $jtransform = $rendering->Transform(rotation:$th, translation:[$x, $y]);
            $link = $this->viewer->draw_polygon([[$l, $b], [$l, $t], [$r, $t], [$r, $b]]);
            $link->add_attr($jtransform);
            $link->set_color(0, 0.8, 0.8);
            $circ = $this->viewer->draw_circle(0.1);
            $circ->set_color(0.8, 0.8, 0);
            $circ->add_attr($jtransform);
        }

        return $this->viewer->render($mode);
    }

    /*
        """Wraps ``x`` so m <= x <= M; but unlike ``bound()`` which
        truncates, ``wrap()`` wraps x around the coordinate system defined by m,M.\n
        For example, m = -180, M = 180 (degrees), x = 360 --> returns 0.

        Args:
            x: a scalar
            m: minimum possible value in range
            M: maximum possible value in range

        Returns:
            x: a scalar, wrapped
        """
    */
    public function wrap($x, $m, $M)
    {
        $diff = $M - $m;
        while($x > $M) {
            $x = $x - $diff;
        }
        while($x < $m) {
            $x = $x + $diff;
        }
        return $x;
    }

    /*
        """Either have m as scalar, so bound(x,m,M) which returns m <= x <= M *OR*
        have m as length 2 vector, bound(x,m, <IGNORED>) returns m[0] <= x <= m[1].

        Args:
            x: scalar

        Returns:
            x: scalar, bound between min (m) and Max (M)
        """
     */
    public function bound($x, $m, $M=null)
    {
        if($M===null) {
            $M = $m[1];
            $m = $m[0];
        }
        # bound x between min (m) and Max (M)
        return min(max($x, $m), $M);
    }

    /*
    """
    Integrate 1D or ND system of ODEs using 4-th order Runge-Kutta.
    This is a toy implementation which may be useful if you find
    yourself stranded on a system w/o scipy.  Otherwise use
    :func:`scipy.integrate`.

    Args:
        derivs: the derivative of the system and has the signature ``dy = derivs(yi)``
        y0: initial state vector
        t: sample times
        args: additional arguments passed to the derivative function
        kwargs: additional keyword arguments passed to the derivative function

    Example 1 ::
        ## 2D system
        def derivs(x):
            d1 =  x[0] + 2*x[1]
            d2 =  -3*x[0] + 4*x[1]
            return (d1, d2)
        dt = 0.0005
        t = arange(0.0, 2.0, dt)
        y0 = (1,2)
        yout = rk4(derivs6, y0, t)

    If you have access to scipy, you should probably be using the
    scipy.integrate tools rather than this function.
    This would then require re-adding the time variable to the signature of derivs.

    Returns:
        yout: Runge-Kutta approximation of the ODE
    """
    */
    public function rk4(callable $derivs, NDArray|float $y0, array $t) : NDArray
    {
        $la = $this->la;
        if(is_numeric($y0)) {
            $yout = $la->alloc([count($t), 1], NDArray::float32);
            $yout[0][0] = $y0;
        } else {
            $yout = $la->alloc([count($t), count($y0)], NDArray::float32);
            $yout[0] = $y0;
        }

        $tlen = count($t)-1;
        for($i=0;$i<$tlen;$i++) {
            $thist = $t[$i];
            $dt = $t[$i + 1] - $thist;
            $dt2 = $dt / 2.0;
            $y0 = $yout[$i];

            $k1 = $la->array($derivs($y0));
            $k2 = $la->array($derivs($la->axpy($k1, $la->copy($y0) ,$dt2)));
            $k3 = $la->array($derivs($la->axpy($k2, $la->copy($y0) ,$dt2)));
            $k4 = $la->array($derivs($la->axpy($k3, $la->copy($y0) ,$dt)));

            //$yout[$i + 1] = $y0 + $dt / 6.0 * ($k1 + 2 * $k2 + 2 * $k3 + $k4);
            $k = $la->axpy($k4,$la->axpy($k3,$la->axpy($k2,$la->copy($k1),2),2));
            $yout[$i+1] = $la->axpy($k,$la->copy($y0),$dt/6.0);
        }
        # We only care about the final timestep and we cleave off action value which will be zero
        #return yout[-1][:4]
        $r = $yout[count($yout)-1][[0,3]];
        return $r;
    }
}
