<?php
/*
    Original Source Url:
        https://github.com/openai/gym/blob/master/gym/envs/classic_control/pendulum.py

        
from gym.utils import seeding
*/

namespace Rindow\RL\Gym\ClassicControl\Pendulum;

use RuntimeException;
use InvalidArgumentException;
use LogicException;
use Interop\Polite\Math\Matrix\NDArray;
use Rindow\RL\Gym\Core\AbstractEnv;
use Rindow\RL\Gym\Core\Spaces\Discrete;
use Rindow\RL\Gym\Core\Spaces\Box;
use Rindow\RL\Gym\ClassicControl\Rendering\RenderFactory;
use Rindow\RL\Gym\ClassicControl\Rendering\Transform;
use Rindow\RL\Gym\ClassicControl\Rendering\Image;


class PendulumEnv extends AbstractEnv
{
    protected array $metadata = ["render.modes"=> ["human", "rgb_array"], "video.frames_per_second"=> 30];

    protected float $max_speed = 8;
    protected float $max_torque = 2.0;
    protected float $dt = 0.05;
    protected float $m = 1.0;
    protected float $l = 1.0;
    protected float $g;
    /** @var array<float> $state */
    protected array $state;
    protected ?object $renderingFactory;
    protected ?float $last_u;
    protected Transform $pole_transform;
    protected Image $img;
    protected Transform $imgtrans;

    /**
     * @param array<string,mixed> $metadata
     */
    public function __construct(
        object $la,
        ?float $g=null,
        ?array $metadata=null,
        ?object $renderer=null)
    {
        $g ??= 10.0;
        parent::__construct($la);
        if($metadata) {
            $this->mergeMetadata($metadata);
        }
        if($renderer===null) {
            $renderer = new RenderFactory($la,'gd',$this->metadata);
        }
        $this->renderingFactory = $renderer;
        $this->g = $g;

        $this->setActionSpace(new Box($la,
            -$this->max_torque, $this->max_torque, shape:[], dtype:NDArray::float32
        ));
        $high = $la->array([1.0, 1.0, $this->max_speed], dtype:NDArray::float32);
        $low = $la->scal(-1,$la->copy($high));
        $this->setObservationSpace(new Box($la, $low, $high));

        $this->seed();
    }

    public function doStep(NDArray $action) : array
    {
        $u = $this->la->scalar($action);
        [$th, $thdot] = $this->state;  # th := theta

        $g = $this->g;
        $m = $this->m;
        $l = $this->l;
        $dt = $this->dt;

        $u = min(max($u, -$this->max_torque), $this->max_torque);
        $this->last_u = $u;  # for rendering
        $costs = $this->angle_normalize($th) ** 2 + 0.1 * $thdot ** 2 + 0.001 * ($u ** 2);

        $newthdot = $thdot + (3 * $g / (2 * $l) * sin($th) + 3.0 / ($m * $l ** 2) * $u) * $dt;
        $newthdot = min(max($newthdot, -$this->max_speed), $this->max_speed);
        $newth = $th + $newthdot * $dt;

        $this->state = [$newth, $newthdot];
        return [$this->get_obs(), -$costs, false, []];
    }

    public function doReset() : NDArray
    {
        $la = $this->la;
        $theta = $la->randomUniform([],-M_PI,M_PI);
        $thetadot = $la->randomUniform([],-1,1);
        $this->state = [$la->scalar($theta),$la->scalar($thetadot)];
        $this->last_u = null;
        return $this->get_obs();
    }

    protected function get_obs() : NDArray
    {
        $la = $this->la;
        [$theta, $thetadot] = $this->state;
        return $la->array([cos($theta), sin($theta), $thetadot], dtype:NDArray::float32);
    }

    public function render(?string $mode=null) : mixed
    {
        $mode ??= "human";
        if($this->viewer === null) {
            $rendering = $this->renderingFactory->factory();
            $this->viewer = $rendering->Viewer(500, 500);
            $this->viewer->set_bounds(-2.2, 2.2, -2.2, 2.2);
            $rod = $rendering->make_capsule(1, 0.2);
            $rod->set_color(0.8, 0.3, 0.3);
            $this->pole_transform = $rendering->Transform();
            $rod->add_attr($this->pole_transform);
            $this->viewer->add_geom($rod);
            $axle = $rendering->make_circle(0.05);
            $axle->set_color(0, 0, 0);
            $this->viewer->add_geom($axle);
            $fname = __DIR__."/assets/clockwise.png";
            $this->img = $rendering->Image($fname, 1.0, 1.0);
            $this->imgtrans = $rendering->Transform();
            $this->img->add_attr($this->imgtrans);
        }
        $this->viewer->add_onetime($this->img);
        $this->pole_transform->set_rotation($this->state[0] + M_PI / 2);
        if($this->last_u !== null) {
            $this->imgtrans->set_scale(-$this->last_u / 2, abs($this->last_u) / 2);
        }

        return $this->viewer->render($mode);
    }

    public function fname() : string
    {
        return $fname = __DIR__."/assets/clockwise.png";
    }

    public function angle_normalize(float $x) : float
    {
        return $this->remainder(($x + M_PI) , (2 * M_PI)) - M_PI;
    }
}
