<?php
/*
    http://incompleteideas.net/MountainCar/MountainCar1.cp
    permalink: https://perma.cc/6Z2N-PFWC

    Original Source Url:
        https://github.com/openai/gym/blob/master/gym/envs/classic_control/mountain_car.py

    Description:
        The agent (a car) is started at the bottom of a valley. For any given
        state the agent may choose to accelerate to the left, right or cease
        any acceleration.

    Source:
        The environment appeared first in Andrew Moore's PhD Thesis (1990).

    Observation:
        Type: Box(2)
        Num    Observation               Min            Max
        0      Car Position              -1.2           0.6
        1      Car Velocity              -0.07          0.07

    Actions:
        Type: Discrete(3)
        Num    Action
        0      Accelerate to the Left
        1      Don't accelerate
        2      Accelerate to the Right

        Note: This does not affect the amount of velocity affected by the
        gravitational pull acting on the car.

    Reward:
         Reward of 0 is awarded if the agent reached the flag (position = 0.5)
         on top of the mountain.
         Reward of -1 is awarded if the position of the agent is less than 0.5.

    Starting State:
         The position of the car is assigned a uniform random value in
         [-0.6 , -0.4].
         The starting velocity of the car is always assigned to 0.

    Episode Termination:
         The car position is more than 0.5
         Episode length is greater than 200

*/

namespace Rindow\RL\Gym\ClassicControl\MountainCar;

use RuntimeException;
use InvalidArgumentException;
use LogicException;
use Interop\Polite\Math\Matrix\NDArray;
use Rindow\RL\Gym\Core\AbstractEnv;
use Rindow\RL\Gym\Core\Spaces\Discrete;
use Rindow\RL\Gym\Core\Spaces\Box;
use Rindow\RL\Gym\ClassicControl\Rendering\RenderFactory;

class MountainCarEnv extends AbstractEnv
{
    protected $metadata = ["render.modes"=> ["human", "rgb_array"], "video.frames_per_second"=> 30];

    protected $min_position = -1.2;
    protected $max_position = 0.6;
    protected $max_speed = 0.07;
    protected $goal_position = 0.5;
    protected $force = 0.001;
    protected $gravity = 0.0025;
    protected $goal_velocity;
    protected $low;
    protected $high;
    protected $state;

    public function __construct(object $la, int $goal_velocity=0, array $metadata=null, object $renderer=null)
    {
        parent::__construct($la);
        if($metadata) {
            $this->mergeMetadata($metadata);
        }
        if($renderer===null) {
            $renderer = new RenderFactory($la,'gd',$this->metadata);
        }
        $this->renderingFactory = $renderer;
        $this->goal_velocity = $goal_velocity;
        $this->low = $la->array([$this->min_position, -$this->max_speed], NDArray::float32);
        $this->high = $la->array([$this->max_position, $this->max_speed], NDArray::float32);
        $this->setActionSpace(new Discrete($la, 3));
        $this->setObservationSpace(new Box($la, $this->low, $this->high, [2], NDArray::float32));
        $this->seed();
    }

    /**
    * @param Any $action
    * @return Set(Any $observation, Any $rewards, bool $done, Dict $info)
    */
    protected function doStep($action) : array
    {
        $la = $this->la;
        //assert self.action_space.contains(action), "%r (%s) invalid" % (
        //    action,
        //    type(action),
        //)

        [$position, $velocity] = $this->state;
        $velocity += ($action - 1) * $this->force + cos(3 * $position) * (-$this->gravity);
        $velocity = min(max($velocity, -$this->max_speed), $this->max_speed);
        $position += $velocity;
        $position = min(max($position, $this->min_position), $this->max_position);
        if ($position == $this->min_position && $velocity < 0) {
            $velocity = 0;
        }

        $done = ($position >= $this->goal_position && $velocity >= $this->goal_velocity);
        $reward = -1.0;

        $this->state = [$position, $velocity];
        $state = $la->array($this->state, $dtype=NDArray::float32);
        return [$state, $reward, $done, []];
    }

    /**
    * @return Any $observation
    **/
    protected function doReset()
    {
        $la = $this->la;
        $position = $la->randomUniform([1],$low=-0.6, $high=-0.4);
        $this->state = [$position[0], 0];
        return $la->array($this->state, $dtype=NDArray::float32);
    }

    protected function height($xs)
    {
        $la = $this->la;
        if($xs instanceof NDArray) {
            return $la->increment($la->sin($la->scal(3,$la->copy($xs))), 0.55, 0.45);
        } elseif(is_numeric($xs)) {
            return sin(3 * $xs) * 0.45 + 0.55;
        } else {
            throw new LogicException('height: xs must be NDArray or numeric');
        }
    }

    public function render($mode="human") : mixed
    {
        $la = $this->la;
        $screen_width = 600;
        $screen_height = 400;

        $world_width = $this->max_position - $this->min_position;
        $scale = $screen_width / $world_width;
        $carwidth = 40;
        $carheight = 20;

        if($this->viewer === null) {
            $rendering = $this->renderingFactory->factory();
            $this->viewer = $rendering->Viewer($screen_width, $screen_height);

            $xs = $la->linspace($this->min_position, $this->max_position, 100);
            $ys = $this->height($xs);
            $xys = array_map(null,
                $la->scal($scale,$la->increment($la->copy($xs), -$this->min_position))->toArray(),
                $la->scal($scale, $la->copy($ys))->toArray()
            );

            $this->track = $rendering->make_polyline($xys);
            $this->track->set_linewidth(4);
            $this->viewer->add_geom($this->track);

            $clearance = 10;

            [$l, $r, $t, $b] = [-$carwidth / 2, $carwidth / 2, $carheight, 0];
            $car = $rendering->FilledPolygon([[$l, $b], [$l, $t], [$r, $t], [$r, $b]]);
            $car->add_attr($rendering->Transform($translation=[0, $clearance]));
            $this->cartrans = $rendering->Transform();
            $car->add_attr($this->cartrans);
            $this->viewer->add_geom($car);
            $frontwheel = $rendering->make_circle($carheight / 2.5);
            $frontwheel->set_color(0.5, 0.5, 0.5);
            $frontwheel->add_attr(
                $rendering->Transform($translation=[$carwidth / 4, $clearance])
            );
            $frontwheel->add_attr($this->cartrans);
            $this->viewer->add_geom($frontwheel);
            $backwheel = $rendering->make_circle($carheight / 2.5);
            $backwheel->add_attr(
                $rendering->Transform($translation=[-$carwidth / 4, $clearance])
            );
            $backwheel->add_attr($this->cartrans);
            $backwheel->set_color(0.5, 0.5, 0.5);
            $this->viewer->add_geom($backwheel);
            $flagx = ($this->goal_position - $this->min_position) * $scale;
            $flagy1 = $this->height($this->goal_position) * $scale;
            $flagy2 = $flagy1 + 50;
            $flagpole = $rendering->Line([$flagx, $flagy1], [$flagx, $flagy2]);
            $this->viewer->add_geom($flagpole);
            $flag = $rendering->FilledPolygon(
                [[$flagx, $flagy2], [$flagx, $flagy2 - 10], [$flagx + 25, $flagy2 - 5]]
            );
            $flag->set_color(0.8, 0.8, 0);
            $this->viewer->add_geom($flag);
        }

        $pos = $this->state[0];
        $this->cartrans->set_translation(
            ($pos - $this->min_position) * $scale, $this->height($pos) * $scale
        );
        $this->cartrans->set_rotation(cos(3 * $pos));

        return $this->viewer->render($mode);
    }
}
