<?php
namespace RindowTest\RL\Gym\Core\Spaces\BoxTest;

use PHPUnit\Framework\TestCase;
use Interop\Polite\Math\Matrix\NDArray;
use Rindow\Math\Matrix\MatrixOperator;
use Rindow\RL\Gym\Core\Spaces\Box;;
use RuntimeException;
use InvalidArgumentException;

class BoxTest extends TestCase
{
    public function newMatrixOperator()
    {
        return new MatrixOperator();
    }

    public function newLa($mo)
    {
        return $mo->la();
    }

    public function testNormalValue()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);

        $space = new Box($la,0,4);
        $lowvalue = $la->array(0,dtype:NDArray::float32);
        $highvalue = $la->array(4,dtype:NDArray::float32);
        $lower = $la->array(-0.1,dtype:NDArray::float32);
        $higher = $la->array(4.1,dtype:NDArray::float32);

        $this->assertEquals(NDArray::float32, $space->dtype());
        $this->assertEquals([],$space->shape());
        $this->assertEquals(0,$space->low()->toArray());
        $this->assertEquals(4,$space->high()->toArray());

        $this->assertTrue($space->contains($lowvalue));
        $this->assertTrue($space->contains($highvalue));
        $this->assertFalse($space->contains($lower));
        $this->assertFalse($space->contains($higher));
    }

    public function testNormalArray()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);

        $space = new Box($la,0,4,[2]);
        $lowvalue = $la->array([0, 0],dtype:NDArray::float32);
        $highvalue = $la->array([4, 4],dtype:NDArray::float32);
        $lower = $la->array([-0.1, 0.0],dtype:NDArray::float32);
        $higher = $la->array([0.0, 4.1],dtype:NDArray::float32);

        $this->assertEquals(NDArray::float32, $space->dtype());
        $this->assertEquals([2],$space->shape());
        $this->assertEquals([0,0],$space->low()->toArray());
        $this->assertEquals([4,4],$space->high()->toArray());

        $this->assertTrue($space->contains($lowvalue));
        $this->assertTrue($space->contains($highvalue));
        $this->assertFalse($space->contains($lower));
        $this->assertFalse($space->contains($higher));
    }

    public function testNormalNDArray()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);

        $space = new Box($la,$la->array([0,1]),$la->array([4,5]));
        $lowvalue = $la->array([0, 1],dtype:NDArray::float32);
        $highvalue = $la->array([4, 5],dtype:NDArray::float32);
        $lower = $la->array([-0.1, 0.0],dtype:NDArray::float32);
        $higher = $la->array([0.0, 5.1],dtype:NDArray::float32);

        $this->assertEquals(NDArray::float32, $space->dtype());
        $this->assertEquals([2],$space->shape());
        $this->assertEquals([0,1],$space->low()->toArray());
        $this->assertEquals([4,5],$space->high()->toArray());

        $this->assertTrue($space->contains($lowvalue));
        $this->assertTrue($space->contains($highvalue));
        $this->assertFalse($space->contains($lower));
        $this->assertFalse($space->contains($higher));
    }

    public function testSample()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);

        $space = new Box($la,$la->array([0,1]),$la->array([4,5]));
        for($i=0;$i<100;$i++) {
            $sample = $space->sample();
            $this->assertTrue($space->contains($sample));
        }
    }

    public function testThrowLowerValue()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);

        $space = new Box($la,$la->array([0,1]),$la->array([4,5]));
        $lower = $la->array([-0.1, 0.0],dtype:NDArray::float32);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('observation(0) is too low.:-0.1');
        $space->contains($lower, throw:true, type:'observation');
    }

    public function testThrowHighValue()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);

        $space = new Box($la,$la->array([0,1]),$la->array([4,5]));
        $higher = $la->array([0.0, 5.1],dtype:NDArray::float32);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('observation(1) is too high.:5.1');
        $space->contains($higher, throw:true, type:'observation');
    }

    public function testInvalidDtype()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);

        $space = new Box($la,$la->array([0,1]),$la->array([4,5]));
        $value = $la->array([0, 1],dtype:NDArray::int32);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('dtype of value must be float32. int32 given.');
        $space->contains($value);
    }

    public function testInvalidShape()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);

        $space = new Box($la,$la->array([0,1]),$la->array([4,5]));
        $value = $la->array([0],dtype:NDArray::float32);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('shape of value must be (2). (1) given.');
        $space->contains($value);
    }
}