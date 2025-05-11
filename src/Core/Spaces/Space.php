<?php
namespace Rindow\RL\Gym\Core\Spaces;

use Interop\Polite\Math\Matrix\NDArray;

interface Space
{
    public function sample() : NDArray;
    public function contains(NDArray $x, ?bool $throw=null, ?string $type=null) : bool;
    /** @return array<int> */
    public function shape() : array;
    public function dtype() : int;
}
