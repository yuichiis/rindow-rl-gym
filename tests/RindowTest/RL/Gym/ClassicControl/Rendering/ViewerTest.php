<?php
namespace RindowTest\RL\Gym\ClassicControl\Rendering\ViewerTest;

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
            'render.skipRunViewer' => getenv('TRAVIS_PHP_VERSION') ? true : false,
        ];
    }

    public function newRendering($la)
    {
        $factory = new RenderFactory($la,'gd',metadata:$this->getMetadata());
        return $factory->factory();
    }

    public function testSetBounds()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        $rendering = $this->newRendering($la);

        $viewer = $rendering->Viewer($width=480, $height=480);
        $viewer->set_bounds($left=-2.0, $right=2.0, $bottom=-2.0, $top=2.0);

        $trans = $rendering->Transform([0,0]);

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

        $trans->set_rotation(-45*M_PI/180);
        $viewer->render();
        $viewer->show();
        $this->assertTrue(true);
    }

    public function testDrawGeoms()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        $rendering = $this->newRendering($la);

        $viewer = $rendering->Viewer($width=480, $height=480);
        $viewer->set_bounds($left=-2.0, $right=2.0, $bottom=-2.0, $top=2.0);

        $viewer->draw_circle(radius:0.9, 
                filled:false, color:[1.0,0.0,0.0], linewidth:3)
            ->add_attr($rendering->Transform([-1,-1]));

        $viewer->draw_polygon([[-0.9,-0.9],[0.9,-0.9],[0.9,0.9],[-0.9,0.9]],
                color:[0.0,0.0,1.0])
            ->add_attr($rendering->Transform([ 1,-1]));
        
        $viewer->draw_polyline([[-0.9,-0.9],[0.9,-0.9],[0.9,0.9],[-0.9,0.9]],
                color:[0.0,1.0,0.0], linewidth:3)
            ->add_attr($rendering->Transform([ 1, 1]));

        $viewer->draw_line($start=[-0.9,-0.9],$end=[0.9, 0.9],
                color:[1.0,0.0,1.0], linewidth:3)
            ->add_attr($rendering->Transform([-1, 1]));

        $viewer->render();
        $viewer->show();
        $this->assertTrue(true);
    }

    public function getPlotConfig()
    {
        return [
            'renderer.skipCleaning' => true,
            'renderer.skipRunViewer' => getenv('TRAVIS_PHP_VERSION') ? true : false,
        ];
    }

    public function testRenderRgbArray()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        $rendering = $this->newRendering($la);

        $viewer = $rendering->Viewer($width=480, $height=480);
        $viewer->set_bounds($left=-2.0, $right=2.0, $bottom=-2.0, $top=2.0);

        $viewer->draw_circle(radius:0.9, 
                filled:false, color:[1.0,0.0,0.0], linewidth:3)
            ->add_attr($rendering->Transform([-1,-1]));

        $viewer->draw_polygon([[-0.9,-0.9],[0.9,-0.9],[0.9,0.9],[-0.9,0.9]],
                color:[0.0,0.0,1.0])
            ->add_attr($rendering->Transform([ 1,-1]));
        
        $viewer->draw_polyline([[-0.9,-0.9],[0.9,-0.9],[0.9,0.9],[-0.9,0.9]],
                color:[0.0,1.0,0.0], linewidth:3)
            ->add_attr($rendering->Transform([ 1, 1]));

        $viewer->draw_line($start=[-0.9,-0.9],$end=[0.9, 0.9],
                color:[1.0,0.0,1.0], linewidth:3)
            ->add_attr($rendering->Transform([-1, 1]));

        $array = $viewer->render(mode:'rgb_array');
        $plt = new Plot($this->getPlotConfig(),$mo);
        $plt->imshow($array);
        $plt->show();
        $this->assertTrue(true);
    }
}