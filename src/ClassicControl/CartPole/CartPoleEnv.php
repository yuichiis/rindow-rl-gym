<?php
/*
Classic cart-pole system implemented by Rich Sutton et al.
Copied from http://incompleteideas.net/sutton/book/code/pole.c
permalink: https://perma.cc/C9ZM-652R

Original Source Url:
    https://github.com/openai/gym/blob/master/gym/envs/classic_control/cartpole.py

Description:
    A pole is attached by an un-actuated joint to a cart, which moves along
    a frictionless track. The pendulum starts upright, and the goal is to
    prevent it from falling over by increasing and reducing the cart's
    velocity.

Source:
    This environment corresponds to the version of the cart-pole problem
    described by Barto, Sutton, and Anderson

Observation:
    Type: Box(4)
    Num     Observation               Min                     Max
    0       Cart Position             -4.8                    4.8
    1       Cart Velocity             -Inf                    Inf
    2       Pole Angle                -0.418 rad (-24 deg)    0.418 rad (24 deg)
    3       Pole Angular Velocity     -Inf                    Inf

Actions:
    Type: Discrete(2)
    Num   Action
    0     Push cart to the left
    1     Push cart to the right

    Note: The amount the velocity that is reduced or increased is not
    fixed; it depends on the angle the pole is pointing. This is because
    the center of gravity of the pole increases the amount of energy needed
    to move the cart underneath it

Reward:
    Reward is 1 for every step taken, including the termination step

Starting State:
    All observations are assigned a uniform random value in [-0.05..0.05]

Episode Termination:
    Pole Angle is more than 12 degrees.
    Cart Position is more than 2.4 (center of the cart reaches the edge of
    the display).
    Episode length is greater than 200.
    Solved Requirements:
    Considered solved when the average return is greater than or equal to
    195.0 over 100 consecutive trials.

*/

namespace Rindow\RL\Gym\ClassicControl\CartPole;

use RuntimeException;
use Interop\Polite\Math\Matrix\NDArray;
use Rindow\RL\Gym\Core\AbstractEnv;
use Rindow\RL\Gym\Core\Spaces\Discrete;
use Rindow\RL\Gym\Core\Spaces\Box;
use Rindow\RL\Gym\ClassicControl\Rendering\RenderFactory;

class CartPoleEnv extends AbstractEnv
{
    protected $metadata = ["render.modes"=> ["human", "rgb_array"], "video.frames_per_second"=> 50];

    protected $gravity = 9.8;
    protected $masscart = 1.0;
    protected $masspole = 0.1;
    protected $length = 0.5; # actually half the pole's length
    protected $force_mag = 10.0;
    protected $tau = 0.02;  # seconds between state updates;
    protected $kinematics_integrator = "euler";
    protected $total_mass;
    protected $polemass_length;
    protected $theta_threshold_radians;
    protected $x_threshold;
    protected $state;
    protected $steps_beyond_done;

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
        $this->total_mass = $this->masspole + $this->masscart;
        $this->polemass_length = $this->masspole * $this->length;

        # Angle at which to fail the episode
        $this->theta_threshold_radians = 12 * 2 * M_PI / 360;
        $this->x_threshold = 2.4;

        # Angle limit set to 2 * theta_threshold_radians so failing observation
        # is still within bounds.
        $high = $la->array(
            [
                $this->x_threshold * 2,
                INF,
                $this->theta_threshold_radians * 2,
                INF,
            ],
            NDArray::float32,
        );
        $min = $la->scal(-1.0,$la->copy($high));
        $this->setObservationSpace(new Box($la,$min,$high));
        $this->setActionSpace(new Discrete($la,2));

        $this->seed();
        $this->viewer = null;
        $this->state = null;

        $this->steps_beyond_done = null;
    }

    /**
    * @param Any $action
    * @return Set(Any $observation, Any $rewards, bool $done, Dict $info)
    */
    protected function doStep($action) : array
    {
        //err_msg = "%r (%s) invalid" % (action, type(action))
        //assert self.action_space.contains(action), err_msg

        [$x, $x_dot, $theta, $theta_dot] = $this->state;
        $force = ($action == 1) ? ($this->force_mag) : (-$this->force_mag);
        $costheta = cos($theta);
        $sintheta = sin($theta);

        # For the interested reader:
        # https://coneural.org/florian/papers/05_cart_pole.pdf
        $temp = (
            $force + $this->polemass_length * $theta_dot ** 2 * $sintheta
        ) / $this->total_mass;
        $thetaacc = ($this->gravity * $sintheta - $costheta * $temp) / (
            $this->length * (4.0 / 3.0 - $this->masspole * $costheta ** 2 / $this->total_mass)
        );
        $xacc = $temp - $this->polemass_length * $thetaacc * $costheta / $this->total_mass;

        if($this->kinematics_integrator == "euler") {
            $x = $x + $this->tau * $x_dot;
            $x_dot = $x_dot + $this->tau * $xacc;
            $theta = $theta + $this->tau * $theta_dot;
            $theta_dot = $theta_dot + $this->tau * $thetaacc;
        } else {  # semi-implicit euler
            $x_dot = $x_dot + $this->tau * $xacc;
            $x = $x + $this->tau * $x_dot;
            $theta_dot = $theta_dot + $this->tau * $thetaacc;
            $theta = $theta + $this->tau * $theta_dot;
        }

        $this->state = [$x, $x_dot, $theta, $theta_dot];

        $done =
            $x < -$this->x_threshold
            || $x > $this->x_threshold
            || $theta < -$this->theta_threshold_radians
            || $theta > $this->theta_threshold_radians;

        if(!$done) {
            $reward = 1.0;
        } elseif($this->steps_beyond_done === null) {
            # Pole just fell!
            $this->steps_beyond_done = 0;
            $reward = 1.0;
        } else {
            if($this->steps_beyond_done == 0) {
                throw new RuntimeException(
                    "You are calling 'step()' even though this ".
                    "environment has already returned done = True. You ".
                    "should always call 'reset()' once you receive 'done = ".
                    "True' -- any further steps are undefined behavior."
                );
            }
            $this->steps_beyond_done += 1;
            $reward = 0.0;
        }
        return [$this->la->array($this->state, NDArray::float32),
                $reward, $done, []];
    }

    /**
    * @return Any $observation
    **/
    protected function doReset()
    {
        $la = $this->la;
        $this->state = $la->randomUniform([4],$low=-0.05, $high=0.05);
        $this->steps_beyond_done = null;
        return $this->state;
    }

    public function render($mode="human") : mixed
    {
        $screen_width = 600;
        $screen_height = 400;

        $world_width = $this->x_threshold * 2;
        $scale = $screen_width / $world_width;
        $carty = 100;  # TOP OF CART;
        $polewidth = 10.0;
        $polelen = $scale * (2 * $this->length);
        $cartwidth = 50.0;
        $cartheight = 30.0;

        if($this->viewer===null)
        {
            $rendering = $this->renderingFactory->factory();
            $this->viewer = $rendering->Viewer($screen_width, $screen_height);
            [$l, $r, $t, $b] = [-$cartwidth / 2, $cartwidth / 2, $cartheight / 2, -$cartheight / 2];
            $axleoffset = $cartheight / 4.0;
            $cart = $rendering->FilledPolygon([[$l, $b], [$l, $t], [$r, $t], [$r, $b]]);
            $this->carttrans = $rendering->Transform();
            $cart->add_attr($this->carttrans);
            $this->viewer->add_geom($cart);
            [$l, $r, $t, $b] = [
                -$polewidth / 2,
                $polewidth / 2,
                $polelen - $polewidth / 2,
                -$polewidth / 2,
            ];
            $pole = $rendering->FilledPolygon([[$l, $b], [$l, $t], [$r, $t], [$r, $b]]);
            $pole->set_color(0.8, 0.6, 0.4);
            $this->poletrans = $rendering->Transform([0, $axleoffset]);
            $pole->add_attr($this->poletrans);
            $pole->add_attr($this->carttrans);
            $this->viewer->add_geom($pole);
            $this->axle = $rendering->make_circle($polewidth / 2);
            $this->axle->add_attr($this->poletrans);
            $this->axle->add_attr($this->carttrans);
            $this->axle->set_color(0.5, 0.5, 0.8);
            $this->viewer->add_geom($this->axle);
            $this->track = $rendering->Line([0, $carty], [$screen_width, $carty]);
            $this->track->set_color(0, 0, 0);
            $this->viewer->add_geom($this->track);

            $this->pole_geom = $pole;
        }

        if($this->state === null) {
            return null;
        }

        # Edit the pole polygon vertex
        $pole = $this->pole_geom;
        [$l, $r, $t, $b] = [
            -$polewidth / 2,
            $polewidth / 2,
            $polelen - $polewidth / 2,
            -$polewidth / 2,
        ];
        $pole->v = [[$l, $b], [$l, $t], [$r, $t], [$r, $b]];

        $x = $this->state;
        $cartx = $x[0] * $scale + $screen_width / 2.0;  # MIDDLE OF CART
        $this->carttrans->set_translation($cartx, $carty);
        $this->poletrans->set_rotation(-$x[2]);

        return $this->viewer->render($mode);
    }
}
