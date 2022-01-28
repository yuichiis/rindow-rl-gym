<?php
namespace Rindow\RL\Gym\Core\Spaces;

abstract class AbstractSpace implements Space
{
    protected $la;
    protected $shape;
    protected $dtype;
    public function __construct($la, array $shape=null, $dtype=null, int $seed=null)
    {
        $this->la = $la;
        if($shape===null) {
            $shape = [];
        }
        $this->shape = $shape;
        $this->dtype = $dtype;
        if($seed!==null) {
            srand($seed);
        }
    }

    public function shape() : array
    {
        return $this->shape;
    }

    public function dtype()
    {
        return $this->dtype;
    }
}
