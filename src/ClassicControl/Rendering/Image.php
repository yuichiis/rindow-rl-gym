<?php
/*
    This code is a copy of gym.emvs.classic_control.rendering.
    Click here for the original source code.
    https://github.com/openai/gym/blob/master/gym/envs/classic_control/rendering.py
*/
namespace Rindow\RL\Gym\ClassicControl\Rendering;

use Rindow\RL\Gym\Core\Graphics\GL;
use Rindow\RL\Gym\Core\Graphics\Image as GLImage;

class Image extends Geom
{
    protected float $width;
    protected float $height;
    protected GLImage $img;
    protected bool $flip;

    public function __construct(GL $gl, string $fname, float $width, float $height)
    {
        parent::__construct($gl);
        $this->set_color(1.0, 1.0, 1.0);
        $this->width = $width;
        $this->height = $height;
        $img = $this->gl->load_image($fname);
        $this->img = $img;
        $this->flip = false;
    }

    public function render1() : void
    {
        $this->gl->glPushMatrix();
        $this->gl->glScalef($this->width,$this->height,1);
        $imgWidth = $this->img->width();
        $imgHeight = $this->img->height();
        $this->gl->renderImage(
            $this->img, (int)ceil($imgWidth/2), (int)ceil($imgHeight/2), $imgWidth, $imgHeight
        );
        $this->gl->glPopMatrix();
    }
}
