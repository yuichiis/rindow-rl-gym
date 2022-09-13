<?php
/*
    This code is a copy of gym.emvs.classic_control.rendering.
    Click here for the original source code.
    https://github.com/openai/gym/blob/master/gym/envs/classic_control/rendering.py
*/
namespace Rindow\RL\Gym\ClassicControl\Rendering;

abstract class Attr
{
    abstract public function enable();

    protected $gl;

    public function __construct($gl)
    {
        $this->gl = $gl;
    }

    public function disable() : void
    {
    }
}
