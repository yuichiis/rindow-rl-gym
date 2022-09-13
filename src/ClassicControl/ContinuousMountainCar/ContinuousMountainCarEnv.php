<?php
/*
# -*- coding: utf-8 -*-
"""
@author: Olivier Sigaud

A merge between two sources:

* Adaptation of the MountainCar Environment from the "FAReinforcement" library
of Jose Antonio Martin H. (version 1.0), adapted by  'Tom Schaul, tom@idsia.ch'
and then modified by Arnaud de Broissia

* the OpenAI/gym MountainCar environment
itself from
http://incompleteideas.net/sutton/MountainCar/MountainCar1.cp
permalink: https://perma.cc/6Z2N-PFWC
"""

"""
Description:
    The agent (a car) is started at the bottom of a valley. For any given
    state the agent may choose to accelerate to the left, right or cease
    any acceleration.
Observation:
    Type: Box(2)
    Num    Observation               Min            Max
    0      Car Position              -1.2           0.6
    1      Car Velocity              -0.07          0.07
Actions:
    Type: Box(1)
    Num    Action                    Min            Max
    0      the power coef            -1.0           1.0
    Note: actual driving force is calculated by multipling the power coef by power (0.0015)

Reward:
     Reward of 100 is awarded if the agent reached the flag (position = 0.45) on top of the mountain.
     Reward is decrease based on amount of energy consumed each step.

Starting State:
     The position of the car is assigned a uniform random value in
     [-0.6 , -0.4].
     The starting velocity of the car is always assigned to 0.

Episode Termination:
     The car position is more than 0.45
     Episode length is greater than 200
"""

*/
namespace Rindow\RL\Gym\ClassicControl\ContinuousMountainCar;

use RuntimeException;
use InvalidArgumentException;
use LogicException;
use Interop\Polite\Math\Matrix\NDArray;
use Rindow\RL\Gym\Core\AbstractEnv;
use Rindow\RL\Gym\Core\Spaces\Discrete;
use Rindow\RL\Gym\Core\Spaces\Box;
use Rindow\RL\Gym\ClassicControl\Rendering\RenderFactory;

class ContinuousMountainCarEnv extends AbstractEnv
{
    protected $metadata = ["render.modes"=> ["human", "rgb_array"], "video.frames_per_second"=> 30];

    protected $min_action = -1.0;
    protected $max_action = 1.0;
    protected $min_position = -1.2;
    protected $max_position = 0.6;
    protected $max_speed = 0.07;

    protected $goal_position = 0.45; # was 0.5 in gym, 0.45 in Arnaud de Broissia's version
    protected $goal_velocity;
    protected $power;
    protected $low_state;
    protected $high_state;
    protected $state;

    public function __construct(object $la, $goal_velocity=0, array $metadata=null, object $renderer=null)
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
        $this->power = 0.0015;

        $this->low_state = $la->array(
            [$this->min_position, -$this->max_speed], NDArray::float32
        );
        $this->high_state = $la->array(
            [$this->max_position, $this->max_speed], NDArray::float32
        );

        $this->setActionSpace( new Box($la,
            $this->min_action, $this->max_action, shape:[1], dtype:NDArray::float32
        ));
        $this->setObservationSpace( new Box($la,
            $this->low_state, $this->high_state, dtype:NDArray::float32
        ));

        $this->seed();
    }

    /**
    * @param Any $action
    * @return Set(Any $observation, Any $rewards, bool $done, Dict $info)
    */
    protected function doStep($action) : array
    {
        $la = $this->la;
        [$position, $velocity] = $this->state;
        $force = min(max($action[0], $this->min_action), $this->max_action);

        $velocity += $force * $this->power - 0.0025 * cos(3 * $position);
        if($velocity > $this->max_speed) {
            $velocity = $this->max_speed;
        }
        if($velocity < -$this->max_speed) {
            $velocity = -$this->max_speed;
        }
        $position += $velocity;
        if($position > $this->max_position) {
            $position = $this->max_position;
        }
        if($position < $this->min_position) {
            $position = $this->min_position;
        }
        if($position == $this->min_position && $velocity < 0) {
            $velocity = 0;
        }

        # Convert a possible numpy bool to a Python bool.
        $done = ($position >= $this->goal_position && $velocity >= $this->goal_velocity);

        $reward = 0;
        if($done) {
            $reward = 100.0;
        }
        $reward -= pow($action[0], 2) * 0.1;

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