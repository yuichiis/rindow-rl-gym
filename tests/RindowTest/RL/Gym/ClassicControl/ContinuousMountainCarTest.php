<?php
namespace RindowTest\RL\Gym\ClassicControl\ContinuousMountainCarTest;

use PHPUnit\Framework\TestCase;
use Interop\Polite\Math\Matrix\NDArray;
use Interop\Polite\AI\RL\Environment;
use Rindow\Math\Matrix\MatrixOperator;
use Rindow\RL\Gym\ClassicControl\ContinuousMountainCar\ContinuousMountainCarV0;
use Rindow\RL\Gym\Core\Spaces\Box;
use Rindow\RL\Gym\Core\Spaces\Discrete;

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

    public function testBasic()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        $env = new ContinuousMountainCarV0($la,metadata:$this->getMetadata());

        // maxEpisodeSteps, rewardThreshold
        $this->assertEquals(999,$env->maxEpisodeSteps());
        $this->assertEquals(90.0,$env->rewardThreshold());

        // observationSpace
        $obsSpace = $env->observationSpace();
        $this->assertInstanceof(Box::class,$obsSpace);
        $obsShape = $obsSpace->shape();
        $obsDtype = $obsSpace->dtype();
        $this->assertEquals([2],$obsShape);
        $this->assertEquals(NDArray::float32,$obsDtype);
        $this->assertEquals($obsShape,$obsSpace->high()->shape());
        $this->assertEquals($obsDtype,$obsSpace->high()->dtype());
        $this->assertEquals($obsShape,$obsSpace->low()->shape());
        $this->assertEquals($obsDtype,$obsSpace->low()->dtype());

        // actionSpace
        $actionSpace = $env->actionSpace();
        $this->assertInstanceof(Box::class,$actionSpace);
        $actionShape = $actionSpace->shape();
        $actionDtype = $actionSpace->dtype();
        $this->assertEquals([1],$actionShape);
        $this->assertEquals(NDArray::float32,$actionDtype);
        $this->assertEquals($actionShape,$actionSpace->high()->shape());
        $this->assertEquals($actionDtype,$actionSpace->high()->dtype());
        $this->assertEquals($actionShape,$actionSpace->low()->shape());
        $this->assertEquals($actionDtype,$actionSpace->low()->dtype());

        // reset
        $obs = $env->reset();
        $this->assertInstanceof(NDArray::class,$obs);
        $this->assertEquals($obsShape,$obs->shape());

        // step
        $res = $env->step($la->array([1]));
        $this->assertIsArray($res);
        $this->assertCount(4,$res);
        [$obs,$reward,$done,$info] = $res;
        $this->assertInstanceof(NDArray::class,$obs);
        $this->assertEquals($obsShape,$obs->shape());
        $this->assertEquals($obsDtype,$obs->dtype());
        $this->assertIsFloat($reward);
        $this->assertIsBool($done);

        // seed
        $this->assertEquals([12345],$env->seed(12345));
    }

    public function testRender()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        $env = new ContinuousMountainCarV0($la,metadata:$this->getMetadata());

        $env->reset();
        $env->render();
        $env->show();

        $env->reset();
        $env->render();
        for($i=0;$i<10;$i++) {
            $env->step($la->array([1]));
            $env->render();
        }
        $env->show();
        $this->assertTrue(true);
    }
}
