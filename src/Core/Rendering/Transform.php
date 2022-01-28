<?php
/*
    This code is a copy of gym.emvs.classic_control.rendering.
    Click here for the original source code.
    https://github.com/openai/gym/blob/master/gym/envs/classic_control/rendering.py
*/
namespace Rindow\RL\Gym\Core\Rendering;

use Rindow\RL\Gym\Core\Graphics\GL;

class Transform extends Attr
{
    const RAD2DEG = 57.29577951308232;

    protected $translation;
    protected $rotation;
    protected $scale;


    public function __construct($gl,
        array $translation=null, float $rotation=null, array $scale=null)
    {
        parent::__construct($gl);
        if($translation===null) {
            $translation=[0.0, 0.0];
        }
        if($rotation===null) {
            $rotation=0.0;
        }
        if($scale===null) {
            $scale=[1, 1];
        }
        $this->set_translation(...$translation);
        $this->set_rotation($rotation);
        $this->set_scale(...$scale);
    }

    public function enable()
    {
        $this->gl->glPushMatrix();
        $this->gl->glTranslatef(
            $this->translation[0], $this->translation[1], 0
        );  # translate to GL loc ppint
        $this->gl->glRotatef(self::RAD2DEG * $this->rotation, 0, 0, 1.0);
        $this->gl->glScalef($this->scale[0], $this->scale[1], 1);
    }

    public function disable()
    {
        $this->gl->glPopMatrix();
    }

    public function set_translation($newx, $newy)
    {
        $this->translation = [(float)$newx, (float)$newy];
    }

    public function set_rotation($new)
    {
        $this->rotation = (float)$new;
    }

    public function set_scale($newx,$newy) {
        $this->scale = [(float)$newx, (float)$newy];
    }
}
