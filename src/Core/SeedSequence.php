<?php
namespace Rindow\RL\Gym\Core;

class SeedSequence
{
    private int $sequence;
    private int $state;

    public function __construct(int $seed, ?int $sequence=null)
    {
        $this->sequence = $sequence ?? 0;
        $this->seed($seed);
    }

    public function seed(int $seed) : void
    {
        $this->state = ($seed + $this->sequence) & 0x7fffffff;
    }

    public function next() : int
    {
        $this->state = (int)(($this->state * 1664525 + 1013904223) & 0x7fffffff);
        return $this->state;
    }

    public function randInt(?int $min=null, ?int $max=null) : int
    {
        $min ??= ~0x7fffffff;
        $max ??= 0x7fffffff;
        $this->next();
        return $min + ($this->state % ($max - $min + 1));
    }
}
