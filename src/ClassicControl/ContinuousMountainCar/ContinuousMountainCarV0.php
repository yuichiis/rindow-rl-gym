<?php
namespace Rindow\RL\Gym\ClassicControl\ContinuousMountainCar;

class ContinuousMountainCarV0 extends ContinuousMountainCarEnv
{
    protected int $maxEpisodeSteps=999;
    protected float $rewardThreshold=90.0;
}
