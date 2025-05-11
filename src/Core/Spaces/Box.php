<?php
namespace Rindow\RL\Gym\Core\Spaces;

use InvalidArgumentException;
use RuntimeException;
use Interop\Polite\Math\Matrix\NDArray;

class Box extends AbstractSpace
{
    protected NDArray $low;
    protected NDArray $high;

    /**
     * @param array<int> $shape
     */
    public function __construct(
        object $la, 
        NDArray|float|int $low,
        NDArray|float|int $high,
        ?array $shape=null,
        ?int $dtype=null,
        ?int $seed=null
        )
    {
        if(is_scalar($low)&&is_scalar($high)) {
            $dtype ??= NDArray::float32;
            $shape ??= [];
            $low = $la->fill($low,$la->alloc($shape,dtype:$dtype));
            $high = $la->fill($high,$la->alloc($shape,dtype:$dtype));
        } elseif($low instanceof NDArray&&$high instanceof NDArray) {
            if($low->shape()!=$high->shape()||$low->dtype()!=$high->dtype()) {
                throw new InvalidArgumentException('Unmatch shape or dtype of min and max');
            }
            if($shape!==null&&$low->shape()!=$shape) {
                throw new InvalidArgumentException('Unmatch specifying shape and min or max');
            }
            if($dtype!==null&&$low->dtype()!=$dtype) {
                throw new InvalidArgumentException('Unmatch specifying dtype and min or max');
            }
            $shape = $low->shape();
            $dtype = $low->dtype();
        } else {
            throw new InvalidArgumentException('The specification of min and max is not unified');
        }
        parent::__construct($la,shape:$shape,dtype:$dtype,seed:$seed);
        $this->low = $low;
        $this->high = $high;
    }

    public function high() : NDArray
    {
        return $this->high;
    }

    public function low() : NDArray
    {
        return $this->low;
    }

    public function sample() : NDArray
    {
        $la = $this->la;
        $low = $this->low;
        $high = $this->high;
        if($la->isInt($this->low)) {
            $low = $la->astype($low,NDArray::float32);
            $high = $la->astype($high,NDArray::float32);
        }
        $value = $la->randomUniform($low->shape(),0.0,1.0,NDArray::float32);
        $scale = $la->axpy($low,$la->copy($high),-1);
        $value = $la->multiply($scale,$value);
        $value = $la->axpy($low,$value);
        $value = $la->nan2num($value);
        if($la->isInt($this->low)) {
            $value = $la->astype($value,$this->low->dtype());
        }
        return $value;
    }

    public function contains(NDArray $x, ?bool $throw=null, ?string $type=null) : bool
    {
        $la = $this->la;
        if($type===null) {
            $type = 'value';
        }
        if($x->dtype()!==$this->dtype()) {
            $xdtype = $la->dtypeToString($x->dtype());
            $dtype = $la->dtypeToString($this->dtype());
            throw new InvalidArgumentException("dtype of $type must be $dtype. $xdtype given.");
        }
        if($x->shape()!=$this->shape()) {
            $xshape = implode(',',$x->shape());
            $shape = implode(',',$this->shape());
            throw new InvalidArgumentException("shape of $type must be ($shape). ($xshape) given.");
        }
        $error = $la->less($la->copy($x),$this->low);
        if($la->scalar($la->sum($error))) {
            if($throw) {
                $key = $la->iamax($error);
                if($key instanceof NDArray) {
                    $value = $la->gatherb($x,$key);
                    $key = $la->scalar($key);
                    $value = $la->scalar($value);
                } else {
                    $value = $x[$key];
                }
                throw new RuntimeException("The $type($key) is too low.:$value");
            }
            return false;
        }
        $error = $la->greater($la->copy($x),$this->high);
        if($la->scalar($la->sum($error))) {
            if($throw) {
                $key = $la->iamax($error);
                if($key instanceof NDArray) {
                    $value = $la->gatherb($x,$key);
                    $key = $la->scalar($key);
                    $value = $la->scalar($value);
                } else {
                    $value = $x[$key];
                }
                throw new RuntimeException("The $type($key) is too high.:$value");
            }
            return false;
        }
        return true;
    }
}
