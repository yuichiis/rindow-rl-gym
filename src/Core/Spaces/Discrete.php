<?php
namespace Rindow\RL\Gym\Core\Spaces;

use InvalidArgumentException;
use RuntimeException;
use Interop\Polite\Math\Matrix\NDArray;

class Discrete extends AbstractSpace
{
    protected int $n;
    public function __construct(object $la, int $n, ?int $seed=null)
    {
        parent::__construct($la,seed:$seed);
        $this->n = $n;
    }

    public function n() : int
    {
        return $this->n;
    }

    public function sample() : NDArray
    {
        $la = $this->la;
        $random = $la->array(rand(0,$this->n-1),dtype:NDArray::int32);
        return $random;
    }

    public function contains(NDArray $x, ?bool $throw=null, ?string $type=null) : bool
    {
        $la = $this->la;
        if($type===null) {
            $type = 'value';
        }
        if(!$la->isInt($x)) {
            $dtype = $this->dtypeToString($x->dtype());
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
