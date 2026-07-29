<?php
namespace Rindow\RL\Gym\Core\Spaces;

use Interop\Polite\Math\Matrix\NDArray;
use Rindow\RL\Gym\Core\PhpPcg32;

abstract class AbstractSpace
{
    protected object $la;
    /** @var array<int> */
    protected array $shape;
    protected int $dtype;
    protected PhpPcg32 $rnd;

    /**
     * @param array<int> $shape
     */
    public function __construct(
        object $la,
        array $shape,
        int $dtype,
    )
    {
        $this->la = $la;
        $this->shape = $shape;
        $this->dtype = $dtype;
        $this->rnd = new PhpPcg32($this->la->randInt(),$this->la->randInt());
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

    public function seed(int $seed) : void
    {
        $this->rnd->setSeed($seed);
    }
}
