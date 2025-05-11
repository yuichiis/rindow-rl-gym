<?php
namespace RindowTest\RL\Gym\ClassicControl\MazeTest;

use PHPUnit\Framework\TestCase;
use Interop\Polite\Math\Matrix\NDArray;
use Interop\Polite\AI\RL\Environment;
use Rindow\Math\Matrix\MatrixOperator;
use Rindow\RL\Gym\ClassicControl\Maze\Maze;
use Rindow\RL\Gym\Core\Spaces\Box;
use Rindow\RL\Gym\Core\Spaces\Discrete;

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
        $mazeRules = $la->array([
        //   UP    DOWN  RIGHT LEFT
            [NAN,    1,    1,  NAN], // 0  +-+-+-+
            [NAN,    1,    1,    1], // 1  |0 1 2|
            [NAN,  NAN,  NAN,    1], // 2  + + +-+
            [  1,    1,  NAN,  NAN], // 3  |3|4 5|
            [  1,  NAN,    1,  NAN], // 4  + +-+ +
            [NAN,    1,  NAN,    1], // 5  |6 7|8|
            [  1,  NAN,    1,  NAN], // 6  +-+-+-+
            [NAN,  NAN,  NAN,    1], // 7
            [  1,  NAN,  NAN,  NAN], // 8
        ]);
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
        $this->assertInstanceof(Discrete::class,$obsSpace);
        $obsShape = $obsSpace->shape();
        $obsDtype = $obsSpace->dtype();
        $this->assertEquals([],$obsShape);
        $this->assertEquals(NDArray::int32,$obsDtype);
        $this->assertIsInt($obsSpace->n());
        $this->assertEquals(9,$obsSpace->n());

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
        $obs = $env->reset();
        $this->assertInstanceof(NDArray::class,$obs);
        $this->assertEquals(NDArray::int32,$obs->dtype());
        $this->assertEquals(0,$la->scalar($obs));

        // step
        $action = $la->array(Maze::RIGHT,dtype:NDArray::int32);
        $res = $env->step($action);
        $this->assertIsArray($res);
        $this->assertCount(4,$res);
        [$obs,$reward,$done,$info] = $res;
        $this->assertInstanceof(NDArray::class,$obs);
        $this->assertEquals(NDArray::int32,$obs->dtype());
        $this->assertEquals(1,$la->scalar($obs));
        $this->assertIsFloat($reward);
        $this->assertEquals(-1.0,$reward);
        $this->assertIsBool($done);

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
