<?php
/*
    This code is a copy of gym.emvs.classic_control.rendering.
    Click here for the original source code.
    https://github.com/openai/gym/blob/master/gym/envs/classic_control/rendering.py
*/
namespace Rindow\RL\Gym\ClassicControl\Rendering;

use Rindow\RL\Gym\Core\Graphics\GL;

class LineWidth extends Attr
{
    public float $stroke;

    public function __construct(GL $gl, float $stroke)
    {
        parent::__construct($gl);
        $this->stroke = $stroke;
    }

    public function enable() : void
    {
        $this->gl->glLineWidth($this->stroke);
    }
}
