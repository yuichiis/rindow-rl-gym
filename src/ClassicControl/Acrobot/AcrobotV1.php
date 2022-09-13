<?php
namespace Rindow\RL\Gym\ClassicControl\Acrobot;

class AcrobotV1 extends AcrobotEnv
{
    protected $rewardThreshold=-100.0;
    protected $maxEpisodeSteps=500;
}
