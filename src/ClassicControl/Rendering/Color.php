<?php
/*
    This code is a copy of gym.emvs.classic_control.rendering.
    Click here for the original source code.
    https://github.com/openai/gym/blob/master/gym/envs/classic_control/rendering.py
*/
namespace Rindow\RL\Gym\ClassicControl\Rendering;

use Rindow\RL\Gym\Core\Graphics\GL;

class Color extends Attr
{
    /** @var array<float> $vec4 */
    public array $vec4;

    /**
     * @param array<float> $vec4
     */
    public function __construct(GL $gl,array $vec4)
    {
        parent::__construct($gl);
        $this->vec4 = $vec4;
    }

    public function enable() : void
    {
        $this->gl->glColor4f(...$this->vec4);
    }
}
