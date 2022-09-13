<?php
namespace Rindow\RL\Gym\ClassicControl\MountainCar;

class MountainCarV0 extends MountainCarEnv
{
    protected $maxEpisodeSteps=200;
    protected $rewardThreshold=-110.0;
}
