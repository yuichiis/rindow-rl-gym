<?php
namespace Rindow\RL\Gym\Core\Graphics;

class Window
{
    protected $gl;
    protected $width;
    protected $height;
    protected $display;

    public function __construct($gl, $width, $height, $display)
    {
        $this->gl = $gl;
        $this->width = $width;
        $this->height = $height;
        $this->display = $display;
    }

    public function close()
    {
        $this->gl = null;
    }

    public function clear()
    {
        $this->gl->clear();
    }

    public function switch_to()
    {
    }

    public function dispatch_events()
    {
    }

    public function flip()
    {
        $this->gl->flip();
    }
}
