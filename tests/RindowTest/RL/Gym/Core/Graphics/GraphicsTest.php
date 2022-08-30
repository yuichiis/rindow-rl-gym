<?php
namespace RindowTest\RL\Gym\Core\Graphics\GDGLTest;

use PHPUnit\Framework\TestCase;
use Interop\Polite\Math\Matrix\NDArray;
use Rindow\Math\Matrix\MatrixOperator;
use Rindow\Math\Plot\Plot;
use Rindow\RL\Gym\Core\Graphics\GDGL;
use Rindow\RL\Gym\Core\Graphics\GL;

class Test extends TestCase
{
    public function newMatrixOperator()
    {
        return new MatrixOperator();
    }

    public function newLa($mo)
    {
        return $mo->la();
    }

    public function testLines()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        $gl = new GDGL($la);

        $display = $gl->get_display(null);
        $window = $gl->get_window($width=400, $height=300, $display);

        $gl->glClearColor($red=1.0, $green=1.0, $blue=1.0, $alpha=1.0);
        $gl->clear();

        $gl->glColor4f($red=0.0, $green=0.0, $blue=0.0, $alpha=1.0);
        $gl->glBegin(GL::GL_LINES);
        $gl->glVertex2f(0.0, 0.0);
        $gl->glVertex2f(400.0, 10.0);
        $gl->glVertex2f(0.0, 5.0);
        $gl->glVertex2f(400.0, 15.0);
        $gl->glEnd();

        $gl->glColor4f($red=0.0, $green=1.0, $blue=0.0, $alpha=1.0);
        $gl->glEnable(GL::GL_LINE_STIPPLE);
        $gl->glLineStipple(1, $style=0xf0f0);
        $gl->glLineWidth(1.0);
        $gl->glBegin(GL::GL_LINES);
        $gl->glVertex2f(0.0, 10.0);
        $gl->glVertex2f(400.0, 20.0);
        $gl->glVertex2f(0.0, 15.0);
        $gl->glVertex2f(400.0, 25.0);
        $gl->glEnd();
        $gl->glDisable(GL::GL_LINE_STIPPLE);

        $gl->glColor4f($red=1.0, $green=0.0, $blue=0.0, $alpha=1.0);
        $gl->glLineWidth(2.0);
        $gl->glBegin(GL::GL_LINES);
        $gl->glVertex2f(0.0, 20.0);
        $gl->glVertex2f(400.0, 30.0);
        $gl->glVertex2f(0.0, 25.0);
        $gl->glVertex2f(400.0, 35.0);
        $gl->glEnd();

        $fname = $gl->output();
        $gl->show();
        $this->assertTrue(true);
    }

    public function testLineLoop()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        $gl = new GDGL($la);

        $display = $gl->get_display(null);
        $window = $gl->get_window($width=400, $height=300, $display);

        $gl->glClearColor($red=1.0, $green=1.0, $blue=1.0, $alpha=1.0);
        $gl->clear();

        $gl->glColor4f($red=0.0, $green=0.0, $blue=0.0, $alpha=1.0);
        $gl->glBegin(GL::GL_LINE_LOOP);
        $gl->glVertex2f(1.0,   1.0);
        $gl->glVertex2f(398.0, 1.0);
        $gl->glVertex2f(398.0, 9.0);
        $gl->glEnd();

        $gl->glColor4f($red=0.0, $green=1.0, $blue=0.0, $alpha=1.0);
        $gl->glEnable(GL::GL_LINE_STIPPLE);
        $gl->glLineStipple(1, $style=0xf0f0);
        $gl->glLineWidth(1.0);
        $gl->glBegin(GL::GL_LINE_LOOP);
        $gl->glVertex2f(1.0,   10.0);
        $gl->glVertex2f(398.0, 10.0);
        $gl->glVertex2f(398.0, 19.0);
        $gl->glEnd();
        $gl->glDisable(GL::GL_LINE_STIPPLE);

        $gl->glColor4f($red=1.0, $green=0.0, $blue=0.0, $alpha=1.0);
        $gl->glLineWidth(2.0);
        $gl->glBegin(GL::GL_LINE_LOOP);
        $gl->glVertex2f(1.0,   20.0);
        $gl->glVertex2f(398.0, 20.0);
        $gl->glVertex2f(398.0, 29.0);
        $gl->glEnd();

        $fname = $gl->output();
        $gl->show();
        $this->assertTrue(true);
    }

    public function testLineStrip()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        $gl = new GDGL($la);

        $display = $gl->get_display(null);
        $window = $gl->get_window($width=400, $height=300, $display);

        $gl->glClearColor($red=1.0, $green=1.0, $blue=1.0, $alpha=1.0);
        $gl->clear();

        $gl->glColor4f($red=0.0, $green=0.0, $blue=0.0, $alpha=1.0);
        $gl->glBegin(GL::GL_LINE_STRIP);
        $gl->glVertex2f(1.0,   1.0);
        $gl->glVertex2f(398.0, 1.0);
        $gl->glVertex2f(398.0, 9.0);
        $gl->glEnd();

        $gl->glColor4f($red=0.0, $green=1.0, $blue=0.0, $alpha=1.0);
        $gl->glEnable(GL::GL_LINE_STIPPLE);
        $gl->glLineStipple(1, $style=0xf0f0);
        $gl->glLineWidth(1.0);
        $gl->glBegin(GL::GL_LINE_STRIP);
        $gl->glVertex2f(1.0,   10.0);
        $gl->glVertex2f(398.0, 10.0);
        $gl->glVertex2f(398.0, 19.0);
        $gl->glEnd();
        $gl->glDisable(GL::GL_LINE_STIPPLE);

        $gl->glColor4f($red=1.0, $green=0.0, $blue=0.0, $alpha=1.0);
        $gl->glLineWidth(2.0);
        $gl->glBegin(GL::GL_LINE_STRIP);
        $gl->glVertex2f(1.0,   20.0);
        $gl->glVertex2f(398.0, 20.0);
        $gl->glVertex2f(398.0, 29.0);
        $gl->glEnd();

        $fname = $gl->output();
        $gl->show();
        $this->assertTrue(true);
    }

    public function testTriangles()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        $gl = new GDGL($la);

        $display = $gl->get_display(null);
        $window = $gl->get_window($width=400, $height=300, $display);

        $gl->glClearColor($red=1.0, $green=1.0, $blue=1.0, $alpha=1.0);
        $gl->clear();

        $gl->glColor4f($red=0.0, $green=0.0, $blue=0.0, $alpha=1.0);
        $gl->glBegin(GL::GL_TRIANGLES);
        $gl->glVertex2f(1.0,   1.0);
        $gl->glVertex2f(398.0, 1.0);
        $gl->glVertex2f(398.0, 9.0);
        $gl->glVertex2f(1.0,   31.0);
        $gl->glVertex2f(398.0, 31.0);
        $gl->glVertex2f(398.0, 39.0);
        $gl->glEnd();

        $gl->glColor4f($red=0.0, $green=1.0, $blue=0.0, $alpha=1.0);
        $gl->glEnable(GL::GL_LINE_STIPPLE);
        $gl->glLineStipple(1, $style=0xf0f0);
        $gl->glLineWidth(1.0);
        $gl->glBegin(GL::GL_TRIANGLES);
        $gl->glVertex2f(1.0,   10.0);
        $gl->glVertex2f(398.0, 10.0);
        $gl->glVertex2f(398.0, 19.0);
        $gl->glVertex2f(1.0,   41.0);
        $gl->glVertex2f(398.0, 41.0);
        $gl->glVertex2f(398.0, 49.0);
        $gl->glEnd();
        $gl->glDisable(GL::GL_LINE_STIPPLE);

        $gl->glColor4f($red=1.0, $green=0.0, $blue=0.0, $alpha=1.0);
        $gl->glLineWidth(2.0);
        $gl->glBegin(GL::GL_TRIANGLES);
        $gl->glVertex2f(1.0,   20.0);
        $gl->glVertex2f(398.0, 20.0);
        $gl->glVertex2f(398.0, 29.0);
        $gl->glVertex2f(1.0,   51.0);
        $gl->glVertex2f(398.0, 51.0);
        $gl->glVertex2f(398.0, 59.0);
        $gl->glEnd();

        $fname = $gl->output();
        $gl->show();
        $this->assertTrue(true);
    }

    public function testQuads()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        $gl = new GDGL($la);

        $display = $gl->get_display(null);
        $window = $gl->get_window($width=400, $height=300, $display);

        $gl->glClearColor($red=1.0, $green=1.0, $blue=1.0, $alpha=1.0);
        $gl->clear();

        $gl->glColor4f($red=0.0, $green=0.0, $blue=0.0, $alpha=1.0);
        $gl->glBegin(GL::GL_QUADS);
        $gl->glVertex2f(1.0,   1.0);
        $gl->glVertex2f(398.0, 1.0);
        $gl->glVertex2f(398.0, 9.0);
        $gl->glVertex2f(1.0,   9.0);
        $gl->glVertex2f(1.0,   31.0);
        $gl->glVertex2f(398.0, 31.0);
        $gl->glVertex2f(398.0, 39.0);
        $gl->glVertex2f(1.0,   39.0);
        $gl->glEnd();

        $gl->glColor4f($red=0.0, $green=1.0, $blue=0.0, $alpha=1.0);
        $gl->glEnable(GL::GL_LINE_STIPPLE);
        $gl->glLineStipple(1, $style=0xf0f0);
        $gl->glLineWidth(1.0);
        $gl->glBegin(GL::GL_QUADS);
        $gl->glVertex2f(1.0,   10.0);
        $gl->glVertex2f(398.0, 10.0);
        $gl->glVertex2f(398.0, 19.0);
        $gl->glVertex2f(1.0,   19.0);
        $gl->glVertex2f(1.0,   40.0);
        $gl->glVertex2f(398.0, 40.0);
        $gl->glVertex2f(398.0, 49.0);
        $gl->glVertex2f(1.0,   49.0);
        $gl->glEnd();
        $gl->glDisable(GL::GL_LINE_STIPPLE);

        $gl->glColor4f($red=1.0, $green=0.0, $blue=0.0, $alpha=1.0);
        $gl->glLineWidth(2.0);
        $gl->glBegin(GL::GL_QUADS);
        $gl->glVertex2f(1.0,   20.0);
        $gl->glVertex2f(398.0, 20.0);
        $gl->glVertex2f(398.0, 29.0);
        $gl->glVertex2f(1.0,   29.0);
        $gl->glVertex2f(1.0,   50.0);
        $gl->glVertex2f(398.0, 50.0);
        $gl->glVertex2f(398.0, 59.0);
        $gl->glVertex2f(1.0,   59.0);
        $gl->glEnd();

        $fname = $gl->output();
        $gl->show();
        $this->assertTrue(true);
    }

    public function testPolygon()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        $gl = new GDGL($la);

        $display = $gl->get_display(null);
        $window = $gl->get_window($width=400, $height=300, $display);

        $gl->glClearColor($red=1.0, $green=1.0, $blue=1.0, $alpha=1.0);
        $gl->clear();

        $gl->glColor4f($red=0.0, $green=0.0, $blue=0.0, $alpha=1.0);
        $gl->glBegin(GL::GL_POLYGON);
        $gl->glVertex2f(1.0,    1.0);
        $gl->glVertex2f(398.0,  1.0);
        $gl->glVertex2f(398.0,  9.0);
        $gl->glVertex2f(200.0, 19.0);
        $gl->glVertex2f(1.0,    9.0);
        $gl->glEnd();

        $gl->glColor4f($red=0.0, $green=1.0, $blue=0.0, $alpha=1.0);
        $gl->glEnable(GL::GL_LINE_STIPPLE);
        $gl->glLineStipple(1, $style=0xf0f0);
        $gl->glLineWidth(1.0);
        $gl->glBegin(GL::GL_POLYGON);
        $gl->glVertex2f(1.0,   21.0);
        $gl->glVertex2f(398.0, 21.0);
        $gl->glVertex2f(398.0, 29.0);
        $gl->glVertex2f(200.0, 39.0);
        $gl->glVertex2f(1.0,   29.0);
        $gl->glEnd();
        $gl->glDisable(GL::GL_LINE_STIPPLE);

        $gl->glColor4f($red=1.0, $green=0.0, $blue=0.0, $alpha=1.0);
        $gl->glLineWidth(2.0);
        $gl->glBegin(GL::GL_POLYGON);
        $gl->glVertex2f(1.0,   41.0);
        $gl->glVertex2f(398.0, 41.0);
        $gl->glVertex2f(398.0, 49.0);
        $gl->glVertex2f(200.0, 59.0);
        $gl->glVertex2f(1.0,   49.0);
        $gl->glEnd();

        $fname = $gl->output();
        $gl->show();
        $this->assertTrue(true);
    }

    public function createImage()
    {
        $img = imagecreatetruecolor(100,100);
        $bg = imagecolorallocatealpha($img,255,255,255,0);
        $fg = imagecolorallocatealpha($img,255,0,0,0);
        imagefilledrectangle($img,0,0,99,99,$bg);
        imagerectangle($img,0,0,99,99,$fg);
        imageellipse($img,50,50,100,100,$fg);
        imagestring($img, $font=4, $x=30, $y=30, 'L   R', $fg);
        imagearc($img,50,50,60,60, +90-45,+90+45,$fg);
        imagepng($img,__DIR__.'/testimg.png');
        //system(__DIR__.'/testimg.png');
    }

    public function testImage()
    {
        //$this->createImage();
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        $gl = new GDGL($la);

        $display = $gl->get_display(null);
        $window = $gl->get_window($width=400, $height=300, $display);

        $gl->glClearColor($red=1.0, $green=1.0, $blue=1.0, $alpha=1.0);
        $gl->clear();

        $image = $gl->load_image(__DIR__.'/testimg.png');
        $gl->glPushMatrix();
        $gl->glTranslatef(200,150,0);
        $gl->renderImage($image, $centerx=200.0/2, $centery=100.0/2, $width=200.0, $height=100.0);
        $gl->glPopMatrix();
        $gl->flip();
        $fname = $gl->output();
        $gl->show();
        $this->assertTrue(true);
    }

    public function drar2DBox($gl,$w,$h,$x=null,$y=null)
    {
        $x = $x ?? 0;
        $y = $y ?? 0;
        $gl->glBegin(GL::GL_LINES);
        $gl->glVertex2f($x-$w/2,  $y-$h/2);
        $gl->glVertex2f($x+$w/2,  $y-$h/2);

        $gl->glVertex2f($x+$w/2,  $y-$h/2);
        $gl->glVertex2f($x+$w/2,  $y+$h/2);

        $gl->glVertex2f($x+$w/2,  $y+$h/2);
        $gl->glVertex2f($x-$w/2,  $y+$h/2);

        $gl->glVertex2f($x-$w/2,  $y+$h/2);
        $gl->glVertex2f($x-$w/2,  $y-$h/2);
        $gl->glEnd();
    }

    public function testMatrix2DLines()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        $gl = new GDGL($la);

        $display = $gl->get_display(null);
        $window = $gl->get_window($width=400, $height=300, $display);

        $gl->glClearColor($red=1.0, $green=1.0, $blue=1.0, $alpha=1.0);
        $gl->clear();

        $gl->glColor4f($red=0.0, $green=0.0, $blue=0.0, $alpha=1.0);
        $gl->glPushMatrix();
        $gl->glTranslatef(200,150,0);
        $this->drar2DBox($gl,100,50);
        $gl->glPopMatrix();

        $gl->glColor4f($red=1.0, $green=0.0, $blue=0.0, $alpha=1.0);
        $gl->glPushMatrix();
        $gl->glTranslatef(100,100,0);
        $gl->glRotatef(45,0,0,1);
        $this->drar2DBox($gl,100,50);
        $gl->glPopMatrix();

        $gl->glColor4f($red=0.0, $green=1.0, $blue=0.0, $alpha=1.0);
        $gl->glPushMatrix();
        $gl->glTranslatef(300,250,0);
        $gl->glRotatef(45,0,0,1);
        $gl->glScalef(0.5,1,1);
        $this->drar2DBox($gl,100,50);
        $gl->glPopMatrix();

        $gl->flip();
        $fname = $gl->output();
        $gl->show();
        $this->assertTrue(true);
    }

    public function drarImage($gl,$image,$w,$h)
    {
        $gl->renderImage($image, $w/2, $h/2, $w, $h);
    }

    public function testMatrixImage()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        $gl = new GDGL($la);

        $display = $gl->get_display(null);
        $window = $gl->get_window($width=400, $height=300, $display);
        $image = $gl->load_image(__DIR__.'/testimg.png');

        $gl->glClearColor($red=1.0, $green=1.0, $blue=1.0, $alpha=1.0);
        $gl->clear();

        $gl->glColor4f($red=0.0, $green=0.0, $blue=0.0, $alpha=1.0);
        $gl->glPushMatrix();
        $gl->glTranslatef(200,150,0);
        $this->drarImage($gl,$image,100,50);
        $gl->glPopMatrix();

        $gl->glColor4f($red=1.0, $green=0.0, $blue=0.0, $alpha=1.0);
        $gl->glPushMatrix();
        $gl->glTranslatef(100,100,0);
        $gl->glRotatef(45,0,0,1);
        $this->drarImage($gl,$image,100,50);
        $gl->glPopMatrix();

        $gl->glColor4f($red=0.0, $green=1.0, $blue=0.0, $alpha=1.0);
        $gl->glPushMatrix();
        $gl->glTranslatef(300,250,0);
        $gl->glRotatef(45,0,0,1);
        $gl->glScalef(0.5,1,1);
        $this->drarImage($gl,$image,100,50);
        $gl->glPopMatrix();

        $gl->flip();
        $fname = $gl->output();
        $gl->show();
        $this->assertTrue(true);
    }

    public function drar3DBox($gl,$w,$h,$d,$x=null,$y=null,$z=null)
    {
        $x = $x ?? 0;
        $y = $y ?? 0;
        $z = $z ?? 0;
        $gl->glBegin(GL::GL_LINES);
        // floor
        $gl->glVertex3f($x-$w/2, $y-$h/2, $z-$d/2);
        $gl->glVertex3f($x+$w/2, $y-$h/2, $z-$d/2);

        $gl->glVertex3f($x+$w/2, $y-$h/2, $z-$d/2);
        $gl->glVertex3f($x+$w/2, $y+$h/2, $z-$d/2);

        $gl->glVertex3f($x+$w/2, $y+$h/2, $z-$d/2);
        $gl->glVertex3f($x-$w/2, $y+$h/2, $z-$d/2);

        $gl->glVertex3f($x-$w/2, $y+$h/2, $z-$d/2);
        $gl->glVertex3f($x-$w/2, $y-$h/2, $z-$d/2);

        // pillar
        $gl->glVertex3f($x-$w/2, $y-$h/2, $z-$d/2);
        $gl->glVertex3f($x-$w/2, $y-$h/2, $z+$d/2);

        $gl->glVertex3f($x+$w/2, $y-$h/2, $z-$d/2);
        $gl->glVertex3f($x+$w/2, $y-$h/2, $z+$d/2);

        $gl->glVertex3f($x+$w/2, $y+$h/2, $z-$d/2);
        $gl->glVertex3f($x+$w/2, $y+$h/2, $z+$d/2);

        $gl->glVertex3f($x-$w/2, $y+$h/2, $z-$d/2);
        $gl->glVertex3f($x-$w/2, $y+$h/2, $z+$d/2);

        // ceiling
        $gl->glVertex3f($x-$w/2, $y-$h/2, $z+$d/2);
        $gl->glVertex3f($x+$w/2, $y-$h/2, $z+$d/2);

        $gl->glVertex3f($x+$w/2, $y-$h/2, $z+$d/2);
        $gl->glVertex3f($x+$w/2, $y+$h/2, $z+$d/2);

        $gl->glVertex3f($x+$w/2, $y+$h/2, $z+$d/2);
        $gl->glVertex3f($x-$w/2, $y+$h/2, $z+$d/2);

        $gl->glVertex3f($x-$w/2, $y+$h/2, $z+$d/2);
        $gl->glVertex3f($x-$w/2, $y-$h/2, $z+$d/2);
        $gl->glEnd();
    }

    public function testMatrix3DLines()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        $gl = new GDGL($la);

        $display = $gl->get_display(null);
        $window = $gl->get_window($width=400, $height=300, $display);

        $gl->glClearColor($red=1.0, $green=1.0, $blue=1.0, $alpha=1.0);
        $gl->clear();

        $gl->glColor4f($red=0.0, $green=0.0, $blue=0.0, $alpha=1.0);
        $gl->glPushMatrix();
        $gl->glTranslatef(200,150,0);
        $gl->glRotatef(45,0,0,1);
        $gl->glRotatef(45,0,1,0);
        $gl->glRotatef(45,1,0,0);
        $this->drar3DBox($gl,50,50,100);
        $gl->glPopMatrix();

        $gl->glColor4f($red=1.0, $green=0.0, $blue=0.0, $alpha=1.0);
        $gl->glPushMatrix();
        $gl->glTranslatef(100,100,0);
        $gl->glRotatef(45,0,0,1);
        $gl->glRotatef(45,0,1,0);
        $gl->glRotatef(45,1,0,0);
        $this->drar3DBox($gl,50,50,100);
        $gl->glPopMatrix();

        $gl->glColor4f($red=0.0, $green=1.0, $blue=0.0, $alpha=1.0);
        $gl->glPushMatrix();
        $gl->glTranslatef(300,200,0);
        $gl->glRotatef(45,0,0,1);
        $gl->glRotatef(45,0,1,0);
        $gl->glRotatef(45,1,0,0);
        $gl->glScalef(0.5,1,1);
        $this->drar3DBox($gl,50,50,100);
        $gl->glPopMatrix();

        $gl->flip();
        $fname = $gl->output();
        $gl->show();
        $this->assertTrue(true);
    }

    public function getPlotConfig()
    {
        return [
            'renderer.skipCleaning' => true,
            'renderer.skipRunViewer' => getenv('TRAVIS_PHP_VERSION') ? true : false,
        ];
    }

    public function testGetImageData()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        $gl = new GDGL($la);

        $display = $gl->get_display(null);
        $window = $gl->get_window($width=100, $height=100, $display);

        $gl->glClearColor($red=1.0, $green=1.0, $blue=1.0, $alpha=1.0);
        $gl->clear();

        $gl->glColor4f($red=0.0, $green=1.0, $blue=0.0, $alpha=1.0);
        $gl->glBegin(GL::GL_LINES);
        $gl->glVertex2f( 0.0,  0.0);
        $gl->glVertex2f(99.0,  0.0);
        $gl->glVertex2f(99.0,  0.0);
        $gl->glVertex2f(99.0, 99.0);
        $gl->glVertex2f(99.0, 99.0);
        $gl->glVertex2f( 0.0, 99.0);
        $gl->glVertex2f( 0.0, 99.0);
        $gl->glVertex2f( 0.0,  0.0);
        $gl->glEnd();

        $image = $gl->load_image(__DIR__.'/testimg.png');
        $gl->glPushMatrix();
        $gl->glTranslatef(50,50,0);
        $gl->renderImage($image, $centerx=80.0/2, $centery=80.0/2, $width=80.0, $height=80.0);
        $gl->glPopMatrix();

        $gl->flip();
        $array = $gl->get_image_data();
        $this->assertInstanceof(NDArray::class,$array);
        $plt = new Plot($this->getPlotConfig(),$mo);
        $plt->imshow($array);
        $plt->show();
    }

    public function testAnimation()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        $gl = new GDGL($la);

        $display = $gl->get_display(null);
        $window = $gl->get_window($width=100, $height=100, $display);

        $gl->glClearColor($red=1.0, $green=1.0, $blue=1.0, $alpha=1.0);
        $gl->glColor4f($red=0.0, $green=1.0, $blue=0.0, $alpha=1.0);

        for($i=0;$i<90;$i+=10) {
            $gl->clear();
            $gl->glPushMatrix();
            $gl->glTranslatef(50,50,0);
            $gl->glRotatef($i,0,0,1);
            $gl->glBegin(GL::GL_LINES);
            $gl->glVertex2f(-20.0, -20.0);
            $gl->glVertex2f( 20.0, -20.0);
            $gl->glVertex2f( 20.0, -20.0);
            $gl->glVertex2f( 20.0,  20.0);
            $gl->glVertex2f( 20.0,  20.0);
            $gl->glVertex2f(-20.0,  20.0);
            $gl->glVertex2f(-20.0,  20.0);
            $gl->glVertex2f(-20.0, -20.0);
            $gl->glEnd();
            $gl->glPopMatrix();
            $gl->output();
        }
        $gl->show(true,100);
        $this->assertTrue(true);
    }
}
