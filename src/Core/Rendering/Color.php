<?php
/*
    This code is a copy of gym.emvs.classic_control.rendering.
    Click here for the original source code.
    https://github.com/openai/gym/blob/master/gym/envs/classic_control/rendering.py
*/
namespace Rindow\RL\Gym\Core\Rendering;

class Color extends Attr
{
    public $vec4;

    public function __construct($gl,$vec4)
    {
        parent::__construct($gl);
        $this->vec4 = $vec4;
    }

    public function enable()
    {
        $this->gl->glColor4f(...$this->vec4);
    }
}
