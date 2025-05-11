<?php
namespace Rindow\RL\Gym\ClassicControl\MountainCar;

class MountainCarV0 extends MountainCarEnv
{
    protected int $maxEpisodeSteps=200;
    protected float $rewardThreshold=-110.0;
}
