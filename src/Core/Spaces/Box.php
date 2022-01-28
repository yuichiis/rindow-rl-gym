<?php
namespace Rindow\RL\Gym\Core\Spaces;

use InvalidArgumentException;
use RuntimeException;
use Interop\Polite\Math\Matrix\NDArray;

class Box extends AbstractSpace
{
    protected $low;
    protected $high;

    public function __construct($la, $low, $high, array $shape=null, $dtype=null, int $seed=null)
    {
        if(is_scalar($low)&&is_scalar($high)&&$shape!==null) {
            $low = $la->fill($low,$la->alloc($shape,$dtype));
            $high = $la->fill($high,$la->alloc($shape,$dtype));
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
        parent::__construct($la,$shape,$dtype,$seed);
        $this->low = $low;
        $this->high = $high;
    }

    public function high()
    {
        return $this->high;
    }

    public function low()
    {
        return $this->low;
    }

    public function sample()
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

    public function contains($x,bool $throw=null,string $type=null)
    {
        $la = $this->la;
        if(!($x instanceof NDArray)) {
            throw new InvalidArgumentException('x must be NDArray');
        }
        if($type===null) {
            $type = 'value';
        }
        $error = $la->less($la->axpy($this->low,$la->copy($x),-1),0);
        if($la->sum($error)) {
            if($throw) {
                $key = $la->imax($error);
                throw new RuntimeException($type.'('.$key.') is too low.:'.$x[$key]);
            }
            return false;
        }
        $error = $la->greater($la->axpy($this->high,$la->copy($x),-1),0);
        if($la->sum($error)) {
            if($throw) {
                $key = $la->imax($error);
                throw new RuntimeException($type.'('.$key.') is too high.:'.$x[$key]);
            }
            return false;
        }
        return true;
    }
}
