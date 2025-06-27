<?php
namespace Rindow\RL\Gym\Core\Spaces;

use Interop\Polite\Math\Matrix\NDArray;

abstract class AbstractSpace
{
    protected object $la;
    /** @var array<int> */
    protected array $shape;
    protected int $dtype;

    /**
     * @param array<int> $shape
     */
    public function __construct(
        object $la,
        array $shape,
        int $dtype,
        ?int $seed=null
    )
    {
        $this->la = $la;
        $this->shape = $shape;
        $this->dtype = $dtype;
        if($seed!==null) {
            srand($seed);
        }
    }

    /**
     * @return array<int> $shape
     */
    public function shape() : array
    {
        return $this->shape;
    }

    public function dtype() : int
    {
        return $this->dtype;
    }

}
