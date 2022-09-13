<?php
/*
    This code is a copy of gym.emvs.classic_control.rendering.
    Click here for the original source code.
    https://github.com/openai/gym/blob/master/gym/envs/classic_control/rendering.py
*/
namespace Rindow\RL\Gym\ClassicControl\Rendering;

use Rindow\RL\Gym\Core\Graphics\GL;
use Interop\Polite\Math\Matrix\NDArray;

class Viewer
{
    protected $rendering;
    protected $gl;
    protected $width;
    protected $height;
    protected $window;
    protected $isopen;
    protected $geoms;
    protected $onetime_geoms;
    protected $transform;

    public function __construct($rendering, int $width, int $height, $display=null)
    {
        $this->rendering = $rendering;
        $this->gl = $rendering->gl();

        $display = $this->gl->get_display($display);

        $this->width = $width;
        $this->height = $height;
        $this->window = $this->rendering->get_window($width, $height, $display);
        //$this->window->on_close = $this->window_closed_by_user;
        $this->isopen = true;
        $this->geoms = [];
        $this->onetime_geoms = [];
        $this->transform = $this->rendering->Transform();

        $this->gl->glEnable(GL::GL_BLEND);
        $this->gl->glBlendFunc(GL::GL_SRC_ALPHA, GL::GL_ONE_MINUS_SRC_ALPHA);
    }

    public function rendering()
    {
        return $this->rendering;
    }

    public function close() : void
    {
        if($this->isopen) {
            $this->window->close();
            $this->rendering->close();
            $this->gl->close();
            $this->window = null;
            $this->rendering = null;
            $this->gl = null;
            $this->isopen = false;
        }
    }

    public function window_closed_by_user() : void
    {
        $this->isopen = false;
    }

    public function set_bounds(float $left, float $right, float $bottom, float $top) : void
    {
        assert($right > $left && $top > $bottom);
        $scalex = $this->width / ($right - $left);
        $scaley = $this->height / ($top - $bottom);
        $this->transform = $this->rendering->Transform(
            [-$left * $scalex, -$bottom * $scaley], $rotation=null, $scale=[$scalex, $scaley]
        );
    }

    public function add_geom(Geom $geom) : void
    {
        $this->geoms[] = $geom;
    }

    public function add_onetime(Geom $geom) : void
    {
        $this->onetime_geoms[] = $geom;
    }

    public function render(string $mode=null)
    {
        $this->gl->glClearColor(1, 1, 1, 1);
        $this->window->clear();
        $this->window->switch_to();
        $this->window->dispatch_events();
        $this->transform->enable();
        foreach($this->geoms as $geom) {
            $geom->render();
        }
        foreach($this->onetime_geoms as $geom) {
            $geom->render();
        }
        $this->transform->disable();
        $arr = null;
        if($mode == "rgb_array") {
            $arr = $this->gl->get_image_data();
            //$arr = $arr->reshape($buffer->height, $buffer->width, 4);
            //$arr = $arr[::-1, :, 0:3];
        }
        //$this->window->flip();
        $this->onetime_geoms = [];

        if($mode == "rgb_array") {
            return $arr;
        } elseif($mode == "handler") {
            return $this->gl->handler();
        }

        $filename = $this->gl->output();
        return $this->isopen;
    }

    public function show(bool $loop=null,int $delay=null) : void
    {
        $this->gl->show($loop, $delay);
    }

    # Convenience
    public function draw_circle(float $radius=10, float $res=30, bool $filled=True, ...$attrs) : Geom
    {
        $geom = $this->rendering->make_circle($radius, $res, $filled);
        $this->add_attrs($geom, $attrs);
        $this->add_onetime($geom);
        return $geom;
    }

    public function draw_polygon(array $v, bool $filled=true, ...$attrs) : Geom
    {
        $geom = $this->rendering->make_polygon($v, $filled);
        $this->add_attrs($geom, $attrs);
        $this->add_onetime($geom);
        return $geom;
    }

    public function draw_polyline(array $v, ...$attrs) : Geom
    {
        $geom = $this->rendering->make_polyline($v);
        $this->add_attrs($geom, $attrs);
        $this->add_onetime($geom);
        return $geom;
    }

    public function draw_line(array $start, array $end, ...$attrs) : Geom
    {
        $geom = $this->rendering->Line($start, $end);
        $this->add_attrs($geom, $attrs);
        $this->add_onetime($geom);
        return $geom;
    }

    public function get_array() : NDArray
    {
        $this->window->flip();
        $arr = $this->gl->get_image_data();
        $this->window->flip();
        return $arr;
    }

    protected function add_attrs(Geom $geom, array $attrs) : void
    {
        if(array_key_exists('color',$attrs)) {
            $geom->set_color(...$attrs['color']);
        }
        if(array_key_exists('linewidth',$attrs)) {
            $geom->set_linewidth($attrs['linewidth']);
        }
    }

    public function __destruct()
    {
        $this->close();
    }
}
