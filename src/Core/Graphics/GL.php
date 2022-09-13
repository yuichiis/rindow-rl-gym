<?php
namespace Rindow\RL\Gym\Core\Graphics;

/**
 *
 */
interface GL
{
    /* Matrix Mode */
    //const GL_MODELVIEW                    = 0x1700;
    //const GL_PROJECTION                   = 0x1701;

    /* ClearMode bit */
    const GL_DEPTH_BUFFER_BIT             = 0x0100;
    const GL_COLOR_BUFFER_BIT             = 0x4000;

    /* BeginMode */
    const GL_POINTS                       = 0x0000;
    const GL_LINES                        = 0x0001;
    const GL_LINE_LOOP                    = 0x0002;
    const GL_LINE_STRIP                   = 0x0003;
    const GL_TRIANGLES                    = 0x0004;
    //const GL_TRIANGLE_STRIP               = 0x0005;
    //const GL_TRIANGLE_FAN                 = 0x0006;
    const GL_QUADS                        = 0x0007;
    //const GL_QUAD_STRIP                   = 0x0008;
    const GL_POLYGON                      = 0x0009;

    /* GetTarget */
    const GL_LINE_STIPPLE                 = 0x0B24;
    const GL_BLEND                        = 0x0BE2;

    /* BlendingFactorDest */
    const GL_SRC_ALPHA                    = 0x0302;
    const GL_ONE_MINUS_SRC_ALPHA          = 0x0303;

    public function glBegin(int $mode) : void;
    public function glEnd() : void;
    public function glVertex2f(float $x, float $y) : void;
    public function glVertex3f(float $x, float $y, float $z) : void;
    public function glEnable(int $cap) : void;
    public function glDisable(int $cap) : void;
    public function glBlendFunc(int $sfactor, int $dfactor) : void;
    public function glColor4f(float $red, float $green, float $blue, float $alpha) : void;
    public function glClearColor(float $red, float $green, float $blue, float $alpha) : void;
    public function glLineStipple(int $factor, int $pattern) : void;
    public function glLineWidth(float $width) : void;
    public function glPushMatrix() : void;
    public function glPopMatrix() : void;
    public function glTranslatef(float $x, float $y, float $z) : void;
    public function glRotatef(float $angle, float $x, float $y, float $z) : void;
    public function glScalef(float $x,float $y,float $z) : void;

    public function renderImage(
        Image $image, float $centerx, float $centery, float $width, float $height) : void;

    //public function get_display($display);
    //public function createWindow($width, $height, $display);
    //public function clear() : void;
    //public function flip() : void;
    //public function load_image($fname);
    //public function output();
    //public function get_image_data();
    //public function show(bool $loop=null,int $delay=null) : void;
    //public function handler();
    //public function close() : void;
}
