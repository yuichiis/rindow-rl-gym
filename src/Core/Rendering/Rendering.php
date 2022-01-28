<?php
/*
    This code is a copy of gym.emvs.classic_control.rendering.
    Click here for the original source code.
    https://github.com/openai/gym/blob/master/gym/envs/classic_control/rendering.py
*/
namespace Rindow\RL\Gym\Core\Rendering;

class Rendering
{
    protected $gl;

    public function __construct($gl)
    {
        $this->gl = $gl;
    }

    public function gl()
    {
        return $this->gl;
    }

    public function close()
    {
        $this->gl = null;
    }

    // ==============
    // Attrs
    // ==============
    public function Color($vec4)
    {
        return new Color($this->gl,$vec4);
    }

    public function LineStyle($style)
    {
        return new LineStyle($this->gl, $style);
    }

    public function LineWidth($stroke)
    {
        return new LineWidth($this->gl, $stroke);
    }

    public function Transform(array $translation=null, float $rotation=null, array $scale=null)
    {
        return new Transform($this->gl, $translation, $rotation, $scale);
    }

    // ==============
    // geoms
    // ==============
    public function Compound(array $gs)
    {
        return new Compound($this->gl,$gs);
    }

    public function FilledPolygon(array $v)
    {
        return new FilledPolygon($this->gl,$v);
    }

    public function Image(string $fname, float $width, float $height)
    {
        return new Image($this->gl, $fname, $width, $height);
    }

    public function Line(array $start=null, array $end=null)
    {
        return new Line($this->gl, $start, $end);
    }

    public function Point()
    {
        return new Point($this->gl);
    }

    public function PolyLine(array $v, bool $close)
    {
        return new PolyLine($this->gl, $v, $close);
    }

    // ==============
    // viewer
    // ==============
    public function Viewer($width, $height, $display=null)
    {
        return new Viewer($this, $width, $height, $display);
    }

    public function make_circle($radius=10, $res=30, $filled=true)
    {
        $points = [];
        for($i=0;$i<$res;$i++) {
            $ang = 2 * M_PI * $i / $res;
            $points[] = [cos($ang) * $radius, sin($ang) * $radius];
        }
        if($filled) {
            return new FilledPolygon($this->gl, $points);
        } else {
            return new PolyLine($this->gl, $points, true);
        }
    }

    public function make_polygon($v, $filled=true)
    {
        if($filled) {
            return new FilledPolygon($this->gl, $v);
        } else {
            return new PolyLine($this->gl, $v, true);
        }
    }

    public function make_polyline($v, $filled=true)
    {
        return new PolyLine($this->gl, $v, false);
    }

    public function make_capsule($length, $width)
    {
        [$l, $r, $t, $b] = [0, $length, $width / 2, -$width / 2];
        $box = $this->make_polygon([[$l, $b], [$l, $t], [$r, $t], [$r, $b]]);
        $circ0 = $this->make_circle($width / 2);
        $circ1 = $this->make_circle($width / 2);
        $circ1->add_attr(new Transform($this->gl, [$length, 0]));
        $geom = new Compound($this->gl,[$box, $circ0, $circ1]);
        return $geom;
    }
}
