<?php
/*
    This code is a copy of gym.emvs.classic_control.rendering.
    Click here for the original source code.
    https://github.com/openai/gym/blob/master/gym/envs/classic_control/rendering.py
*/
namespace Rindow\RL\Gym\Core\Rendering;

use Rindow\RL\Gym\Core\Graphics\GL;

class LineStyle extends Attr
{
    protected $style;

    public function __construct($gl,$style)
    {
        parent::__construct($gl);
        $this->style = $style;
    }

    public function enable()
    {
        $this->gl->glEnable(GL::GL_LINE_STIPPLE);
        $this->gl->glLineStipple(1, $this->style);
    }

    public function disable()
    {
        $this->gl->glDisable(GL::GL_LINE_STIPPLE);
    }
}
