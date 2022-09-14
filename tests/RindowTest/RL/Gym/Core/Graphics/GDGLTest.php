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

    public function getMetadata()
    {
        return [
            'render.skipCleaning' => true,
            'render.skipRunViewer' => getenv('TRAVIS_PHP_VERSION') ? true : false,
        ];
    }

    public function newGDGL($la)
    {
        return new GDGL($la,config:$this->getMetadata());
    }

    public function testLinesNormal()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        $gl = $this->newGDGL($la);

        $display = $gl->get_display(null);
        $window = $gl->createWindow($width=400, $height=300, $display);
        $rightedge = ($width/2-1)/($width/2);
        $topedge = ($height/2-1)/($height/2);

        //$gl->glViewport($width/4,$height/4,$width/2,$height/2);

        $gl->glClearColor($red=1.0, $green=1.0, $blue=1.0, $alpha=1.0);
        $gl->glClear(GL::GL_COLOR_BUFFER_BIT);

        $gl->glBegin(GL::GL_LINES);
        $gl->glColor4f($red=1.0, $green=0.0, $blue=0.0, $alpha=1.0);
        $gl->glVertex2f(-1.0, -1.0);
        $gl->glVertex2f($rightedge, -1.0);
        $gl->glColor4f($red=0.0, $green=1.0, $blue=0.0, $alpha=1.0);
        $gl->glVertex2f($rightedge, -1.0);
        $gl->glVertex2f($rightedge,  $topedge);
        $gl->glColor4f($red=0.0, $green=0.0, $blue=1.0, $alpha=1.0);
        $gl->glVertex2f($rightedge,  $topedge);
        $gl->glVertex2f(-1.0,  $topedge);
        $gl->glColor4f($red=1.0, $green=1.0, $blue=0.0, $alpha=1.0);
        $gl->glVertex2f(-1.0,  $topedge);
        $gl->glVertex2f(-1.0, -1.0);
        $gl->glEnd();

        $gl->glColor4f($red=0.0, $green=1.0, $blue=0.0, $alpha=1.0);
        $gl->glEnable(GL::GL_LINE_STIPPLE);
        $gl->glLineWidth(1.0);
        $gl->glBegin(GL::GL_LINES);
        $gl->glLineStipple(3, $style=0x00ff);
        $gl->glVertex2f( 0.0, 0.0);
        $gl->glVertex2f(-0.9, 0.0);
        $gl->glLineStipple(3, $style=0x0f0f);
        $gl->glVertex2f(-0.9, 0.0);
        $gl->glVertex2f(-0.9, 0.9);
        $gl->glLineStipple(3, $style=0x3333);
        $gl->glVertex2f(-0.9, 0.9);
        $gl->glVertex2f( 0.0, 0.9);
        $gl->glLineStipple(3, $style=0x18ff);
        $gl->glVertex2f( 0.0, 0.9);
        $gl->glVertex2f( 0.0, 0.0);
        $gl->glLineStipple(3, $style=0x5555);
        $gl->glVertex2f( 0.0, 0.0);
        $gl->glVertex2f(-0.9, 0.9);
        $gl->glColor4f($red=1.0, $green=1.0, $blue=0.0, $alpha=1.0);
        $gl->glLineStipple(1, $style=0x00ff);
        $gl->glVertex2f(-0.1, 0.1);
        $gl->glVertex2f(-0.8, 0.1);
        $gl->glLineStipple(1, $style=0x0f0f);
        $gl->glVertex2f(-0.8, 0.1);
        $gl->glVertex2f(-0.8, 0.8);
        $gl->glLineStipple(1, $style=0x3333);
        $gl->glVertex2f(-0.8, 0.8);
        $gl->glVertex2f(-0.1, 0.8);
        $gl->glLineStipple(1, $style=0x18ff);
        $gl->glVertex2f(-0.1, 0.8);
        $gl->glVertex2f(-0.1, 0.1);
        $gl->glLineStipple(1, $style=0x5555);
        $gl->glVertex2f(-0.1, 0.1);
        $gl->glVertex2f(-0.8, 0.8);
        $gl->glEnd();
        $gl->glDisable(GL::GL_LINE_STIPPLE);

        $gl->glColor4f($red=1.0, $green=0.0, $blue=0.0, $alpha=1.0);
        $gl->glBegin(GL::GL_LINES);
        $gl->glLineWidth(2.0);
        $gl->glVertex2f(0.0, 0.0);
        $gl->glVertex2f(0.9, 0.0);
        $gl->glLineWidth(3.0);
        $gl->glVertex2f(0.9, 0.0);
        $gl->glVertex2f(0.9,-0.9);
        $gl->glLineWidth(4.0);
        $gl->glVertex2f(0.9,-0.9);
        $gl->glVertex2f(0.0,-0.9);
        $gl->glVertex2f(0.0,-0.9);
        $gl->glVertex2f(0.0, 0.0);
        $gl->glLineWidth(1.0);
        $gl->glVertex2f(0.0, 0.0);
        $gl->glVertex2f(0.9,-0.9);
        $gl->glEnd();

        for($a=0;$a<=90;$a+=30) {
            $gl->glPushMatrix();
            $gl->glTranslatef(0.5,0.5,0.0);
            $gl->glRotatef($a,0,0,1);
            $gl->glScalef(0.1,0.25,1.0);
            $gl->glBegin(GL::GL_LINES);
            $gl->glColor4f($red=1.0, $green=0.0, $blue=0.0, $alpha=1.0);
            $gl->glVertex2f(-1.0, -1.0);
            $gl->glVertex2f( 1.0, -1.0);
            $gl->glColor4f($red=0.0, $green=1.0, $blue=0.0, $alpha=1.0);
            $gl->glVertex2f( 1.0, -1.0);
            $gl->glVertex2f( 1.0,  1.0);
            $gl->glColor4f($red=0.0, $green=0.0, $blue=1.0, $alpha=1.0);
            $gl->glVertex2f( 1.0,  1.0);
            $gl->glVertex2f(-1.0,  1.0);
            $gl->glColor4f($red=1.0, $green=1.0, $blue=0.0, $alpha=1.0);
            $gl->glVertex2f(-1.0,  1.0);
            $gl->glVertex2f(-1.0, -1.0);
            $gl->glEnd();
            $gl->glPopMatrix();
        }

        $fname = $gl->output();
        $gl->show();
        $this->assertTrue(true);
    }

    public function testLineLoop()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        $gl = $this->newGDGL($la);

        $display = $gl->get_display(null);
        $window = $gl->createWindow($width=400, $height=300, $display);

        $gl->glClearColor($red=1.0, $green=1.0, $blue=1.0, $alpha=1.0);
        $gl->glClear(GL::GL_COLOR_BUFFER_BIT);

        $gl->glColor4f($red=0.0, $green=0.0, $blue=0.0, $alpha=1.0);
        $gl->glBegin(GL::GL_LINE_LOOP);
        $gl->glVertex2f(0.1, 0.1);
        $gl->glVertex2f(0.9, 0.1);
        $gl->glVertex2f(0.9, 0.9);
        $gl->glEnd();

        $gl->glColor4f($red=0.0, $green=1.0, $blue=0.0, $alpha=1.0);
        $gl->glEnable(GL::GL_LINE_STIPPLE);
        $gl->glLineStipple(1, $style=0xf0f0);
        $gl->glLineWidth(1.0);
        $gl->glBegin(GL::GL_LINE_LOOP);
        $gl->glVertex2f(-0.1, 0.1);
        $gl->glVertex2f(-0.9, 0.1);
        $gl->glVertex2f(-0.9, 0.9);
        $gl->glEnd();
        $gl->glDisable(GL::GL_LINE_STIPPLE);

        $gl->glColor4f($red=1.0, $green=0.0, $blue=0.0, $alpha=1.0);
        $gl->glLineWidth(2.0);
        $gl->glBegin(GL::GL_LINE_LOOP);
        $gl->glVertex2f(-0.1, -0.1);
        $gl->glVertex2f(-0.9, -0.1);
        $gl->glVertex2f(-0.9, -0.9);
        $gl->glEnd();

        $fname = $gl->output();
        $gl->show();
        $this->assertTrue(true);
    }

    public function testLineStrip()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        $gl = $this->newGDGL($la);

        $display = $gl->get_display(null);
        $window = $gl->createWindow($width=400, $height=300, $display);

        $gl->glClearColor($red=1.0, $green=1.0, $blue=1.0, $alpha=1.0);
        $gl->glClear(GL::GL_COLOR_BUFFER_BIT);

        $gl->glColor4f($red=0.0, $green=0.0, $blue=0.0, $alpha=1.0);
        $gl->glBegin(GL::GL_LINE_STRIP);
        $gl->glVertex2f(0.1, 0.1);
        $gl->glVertex2f(0.9, 0.1);
        $gl->glVertex2f(0.9, 0.9);
        $gl->glEnd();

        $gl->glColor4f($red=0.0, $green=1.0, $blue=0.0, $alpha=1.0);
        $gl->glEnable(GL::GL_LINE_STIPPLE);
        $gl->glLineStipple(1, $style=0xf0f0);
        $gl->glLineWidth(1.0);
        $gl->glBegin(GL::GL_LINE_STRIP);
        $gl->glVertex2f(-0.1, 0.1);
        $gl->glVertex2f(-0.9, 0.1);
        $gl->glVertex2f(-0.9, 0.9);
        $gl->glEnd();
        $gl->glDisable(GL::GL_LINE_STIPPLE);

        $gl->glColor4f($red=1.0, $green=0.0, $blue=0.0, $alpha=1.0);
        $gl->glLineWidth(2.0);
        $gl->glBegin(GL::GL_LINE_STRIP);
        $gl->glVertex2f(-0.1, -0.1);
        $gl->glVertex2f(-0.9, -0.1);
        $gl->glVertex2f(-0.9, -0.9);
        $gl->glEnd();

        $fname = $gl->output();
        $gl->show();
        $this->assertTrue(true);
    }

    public function testTriangles()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        $gl = $this->newGDGL($la);

        $display = $gl->get_display(null);
        $window = $gl->createWindow($width=400, $height=300, $display);

        $gl->glClearColor($red=1.0, $green=1.0, $blue=1.0, $alpha=1.0);
        $gl->glClear(GL::GL_COLOR_BUFFER_BIT);

        $gl->glColor4f($red=0.0, $green=0.0, $blue=0.0, $alpha=1.0);
        $gl->glBegin(GL::GL_TRIANGLES);
        $gl->glVertex2f(0.1,  0.1);
        $gl->glVertex2f(0.5,  0.1);
        $gl->glVertex2f(0.3,  0.9);
        $gl->glVertex2f(0.5,  0.1);
        $gl->glVertex2f(0.9,  0.1);
        $gl->glVertex2f(0.7,  0.9);
        $gl->glEnd();

        $gl->glColor4f($red=0.0, $green=1.0, $blue=0.0, $alpha=1.0);
        $gl->glEnable(GL::GL_LINE_STIPPLE);
        $gl->glLineStipple(1, $style=0xf0f0);
        $gl->glLineWidth(1.0);
        $gl->glBegin(GL::GL_TRIANGLES);
        $gl->glVertex2f(-0.1,  0.1);
        $gl->glVertex2f(-0.5,  0.1);
        $gl->glVertex2f(-0.3,  0.9);
        $gl->glVertex2f(-0.5,  0.1);
        $gl->glVertex2f(-0.9,  0.1);
        $gl->glVertex2f(-0.7,  0.9);
        $gl->glEnd();
        $gl->glDisable(GL::GL_LINE_STIPPLE);

        $gl->glColor4f($red=1.0, $green=0.0, $blue=0.0, $alpha=1.0);
        $gl->glLineWidth(2.0);
        $gl->glBegin(GL::GL_TRIANGLES);
        $gl->glVertex2f(-0.1, -0.1);
        $gl->glVertex2f(-0.5, -0.1);
        $gl->glVertex2f(-0.3, -0.9);
        $gl->glVertex2f(-0.5, -0.1);
        $gl->glVertex2f(-0.9, -0.1);
        $gl->glVertex2f(-0.7, -0.9);
        $gl->glEnd();

        $fname = $gl->output();
        $gl->show();
        $this->assertTrue(true);
    }

    public function testQuads()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        $gl = $this->newGDGL($la);

        $display = $gl->get_display(null);
        $window = $gl->createWindow($width=400, $height=300, $display);

        $gl->glClearColor($red=1.0, $green=1.0, $blue=1.0, $alpha=1.0);
        $gl->glClear(GL::GL_COLOR_BUFFER_BIT);

        $gl->glColor4f($red=0.0, $green=0.0, $blue=0.0, $alpha=1.0);
        $gl->glBegin(GL::GL_QUADS);
        $gl->glVertex2f(0.1, 0.1);
        $gl->glVertex2f(0.4, 0.1);
        $gl->glVertex2f(0.4, 0.9);
        $gl->glVertex2f(0.1, 0.9);
        $gl->glVertex2f(0.5, 0.1);
        $gl->glVertex2f(0.8, 0.1);
        $gl->glVertex2f(0.8, 0.9);
        $gl->glVertex2f(0.5, 0.9);
        $gl->glEnd();

        $gl->glColor4f($red=0.0, $green=1.0, $blue=0.0, $alpha=1.0);
        $gl->glEnable(GL::GL_LINE_STIPPLE);
        $gl->glLineStipple(1, $style=0xf0f0);
        $gl->glLineWidth(1.0);
        $gl->glBegin(GL::GL_QUADS);
        $gl->glVertex2f(-0.1, 0.1);
        $gl->glVertex2f(-0.4, 0.1);
        $gl->glVertex2f(-0.4, 0.9);
        $gl->glVertex2f(-0.1, 0.9);
        $gl->glVertex2f(-0.5, 0.1);
        $gl->glVertex2f(-0.8, 0.1);
        $gl->glVertex2f(-0.8, 0.9);
        $gl->glVertex2f(-0.5, 0.9);
        $gl->glEnd();
        $gl->glDisable(GL::GL_LINE_STIPPLE);

        $gl->glColor4f($red=1.0, $green=0.0, $blue=0.0, $alpha=1.0);
        $gl->glLineWidth(2.0);
        $gl->glBegin(GL::GL_QUADS);
        $gl->glVertex2f(-0.1, -0.1);
        $gl->glVertex2f(-0.4, -0.1);
        $gl->glVertex2f(-0.4, -0.9);
        $gl->glVertex2f(-0.1, -0.9);
        $gl->glVertex2f(-0.5, -0.1);
        $gl->glVertex2f(-0.8, -0.1);
        $gl->glVertex2f(-0.8, -0.9);
        $gl->glVertex2f(-0.5, -0.9);
        $gl->glEnd();

        $fname = $gl->output();
        $gl->show();
        $this->assertTrue(true);
    }

    public function testPolygon()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        $gl = $this->newGDGL($la);

        $display = $gl->get_display(null);
        $window = $gl->createWindow($width=400, $height=300, $display);

        $gl->glClearColor($red=1.0, $green=1.0, $blue=1.0, $alpha=1.0);
        $gl->glClear(GL::GL_COLOR_BUFFER_BIT);

        $gl->glColor4f($red=0.0, $green=0.0, $blue=0.0, $alpha=1.0);
        $gl->glBegin(GL::GL_POLYGON);
        $gl->glVertex2f(0.1, 0.1);
        $gl->glVertex2f(0.9, 0.1);
        $gl->glVertex2f(0.9, 0.5);
        $gl->glVertex2f(0.5, 0.9);
        $gl->glVertex2f(0.1, 0.5);
        $gl->glEnd();

        $gl->glColor4f($red=0.0, $green=1.0, $blue=0.0, $alpha=1.0);
        $gl->glEnable(GL::GL_LINE_STIPPLE);
        $gl->glLineStipple(1, $style=0xf0f0);
        $gl->glLineWidth(1.0);
        $gl->glBegin(GL::GL_POLYGON);
        $gl->glVertex2f(-0.1, 0.1);
        $gl->glVertex2f(-0.9, 0.1);
        $gl->glVertex2f(-0.9, 0.5);
        $gl->glVertex2f(-0.5, 0.9);
        $gl->glVertex2f(-0.1, 0.5);
        $gl->glEnd();
        $gl->glDisable(GL::GL_LINE_STIPPLE);

        $gl->glColor4f($red=1.0, $green=0.0, $blue=0.0, $alpha=1.0);
        $gl->glLineWidth(2.0);
        $gl->glBegin(GL::GL_POLYGON);
        $gl->glVertex2f(-0.1, -0.1);
        $gl->glVertex2f(-0.9, -0.1);
        $gl->glVertex2f(-0.9, -0.5);
        $gl->glVertex2f(-0.5, -0.9);
        $gl->glVertex2f(-0.1, -0.5);
        $gl->glEnd();

        $fname = $gl->output();
        $gl->show();
        $this->assertTrue(true);
    }

    public function createImage()
    {
        $width = 200;
        $height = 100;
        $centerx = (int)ceil($width/2);
        $centery = (int)ceil($height/2);
        $img = imagecreatetruecolor($width,$height);
        imagealphablending($img,false);
        imagesavealpha($img,true);
        $bg = imagecolorallocatealpha($img,0,0,0,127);
        $fg = imagecolorallocatealpha($img,255,0,0,0);
        imagefilledrectangle($img,0,0,$width-1,$height-1,$bg);
        imagerectangle($img,0,0,$width-1,$height-1,$fg);
        imageellipse($img,$centerx,$centery,min($width,$height),min($width,$height),$fg);
        imagestring($img, $font=4, $centerx-20, $centery-20, 'L   R', $fg);
        imagearc($img,$centerx,$centery,
                    min($width,$height)*0.6,min($width,$height)*0.6,
                    +90-45,+90+45,$fg);
        imagepng($img,__DIR__.'/testimg.png');
        system(__DIR__.'/testimg.png');
    }

    public function testRenderImage()
    {
        //$this->createImage();
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        $gl = $this->newGDGL($la);

        $display = $gl->get_display(null);
        $window = $gl->createWindow($width=600, $height=300, $display);

        $gl->glClearColor($red=1.0, $green=1.0, $blue=1.0, $alpha=1.0);
        $gl->glClear(GL::GL_COLOR_BUFFER_BIT);
        $transx = 0.0; $transy = 0;
        $angle = 0;
        $scalex = 0.5; $scaley = 0.25;
        $fname = __DIR__.'/testimg.png';
        $tests = [
        //  $transx,$transy, $angle, $scalex,$scaley, $centerx,$centery, $width,$height
            [-0.7, 0.7,          0,        0.2, 0.1,       100,50,           200,100],
            [ 0.0, 0.7,          30,       0.2, 0.1,       100,50,           200,100],
            [ 0.7, 0.7,          120,      0.2, 0.1,       100,50,           200,100],
            [-0.7, 0.0,          45,       0.2, 0.2,       100,50,           200,100],
            [ 0.0, 0.0,          30,       0.1, 0.2,       100,50,           200,100],
            [ 0.7, 0.0,          30,      -0.2, 0.1,       100,50,           200,100],
            [-0.7,-0.7,          30,       0.2,-0.1,       100,50,           200,100],
            [ 0.0,-0.7,          30,       0.2, 0.2,       100,50,           100,100],
            [ 0.7,-0.7,          30,       0.2, 0.2,        50,50,           100,100],
        ];

        foreach($tests as [$transx,$transy,$angle,$scalex,$scaley,$centerx,$centery,$width,$height]) {
            $gl->glPushMatrix();
            $gl->glTranslatef($transx,$transy,0);
            $gl->glRotatef($angle,0,0,1);
            $gl->glScalef($scalex,$scaley,1.0);
    
            $gl->glColor4f($red=0.0, $green=0.0, $blue=1.0, $alpha=1.0);
            $gl->glBegin(GL::GL_QUADS);
            $gl->glVertex2f( 0.0,-1.0);
            $gl->glVertex2f( 1.0,-1.0);
            $gl->glVertex2f( 1.0, 1.0);
            $gl->glVertex2f( 0.0, 1.0);
            $gl->glEnd();
    
            $img = $gl->load_image($fname);
            $gl->renderImage($img,$centerx,$centery,$width,$height);
    
            $gl->glColor4f($red=0.0, $green=1.0, $blue=0.0, $alpha=1.0);
            $gl->glBegin(GL::GL_LINE_STRIP);
            $gl->glVertex2f( 0.0,-1.0);
            $gl->glVertex2f( 1.0,-1.0);
            $gl->glVertex2f( 1.0, 1.0);
            $gl->glVertex2f(-1.0, 1.0);
            $gl->glVertex2f(-1.0,-1.0);
            $gl->glVertex2f( 0.0,-1.0);
            $gl->glVertex2f( 0.0, 0.7);
            $gl->glVertex2f(-0.7, 0.0);
            $gl->glVertex2f( 0.7, 0.0);
            $gl->glEnd();
    
            $gl->glPopMatrix();
        }

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
        $gl = $this->newGDGL($la);

        $display = $gl->get_display(null);
        $window = $gl->createWindow($width=400, $height=300, $display);

        $gl->glClearColor($red=1.0, $green=1.0, $blue=1.0, $alpha=1.0);
        $gl->glClear(GL::GL_COLOR_BUFFER_BIT);
        $gl->glTranslatef(-1,-1,0);
        $gl->glScalef(2/$width,2/$height,1);

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

        $fname = $gl->output();
        $gl->show();
        $this->assertTrue(true);
    }

    public function drarImage($gl,$image,$w,$h)
    {
        $gl->renderImage($image, $w/2, $h/2, $w, $h);
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
        $gl = $this->newGDGL($la);

        $display = $gl->get_display(null);
        $window = $gl->createWindow($width=400, $height=300, $display);

        $gl->glClearColor($red=1.0, $green=1.0, $blue=1.0, $alpha=1.0);
        $gl->glClear(GL::GL_COLOR_BUFFER_BIT);

        $gl->glTranslatef(-1,-1,0);
        $gl->glScalef(2/$width,2/$height,1);

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
        $gl = $this->newGDGL($la);

        $display = $gl->get_display(null);
        $window = $gl->createWindow($width=100, $height=100, $display);

        $gl->glClearColor($red=1.0, $green=1.0, $blue=1.0, $alpha=1.0);
        $gl->glClear(GL::GL_COLOR_BUFFER_BIT);

        $gl->glColor4f($red=0.0, $green=1.0, $blue=0.0, $alpha=1.0);
        $gl->glBegin(GL::GL_LINE_LOOP);
        $gl->glVertex2f( 0.0,  0.0);
        $gl->glVertex2f( 0.9,  0.0);
        $gl->glVertex2f( 0.9,  0.9);
        $gl->glVertex2f( 0.0,  0.9);
        $gl->glEnd();

        $image = $gl->load_image(__DIR__.'/testimg.png');
        $gl->glPushMatrix();
        $gl->glTranslatef(-0.5,-0.5,0.0);
        $gl->glScalef(0.5,0.5,0.5);
        $gl->renderImage($image, $centerx=200/2, $centery=100/2, $width=100, $height=100);
        $gl->glPopMatrix();

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
        $gl = $this->newGDGL($la);

        $display = $gl->get_display(null);
        $window = $gl->createWindow($width=100, $height=100, $display);

        $gl->glClearColor($red=1.0, $green=1.0, $blue=1.0, $alpha=1.0);
        $gl->glColor4f($red=0.0, $green=1.0, $blue=0.0, $alpha=1.0);

        for($i=0;$i<90;$i+=10) {
            $gl->glClear(GL::GL_COLOR_BUFFER_BIT);
            $gl->glPushMatrix();
            $gl->glTranslatef(0,0,0);
            $gl->glRotatef($i,0,0,1);
            $gl->glBegin(GL::GL_LINE_LOOP);
            $gl->glVertex2f(-0.5,  -0.5);
            $gl->glVertex2f( 0.5,  -0.5);
            $gl->glVertex2f( 0.5,   0.5);
            $gl->glVertex2f(-0.5,   0.5);
            $gl->glEnd();
            $gl->glPopMatrix();
            $gl->output();
        }
        $gl->show(true,100);
        $this->assertTrue(true);
    }

    public function testCleanup()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        $gl = $this->newGDGL($la);
        $gl->cleanup();
        $this->assertTrue(true);
    }
}
