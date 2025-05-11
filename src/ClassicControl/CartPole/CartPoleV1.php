<?php
namespace Rindow\RL\Gym\ClassicControl\CartPole;

class CartPoleV1 extends CartPoleEnv
{
    protected int $maxEpisodeSteps=500;
    protected float $rewardThreshold=475.0;
}
