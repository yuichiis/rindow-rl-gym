<?php
namespace RindowTest\RL\Gym\ClassicControl\Rendering\RenderingTest;

use PHPUnit\Framework\TestCase;
use Interop\Polite\Math\Matrix\NDArray;
use Rindow\Math\Matrix\MatrixOperator;
use Rindow\Math\Plot\Plot;
use Rindow\RL\Gym\Core\Graphics\GDGL;
use Rindow\RL\Gym\Core\Graphics\GL;
use Rindow\RL\Gym\ClassicControl\Rendering\Rendering;
use Rindow\RL\Gym\ClassicControl\Rendering\RenderFactory;

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
        ];
    }

    public function newRendering($la)
    {
        $factory = new RenderFactory($la,'gd',metadata:$this->getMetadata());
        return $factory->factory();
    }

    public function testFilledPolygon()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        $rendering = $this->newRendering($la);

        $viewer = $rendering->Viewer($width=640, $height=480);
        $trans = $rendering->Transform([320,240]);

        $geom1 = $rendering->FilledPolygon([
            [-1,-1], [ 0,-1], [ 0, 0], [-1, 0],
        ]);
        $geom1->set_color(0.0, 1.0, 0.0);
        $geom1->add_attr($trans);
        $viewer->add_geom($geom1);
        $geom2 = $rendering->FilledPolygon([
            [ 1, 1], [ 0, 1], [ 0, 0], [ 1, 0],
        ]);
        $geom2->set_color(0.0, 0.0, 1.0);
        $geom2->add_attr($trans);
        $viewer->add_geom($geom2);

        $trans->set_scale(200.0,200.0);
        $trans->set_rotation(-45*M_PI/180);
        $viewer->render();
        $viewer->show();
        $this->assertTrue(true);
    }

    public function testLine()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        $rendering = $this->newRendering($la);

        $viewer = $rendering->Viewer($width=640, $height=480);
        $trans = $rendering->Transform([320,240]);

        $geom1 = $rendering->Line([-1,-1], [ 0,-1]);
        $geom1->set_color(0.0, 1.0, 0.0);
        $geom1->add_attr($trans);
        $viewer->add_geom($geom1);
        $geom2 = $rendering->Line([ 1, 1], [ 0, 1]);
        $geom2->set_color(0.0, 0.0, 1.0);
        $geom2->set_linewidth(5);
        $geom2->add_attr($trans);
        $viewer->add_geom($geom2);

        $trans->set_scale(200.0,200.0);
        $trans->set_rotation(-45*M_PI/180);
        $viewer->render();
        $viewer->show();
        $this->assertTrue(true);
    }

    public function testPoint()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        $rendering = $this->newRendering($la);

        $viewer = $rendering->Viewer($width=640, $height=480);
        $trans = $rendering->Transform([320,240]);

        $geom1 = $rendering->Point();
        $geom1->set_color(0.0, 1.0, 0.0);
        $geom1->add_attr($trans);
        $viewer->add_geom($geom1);

        $trans->set_scale(200.0,200.0);
        $trans->set_rotation(-45*M_PI/180);
        $viewer->render();
        $viewer->show();
        $this->assertTrue(true);
    }

    public function testPolyline()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        $rendering = $this->newRendering($la);

        $viewer = $rendering->Viewer($width=640, $height=480);
        $trans = $rendering->Transform([320,240]);

        $geom1 = $rendering->Polyline([
            [-1,-1], [ 0,-1], [ 0, 0], [-1, 0],
        ], true);
        $geom1->set_color(0.0, 1.0, 0.0);
        $geom1->add_attr($trans);
        $viewer->add_geom($geom1);
        $geom2 = $rendering->Polyline([
            [ 1, 1], [ 0, 1], [ 0, 0], [ 1, 0],
        ], false);
        $geom2->set_color(0.0, 0.0, 1.0);
        $geom2->set_linewidth(5);
        $geom2->add_attr($trans);
        $viewer->add_geom($geom2);

        $trans->set_scale(200.0,200.0);
        $trans->set_rotation(-45*M_PI/180);
        $viewer->render();
        $viewer->show();
        $this->assertTrue(true);
    }

    public function testCompound()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        $rendering = $this->newRendering($la);

        $viewer = $rendering->Viewer($width=640, $height=480);
        $trans = $rendering->Transform([320,240]);

        $geom1 = $rendering->FilledPolygon([
            [-1,-1], [ 0,-1], [ 0, 0], [-1, 0],
        ]);
        $geom2 = $rendering->FilledPolygon([
            [ 1, 1], [ 0, 1], [ 0, 0], [ 1, 0],
        ]);

        $comp = $rendering->Compound([$geom1,$geom2]);
        $comp->set_color(0.0, 0.0, 1.0);
        $comp->add_attr($trans);
        $viewer->add_geom($comp);

        $trans->set_scale(200.0,200.0);
        $trans->set_rotation(-45*M_PI/180);
        $viewer->render();
        $viewer->show();
        $this->assertTrue(true);
    }

    public function testLineStyle()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        $rendering = $this->newRendering($la);

        $viewer = $rendering->Viewer($width=640, $height=480);
        $trans = $rendering->Transform([320,240]);

        $geom1 = $rendering->Line([-1,-1], [ 0,-1]);
        $geom1->set_color(1.0, 0.0, 0.0);
        $geom1->set_linewidth(2.0);
        $geom1->add_attr($rendering->LineStyle(0x00ff));
        $geom1->add_attr($trans);
        $viewer->add_geom($geom1);
        $geom2 = $rendering->Line([ 1, 1], [ 0, 1]);
        $geom2->set_color(0.0, 0.0, 1.0);
        $geom2->set_linewidth(2.0);
        $geom2->add_attr($rendering->LineStyle(0x33ff));
        $geom2->add_attr($trans);
        $viewer->add_geom($geom2);

        $trans->set_scale(200.0,200.0);
        $trans->set_rotation(-45*M_PI/180);
        $viewer->render();
        $viewer->show();
        $this->assertTrue(true);
    }

    public function testImage()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        $rendering = $this->newRendering($la);

        $viewer = $rendering->Viewer($width=640, $height=480);
        $trans = $rendering->Transform([320,240]);

        $geom1 = $rendering->Image(__DIR__.'/testimg.png',$width=200.0, $height=100.0);
        $geom1->add_attr($trans);
        $viewer->add_geom($geom1);

        $trans->set_scale(0.5,0.5);
        $trans->set_rotation(45*M_PI/180);
        $viewer->render();
        $viewer->show();
        $this->assertTrue(true);
    }

    public function testMakeCircle()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        $rendering = $this->newRendering($la);

        $viewer = $rendering->Viewer($width=640, $height=480);
        
        $circle = $rendering->make_circle(radius:240);
        $circle->set_color(1.0, 0.0, 0.0);
        $circle->add_attr($rendering->Transform([320,240]));
        $viewer->add_geom($circle);
        $viewer->render();
        $viewer->show();
        $this->assertTrue(true);
    }

    public function testMakePolygon()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        $rendering = $this->newRendering($la);

        $viewer = $rendering->Viewer($width=640, $height=480);
        
        $polygon = $rendering->make_polygon([
            [-100,-100],
            [ 100,-100],
            [ 100, 100],
            [-100, 100],
        ]);
        $polygon->set_color(0.0, 1.0, 0.0);
        $trans = $rendering->Transform([320,240]);
        $trans->set_scale(1.5,1.0);
        $trans->set_rotation(45*M_PI/180);
        $polygon->add_attr($trans);
        $viewer->add_geom($polygon);
        $viewer->render();
        $viewer->show();
        $this->assertTrue(true);
    }

    public function testMakePolyline()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        $rendering = $this->newRendering($la);

        $viewer = $rendering->Viewer($width=640, $height=480);
        
        $polygon = $rendering->make_polyline([
            [-100,-100],
            [ 100,-100],
            [ 100, 100],
            [-100, 100],
        ]);
        $polygon->set_color(0.0, 1.0, 0.0);
        $trans = $rendering->Transform([320,240]);
        $trans->set_scale(1.5,1.0);
        $trans->set_rotation(45*M_PI/180);
        $polygon->add_attr($trans);
        $viewer->add_geom($polygon);
        $viewer->render();
        $viewer->show();
        $this->assertTrue(true);
    }

    public function testMakeCapsule()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        $rendering = $this->newRendering($la);

        $viewer = $rendering->Viewer($width=640, $height=480);
        
        $capsule = $rendering->make_capsule($length=200, $width=50);
        $capsule->set_color(1.0, 0.0, 0.0);
        $trans = $rendering->Transform([320,240]);
        $trans->set_rotation(30*M_PI/180);
        $capsule->add_attr($trans);
        $viewer->add_geom($capsule);
        $viewer->render();
        $viewer->show();
        $this->assertTrue(true);
    }
}