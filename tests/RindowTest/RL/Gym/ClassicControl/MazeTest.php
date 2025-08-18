<?php
namespace RindowTest\RL\Gym\ClassicControl\MazeTest;

use PHPUnit\Framework\TestCase;
use Interop\Polite\Math\Matrix\NDArray;
use Interop\Polite\AI\RL\Environment;
use Rindow\Math\Matrix\MatrixOperator;
use Rindow\RL\Gym\ClassicControl\Maze\Maze;
use Rindow\RL\Gym\Core\Spaces\Box;
use Rindow\RL\Gym\Core\Spaces\Discrete;
use Rindow\RL\Gym\Core\Spaces\Dict;

class MazeTest extends TestCase
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
            'render.skipRunViewer' => getenv('PLOT_RENDERER_SKIP') ? true : false,
        ];
    }

    public function newRules($la)
    {
        // +-+-+-+
        // |0 1 2|
        // + + +-+
        // |3|4 5|
        // + +-+ +
        // |6 7|8|
        // +-+-+-+
        $mazeRules = $la->array([
        //   UP    DOWN  RIGHT LEFT
            [false,  true,  true, false], // 0  
            [false,  true,  true,  true], // 1  
            [false, false, false,  true], // 2  
            [ true,  true, false, false], // 3  
            [ true, false,  true, false], // 4  
            [false,  true, false,  true], // 5  
            [ true, false,  true, false], // 6  
            [false, false, false,  true], // 7
            [ true, false, false, false], // 8
        ],dtype:NDArray::bool);
        [$width,$height,$exit] = [3,3,8];
        return [$mazeRules,$width,$height,$exit];
    }

    public function testBasic()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        [$mazeRules,$width,$height,$exit] = $this->newRules($la);
        $env = new Maze($la,$mazeRules,$width,$height,$exit,$throw=true,$maxEpisodeSteps=100,metadata:$this->getMetadata());

        // maxEpisodeSteps, rewardThreshold
        $this->assertEquals(100,$env->maxEpisodeSteps());
        $this->assertEquals(0,$env->rewardThreshold());

        // observationSpace
        $obsSpace = $env->observationSpace();
        $this->assertInstanceof(Dict::class,$obsSpace);
        $this->assertInstanceof(Box::class,$obsSpace['location']);
        $obsShape = $obsSpace['location']->shape();
        $obsDtype = $obsSpace['location']->dtype();
        $this->assertEquals([2],$obsShape);
        $this->assertEquals(NDArray::int32,$obsDtype);
        //$this->assertIsInt($obsSpace->n());
        //$this->assertEquals(9,$obsSpace->n());
        $this->assertEquals([0,0],$obsSpace['location']->low()->toArray());
        $this->assertEquals([$height-1,$width-1],$obsSpace['location']->high()->toArray());

        // actionSpace
        $actionSpace = $env->actionSpace();
        $this->assertInstanceof(Discrete::class,$actionSpace);
        $actionShape = $actionSpace->shape();
        $actionDtype = $actionSpace->dtype();
        $this->assertEquals([],$actionShape);
        $this->assertEquals(NDArray::int32,$actionDtype);
        $this->assertIsInt($actionSpace->n());
        [$dmy,$actionN] = $mazeRules->shape();
        $this->assertEquals($actionN,$actionSpace->n());

        // reset
        [$obs,$info] = $env->reset();
        $this->assertIsArray($obs);
        $this->assertInstanceof(NDArray::class,$obs['location']);
        $this->assertEquals(NDArray::int32,$obs['location']->dtype());
        $this->assertEquals([0,0],$obs['location']->toArray());
        $this->assertInstanceof(NDArray::class,$obs['actionMask']);
        $this->assertEquals(NDArray::bool,$obs['actionMask']->dtype());
        $this->assertEquals([false,  true,  true, false],$obs['actionMask']->toArray());

        // step
        $action = $la->array(Maze::RIGHT,dtype:NDArray::int32);
        $res = $env->step($action);
        $this->assertIsArray($res);
        $this->assertCount(5,$res);
        [$obs,$reward,$done,$trunc,$info] = $res;
        $this->assertInstanceof(NDArray::class,$obs['location']);
        $this->assertEquals(NDArray::int32,$obs['location']->dtype());
        $this->assertEquals([0,1],$obs['location']->toArray());
        $this->assertIsFloat($reward);
        $this->assertEquals(-1.0,$reward);
        $this->assertIsBool($done);
        $this->assertIsBool($trunc);
        $this->assertEquals([false,  true,  true,  true],$obs['actionMask']->toArray());

        // seed
        $this->assertEquals([12345],$env->seed(12345));
    }

    public function testRender()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        [$mazeRules,$width,$height,$exit] = $this->newRules($la);
        $env = new Maze($la,$mazeRules,$width,$height,$exit,$throw=true,$maxEpisodeSteps=100,metadata:$this->getMetadata());

        $env->reset();
        $env->render();
        $env->show();

        $env->reset();
        $env->render();
        $actions = [Maze::RIGHT,Maze::DOWN,Maze::RIGHT,Maze::DOWN];
        foreach($actions as $action) {
            $action = $la->array($action,dtype:NDArray::int32);
            $env->step($action);
            $env->render();
        }
        $env->show(delay:100);
        $this->assertTrue(true);
    }
}
