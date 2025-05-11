<?php
/*
    This code is a copy of gym.emvs.classic_control.rendering.
    Click here for the original source code.
    https://github.com/openai/gym/blob/master/gym/envs/classic_control/rendering.py
*/
namespace Rindow\RL\Gym\ClassicControl\Rendering;

use Rindow\RL\Gym\Core\Graphics\GL;

class Compound extends Geom
{
    /** @var array<Geom> $geoms */
    protected array $geoms;

    /**
     * @param array<Geom> $geoms
     */
    public function __construct(GL $gl, array $geoms)
    {
        parent::__construct($gl);
        $this->geoms = $geoms;
        foreach($this->geoms as $geom) {
            $geom->attrs = array_filter($geom->attrs,fn($a)=>!($a instanceof Color));
        }
    }

    public function render1() : void
    {
        foreach ($this->geoms as $geom) {
            $geom->render();
        }
    }
}
