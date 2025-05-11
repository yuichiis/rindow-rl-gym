<?php
namespace Rindow\RL\Gym\ClassicControl\CartPole;

class CartPoleV0 extends CartPoleEnv
{
    protected int $maxEpisodeSteps=200;
    protected float $rewardThreshold=195.0;
}
