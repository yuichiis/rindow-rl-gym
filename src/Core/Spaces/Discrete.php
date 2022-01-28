<?php
namespace Rindow\RL\Gym\Core\Spaces;

use InvalidArgumentException;
use RuntimeException;

class Discrete extends AbstractSpace
{
    protected $n;
    public function __construct($la, int $n, int $seed=null)
    {
        parent::__construct($la,null,null,$seed);
        $this->n = $n;
    }

    public function n()
    {
        return $this->n;
    }

    public function sample()
    {
        return rand(0,$this->n-1);
    }

    public function contains($x,bool $throw=null,string $type=null)
    {
        if(!is_int($x)) {
            throw new InvalidArgumentException('x must be integer');
        }
        if($type===null) {
            $type = 'value';
        }
        if($x<0||$x>=$this->n) {
            if($throw) {
                throw new RuntimeException('The '.$type.' is out of range:'.$x);
            }
            return false;
        }
        return true;
    }
}
