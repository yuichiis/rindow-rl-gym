<?php
namespace Rindow\RL\Gym\CartPole;

class CartPoleV0 extends CartPoleEnv
{
    protected $maxEpisodeSteps=200;
    protected $rewardThreshold=195.0;
}
