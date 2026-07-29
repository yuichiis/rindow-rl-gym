<?php
namespace Rindow\RL\Gym\Core\Spaces;

use InvalidArgumentException;
use RuntimeException;
use Interop\Polite\Math\Matrix\NDArray;
use Interop\Polite\AI\RL\Spaces\Discrete as DiscreteInterface;

class Discrete extends AbstractSpace implements DiscreteInterface
{
    protected int $n;
    public function __construct(object $la, int $n)
    {
        parent::__construct($la,
            shape:[],
            dtype:NDArray::int32
        );
        $this->n = $n;
    }

    public function n() : int
    {
        return $this->n;
    }

    public function sample() : NDArray|array
    {
        $la = $this->la;
        $random = $la->array($this->rnd->randInt(0,$this->n-1),dtype:NDArray::int32);
        return $random;
    }

    public function contains(NDArray|array $x, ?bool $throw=null, ?string $type=null) : bool
    {
        $la = $this->la;
        if(!($x instanceof NDArray)) {
            $valuetype = gettype($x);
            throw new InvalidArgumentException("type of $type must be NDArray. $valuetype given.");
        }
        if($type===null) {
            $type = 'value';
        }
        if(!$la->isInt($x)) {
            $dtype = $la->dtypeToString($x->dtype());
            throw new InvalidArgumentException("$type must be integer. $dtype given.");
        }
        if($x->size()!=1) {
            $shape = implode(',',$x->shape());
            throw new InvalidArgumentException("$type must be scalar NDArray. shape ($shape) given.");
        }
        $value = $la->scalar($x->reshape([]));
        if($value<0||$value>=$this->n) {
            if($throw) {
                throw new RuntimeException("The $type is out of range:$value");
            }
            return false;
        }
        return true;
    }
}
