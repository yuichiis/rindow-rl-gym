<?php
namespace RindowTest\RL\Gym\Core\Spaces\DiscreteTest;

use PHPUnit\Framework\TestCase;
use Interop\Polite\Math\Matrix\NDArray;
use Rindow\Math\Matrix\MatrixOperator;
use Rindow\RL\Gym\Core\Spaces\Discrete;;
use RuntimeException;
use InvalidArgumentException;

class DiscreteTest extends TestCase
{
    public function newMatrixOperator()
    {
        return new MatrixOperator();
    }

    public function newLa($mo)
    {
        return $mo->la();
    }

    public function testNormal()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);

        $space = new Discrete($la,4);
        $lowvalue = $la->array(0,dtype:NDArray::int32);
        $highvalue = $la->array(3,dtype:NDArray::int32);
        $lower = $la->array(-1,dtype:NDArray::int32);
        $higher = $la->array(4,dtype:NDArray::int32);
        $lowarray = $la->array([0],dtype:NDArray::int32);
        $higharray = $la->array([3],dtype:NDArray::int32);
        $lowerarray = $la->array([-1],dtype:NDArray::int32);
        $higherarray = $la->array([4],dtype:NDArray::int32);

        $this->assertEquals(NDArray::int32, $space->dtype());
        $this->assertEquals([],$space->shape());
        $this->assertEquals(4,$space->n());

        $this->assertTrue($space->contains($lowvalue));
        $this->assertTrue($space->contains($highvalue));
        $this->assertFalse($space->contains($lower));
        $this->assertFalse($space->contains($higher));
        $this->assertTrue($space->contains($lowarray));
        $this->assertTrue($space->contains($higharray));
        $this->assertFalse($space->contains($lowerarray));
        $this->assertFalse($space->contains($higherarray));
    }

    public function testSample()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);

        $space = new Discrete($la,4);
        for($i=0;$i<100;$i++) {
            $sample = $space->sample();
            $this->assertTrue($space->contains($sample));
        }
    }

    public function testThrowLowerValue()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);

        $space = new Discrete($la,4);
        $lower = $la->array(-1,dtype:NDArray::int32);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The action is out of range:-1');
        $space->contains($lower, throw:true, type:'action');
    }

    public function testThrowHighValue()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);

        $space = new Discrete($la,4);
        $higher = $la->array(4,dtype:NDArray::int32);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The action is out of range:4');
        $space->contains($higher, throw:true, type:'action');
    }

    public function testThrowLowerArray()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);

        $space = new Discrete($la,4);
        $lower = $la->array([-1],dtype:NDArray::int32);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The action is out of range:-1');
        $space->contains($lower, throw:true, type:'action');
    }

    public function testThrowHighArray()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);

        $space = new Discrete($la,4);
        $higher = $la->array([4],dtype:NDArray::int32);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The action is out of range:4');
        $space->contains($higher, throw:true, type:'action');
    }

    public function testInvalidDtype()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);

        $space = new Discrete($la,4);
        $value = $la->array(0,dtype:NDArray::float32);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('value must be integer. float32 given.');
        $space->contains($value);
    }

    public function testInvalidShape()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);

        $space = new Discrete($la,4);
        $value = $la->array([0,0],dtype:NDArray::int32);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('value must be scalar NDArray. shape (2) given');
        $space->contains($value);
    }
}