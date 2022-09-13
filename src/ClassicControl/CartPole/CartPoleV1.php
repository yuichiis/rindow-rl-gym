<?php
namespace Rindow\RL\Gym\ClassicControl\CartPole;

class CartPoleV1 extends CartPoleEnv
{
    protected $maxEpisodeSteps=500;
    protected $rewardThreshold=475.0;
}
