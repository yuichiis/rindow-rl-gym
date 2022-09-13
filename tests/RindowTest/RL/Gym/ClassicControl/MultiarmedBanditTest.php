<?php
namespace RindowTest\RL\Gym\ClassicControl\MultiarmedBanditTest;

use PHPUnit\Framework\TestCase;
use Interop\Polite\Math\Matrix\NDArray;
use Interop\Polite\AI\RL\Environment;
use Rindow\Math\Matrix\MatrixOperator;
use Rindow\RL\Gym\ClassicControl\MultiarmedBandit\Slots;
use Rindow\RL\Gym\Core\Spaces\Box;
use Rindow\RL\Gym\Core\Spaces\Discrete;

use LogicException;

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

    public function newProbs()
    {
        return [0.25,0.25,0.0,0.5];
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
        $probs = $this->newProbs();
        $env = new Slots($la,$probs,metadata:$this->getMetadata());

        // maxEpisodeSteps, rewardThreshold
        $this->assertEquals(0,$env->maxEpisodeSteps());
        $this->assertEquals(0,$env->rewardThreshold());

        // observationSpace
        $obsSpace = $env->observationSpace();
        $this->assertNull($obsSpace);

        // actionSpace
        $actionSpace = $env->actionSpace();
        $this->assertInstanceof(Discrete::class,$actionSpace);
        $actionShape = $actionSpace->shape();
        $actionDtype = $actionSpace->dtype();
        $this->assertEquals([],$actionShape);
        $this->assertEquals(null,$actionDtype);
        $this->assertIsInt($actionSpace->n());
        $actionN = count($probs);
        $this->assertEquals($actionN,$actionSpace->n());

        // reset
        $obs = $env->reset();
        $this->assertEquals(0,$obs);

        // step
        $res = $env->step(0);
        $this->assertIsArray($res);
        $this->assertCount(4,$res);
        [$obs,$reward,$done,$info] = $res;
        $this->assertEquals(0,$obs);
        $this->assertIsFloat($reward);
        $this->assertIsBool($done);

        // seed
        $this->assertEquals([12345],$env->seed(12345));
    }

    public function testRender()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);
        $probs = $this->newProbs();
        $env = new Slots($la,$probs,metadata:$this->getMetadata());

        $env->reset();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented.');
        $env->render();
    }
}
