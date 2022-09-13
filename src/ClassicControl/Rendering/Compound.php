<?php
/*
    This code is a copy of gym.emvs.classic_control.rendering.
    Click here for the original source code.
    https://github.com/openai/gym/blob/master/gym/envs/classic_control/rendering.py
*/
namespace Rindow\RL\Gym\ClassicControl\Rendering;

class Compound extends Geom
{
    protected $gs;

    public function __construct($gl, array $gs)
    {
        parent::__construct($gl);
        $this->gs = $gs;
        foreach($this->gs as $g) {
            $g->attrs = array_filter($g->attrs,fn($a)=>!($a instanceof Color));
        }
    }

    public function render1() : void
    {
        foreach ($this->gs as $g) {
            $g->render();
        }
    }
}
