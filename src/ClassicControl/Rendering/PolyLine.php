<?php
/*
    This code is a copy of gym.emvs.classic_control.rendering.
    Click here for the original source code.
    https://github.com/openai/gym/blob/master/gym/envs/classic_control/rendering.py
*/
namespace Rindow\RL\Gym\ClassicControl\Rendering;

use Rindow\RL\Gym\Core\Graphics\GL;

class PolyLine extends Geom
{
    protected $v;
    protected $close;
    protected $linewidth;

    public function __construct($gl, array $v, bool $close)
    {
        parent::__construct($gl);
        $this->v = $v;
        $this->close = $close;
        $this->linewidth = new LineWidth($this->gl,1);
        $this->add_attr($this->linewidth);
    }

    public function render1() : void
    {
        $this->gl->glBegin($this->close ? GL::GL_LINE_LOOP : GL::GL_LINE_STRIP);
        foreach($this->v as $p) {
            $this->gl->glVertex3f($p[0], $p[1], 0);  # draw each vertex
        }
        $this->gl->glEnd();
    }

    public function set_linewidth(float $x) : void
    {
        $this->linewidth->stroke = $x;
    }
}
