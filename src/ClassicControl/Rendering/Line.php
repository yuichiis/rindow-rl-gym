<?php
/*
    This code is a copy of gym.emvs.classic_control.rendering.
    Click here for the original source code.
    https://github.com/openai/gym/blob/master/gym/envs/classic_control/rendering.py
*/
namespace Rindow\RL\Gym\ClassicControl\Rendering;

use Rindow\RL\Gym\Core\Graphics\GL;

class Line extends Geom
{
    public function __construct($gl, array $start=null, array $end=null)
    {
        parent::__construct($gl);
        if($start===null) {
            $start=[0.0, 0.0];
        }
        if($end===null) {
            $end=[0.0, 0.0];
        }
        $this->start = $start;
        $this->end = $end;
        $this->linewidth = new LineWidth($this->gl,1);
        $this->add_attr($this->linewidth);
    }

    public function render1() : void
    {
        $this->gl->glBegin(GL::GL_LINES);
        $this->gl->glVertex2f(...$this->start);
        $this->gl->glVertex2f(...$this->end);
        $this->gl->glEnd();
    }

    public function set_linewidth(float $x) : void
    {
        $this->linewidth->stroke = $x;
    }
}
