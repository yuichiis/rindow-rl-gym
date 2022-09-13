<?php
namespace Rindow\RL\Gym\ClassicControl\ContinuousMountainCar;

class ContinuousMountainCarV0 extends ContinuousMountainCarEnv
{
    protected $maxEpisodeSteps=999;
    protected $rewardThreshold=90.0;
}
