<?php
namespace Rindow\RL\Gym\Core\Graphics;

class Window
{
    protected ?GL $gl;
    protected int $width;
    protected int $height;
    protected mixed $display;

    public function __construct(GL $gl, int $width, int $height, mixed $display)
    {
        $this->gl = $gl;
        $this->width = $width;
        $this->height = $height;
        $this->display = $display;
    }

    public function close() : void
    {
        $this->gl = null;
    }

    public function clear() : void
    {
        $this->gl->clear();
    }

    public function switch_to() : void
    {
    }

    public function dispatch_events() : void
    {
    }

    public function flip() : void
    {
        $this->gl->flip();
    }
}
