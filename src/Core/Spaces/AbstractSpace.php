<?php
namespace Rindow\RL\Gym\Core\Spaces;

use Interop\Polite\Math\Matrix\NDArray;

abstract class AbstractSpace implements Space
{
    protected object $la;
    /** @var array<int> */
    protected array $shape;
    protected int $dtype;
    /** @var array<int,string> $dtypeToString */
    protected array $dtypeToString = [
        NDArray::bool=>'bool',
        NDArray::int8=>'int8',   NDArray::uint8=>'uint8',
        NDArray::int16=>'int16', NDArray::uint16=>'uint16',
        NDArray::int32=>'int32', NDArray::uint32=>'uint32',
        NDArray::int64=>'int64', NDArray::uint64=>'uint64',
        NDArray::float16=>'float16',
        NDArray::float32=>'float32', NDArray::float64=>'float64',
        NDArray::complex64=>'complex64', NDArray::complex128=>'complex128',
    ];

    /**
     * @param array<int> $shape
     */
    public function __construct(
        object $la,
        ?array $shape=null,
        ?int $dtype=null,
        ?int $seed=null
    )
    {
        $this->la = $la;
        if($shape===null) {
            $shape = [];
        }
        if($dtype===null) {
            $dtype = NDArray::int32;
        }
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

    protected function dtypeToString(int $dtype) : string
    {
        if(!isset($this->dtypeToString[$dtype])) {
            return 'Unknown';
        }
        return $this->dtypeToString[$dtype];
    }

}
