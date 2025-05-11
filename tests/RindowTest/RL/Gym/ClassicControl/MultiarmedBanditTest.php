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

class MultiarmedBanditTest extends TestCase
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
            'render.skipRunViewer' => getenv('PLOT_RENDERER_SKIP') ? true : false,
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
        $this->assertEquals(NDArray::int32,$actionDtype);
        $this->assertIsInt($actionSpace->n());
        $actionN = count($probs);
        $this->assertEquals($actionN,$actionSpace->n());

        // reset
        $obs = $env->reset();
        $this->assertEquals(0,$la->scalar($obs));

        // step
        $action = $la->array(0,dtype:NDArray::int32);
        $res = $env->step($action);
        $this->assertIsArray($res);
        $this->assertCount(4,$res);
        [$obs,$reward,$done,$info] = $res;
        $this->assertEquals(0,$la->scalar($obs));
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
