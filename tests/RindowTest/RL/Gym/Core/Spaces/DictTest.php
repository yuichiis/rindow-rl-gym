<?php
namespace RindowTest\RL\Gym\Core\Spaces\DictTest;

use PHPUnit\Framework\TestCase;
use Interop\Polite\Math\Matrix\NDArray;
use Rindow\Math\Matrix\MatrixOperator;
use Rindow\RL\Gym\Core\Spaces\Dict;
use Rindow\RL\Gym\Core\Spaces\Discrete;
use Rindow\RL\Gym\Core\Spaces\Box;
use RuntimeException;
use InvalidArgumentException;

class DictTest extends TestCase
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

        $space = new Dict($la,[
            'int8' => new Discrete($la,8),
            'box0145'=> new Box($la,low:$la->array([0,1]),high:$la->array([4,5])),
            'dict' => new Dict($la,[
                'int4' => new Discrete($la,4),
                'box6789' => new Box($la,low:$la->array([6,7]),high:$la->array([8,9])),
            ]),
        ]);
        $this->assertCount(3,$space);
        $this->assertFalse(isset($space['none']));
        $this->assertTrue(isset($space['int8']));
        $this->assertInstanceof(Discrete::class,$space['int8']);
        $this->assertInstanceof(Box::class,$space['box0145']);
        $this->assertInstanceof(Dict::class,$space['dict']);
        $space['int10'] = new Discrete($la,10);
        $this->assertInstanceof(Discrete::class,$space['int10']);

        $keys = [];
        $types = [];
        foreach($space as $key => $sp) {
            $keys[] = $key;
            $types[] = get_class($sp);
        }
        $this->assertEquals(['int8','box0145','dict','int10'],$keys);
        $this->assertEquals([
            Discrete::class,
            Box::class,
            Dict::class,
            Discrete::class,
        ],$types);
        $this->assertTrue($space->contains([
            'int8' => $la->array(7,dtype:NDArray::int32),
            'box0145' => $la->array([0,5]),
            'dict' => [
                'int4' => $la->array(3,dtype:NDArray::int32),
                'box6789' => $la->array([6,9]),
            ],
            'int10' => $la->array(9,dtype:NDArray::int32),
        ]));
    }

    public function testSample()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);

        $space = new Dict($la,[
            'int8' => new Discrete($la,8),
            'box0145'=> new Box($la,low:$la->array([0,1]),high:$la->array([4,5])),
            'dict' => new Dict($la,[
                'int4' => new Discrete($la,4),
                'box6789' => new Box($la,low:$la->array([6,7]),high:$la->array([8,9])),
            ]),
        ]);
        for($i=0;$i<100;$i++) {
            $sample = $space->sample();
            $this->assertTrue($space->contains($sample));
        }
    }

    public function testThrowLowerValue()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);

        $space = new Dict($la,[
            'int8' => new Discrete($la,8),
            'box0145'=> new Box($la,low:$la->array([0,1]),high:$la->array([4,5])),
            'dict' => new Dict($la,[
                'int4' => new Discrete($la,4),
                'box6789' => new Box($la,low:$la->array([6,7]),high:$la->array([8,9])),
            ]),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The action.dict.box6789(0) is too low.:-100');

        $space->contains([
            'int8' => $la->array(7,dtype:NDArray::int32),
            'box0145' => $la->array([0,5]),
            'dict' => [
                'int4' => $la->array(3,dtype:NDArray::int32),
                'box6789' => $la->array([-100,9]),
            ],
        ], throw:true, type:'action');
    }

    public function testInvalidKey()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);

        $space = new Dict($la,[
            'int8' => new Discrete($la,8),
            'box0145'=> new Box($la,low:$la->array([0,1]),high:$la->array([4,5])),
            'dict' => new Dict($la,[
                'int4' => new Discrete($la,4),
                'box6789' => new Box($la,low:$la->array([6,7]),high:$la->array([8,9])),
            ]),
        ]);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Keys of the action.dict must be (int4,box6789). (int4,XXXXXX) given.');

        $space->contains([
            'int8' => $la->array(7,dtype:NDArray::int32),
            'box0145' => $la->array([0,5]),
            'dict' => [
                'int4' => $la->array(3,dtype:NDArray::int32),
                'XXXXXX' => $la->array([-100,9]),
            ],
        ], throw:true, type:'action');
    }

    public function testInvalidSpace()
    {
        $mo = $this->newMatrixOperator();
        $la = $this->newLa($mo);

        $space = new Dict($la,[
            'int8' => new Discrete($la,8),
            'box0145'=> new Box($la,low:$la->array([0,1]),high:$la->array([4,5])),
            'dict' => new Dict($la,[
                'int4' => new Discrete($la,4),
                'box6789' => new Box($la,low:$la->array([6,7]),high:$la->array([8,9])),
            ]),
        ]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('type of action.dict must be array. object given.');

        $space->contains([
            'int8' => $la->array(7,dtype:NDArray::int32),
            'box0145' => $la->array([0,5]),
            'dict' => $la->array(0),
        ], throw:true, type:'action');
   }
}