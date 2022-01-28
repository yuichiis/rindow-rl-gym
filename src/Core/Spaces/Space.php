<?php
namespace Rindow\RL\Gym\Core\Spaces;

interface Space
{
    public function sample();
    public function contains($x,bool $throw=null,string $type=null);
    public function shape() : array;
    public function dtype();
}
