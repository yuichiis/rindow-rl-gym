<?php
/*
    This code is a copy of gym.emvs.classic_control.rendering.
    Click here for the original source code.
    https://github.com/openai/gym/blob/master/gym/envs/classic_control/rendering.py
*/
namespace Rindow\RL\Gym\ClassicControl\Rendering;

abstract class Geom
{
    abstract public function render1();

    protected $gl;
    protected $color;
    public $attrs;

    public function __construct($gl)
    {
        $this->gl = $gl;
        $this->color = new Color($gl,[0, 0, 0, 1.0]);
        $this->attrs = [$this->color];
    }

    public function render() : void
    {
        foreach(array_reverse($this->attrs) as $attr) {
            $attr->enable();
        }
        $this->render1();
        foreach($this->attrs as $attr) {
            $attr->disable();
        }
    }

    public function add_attr(Attr $attr) : void
    {
        if($attr instanceof LineStyle) {
            array_unshift($this->attrs, $attr);
            return;
        }
        $this->attrs[] = $attr;
    }

    public function set_color(float $r, float $g, float $b) : void
    {
        $this->color->vec4 = [$r, $g, $b, 1.0];
    }
}
