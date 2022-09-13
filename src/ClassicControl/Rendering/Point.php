<?php
/*
    This code is a copy of gym.emvs.classic_control.rendering.
    Click here for the original source code.
    https://github.com/openai/gym/blob/master/gym/envs/classic_control/rendering.py
*/
namespace Rindow\RL\Gym\ClassicControl\Rendering;

use Rindow\RL\Gym\Core\Graphics\GL;

class Point extends Geom
{
    public function render1() : void
    {
        $this->gl->glBegin(GL::GL_POINTS);  # draw point
        $this->gl->glVertex3f(0.0, 0.0, 0.0);
        $this->gl->glEnd();
    }
}
