<?php
namespace Rindow\RL\Gym\ClassicControl\Acrobot;

class AcrobotV1 extends AcrobotEnv
{
    protected float $rewardThreshold=-100.0;
    protected int $maxEpisodeSteps=500;
}
