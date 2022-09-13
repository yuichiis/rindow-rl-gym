<?php
/*
    This code is a copy of gym.emvs.classic_control.rendering.
    Click here for the original source code.
    https://github.com/openai/gym/blob/master/gym/envs/classic_control/rendering.py
*/
namespace Rindow\RL\Gym\ClassicControl\Rendering;

use Rindow\RL\Gym\Core\Graphics\GL;

class FilledPolygon extends Geom
{
    public $v;

    public function __construct($gl, array $v)
    {
        parent::__construct($gl);
        $this->v = $v;
    }

    public function render1() : void
    {
        if(count($this->v) == 4) {
            $this->gl->glBegin(GL::GL_QUADS);
        } elseif(count($this->v) > 4) {
            $this->gl->glBegin(GL::GL_POLYGON);
        } else {
            $this->gl->glBegin(GL::GL_TRIANGLES);
        }
        foreach($this->v as $p) {
            $this->gl->glVertex3f($p[0], $p[1], 0);  # draw each vertex
        }
        $this->gl->glEnd();
    }
}
