<?php
namespace Rindow\RL\Gym\ClassicControl\MultiarmedBandit;

use Rindow\RL\Gym\Core\AbstractEnv;
use Rindow\RL\Gym\Core\Spaces\Discrete;
use Interop\Polite\Math\Matrix\NDArray;

class Slots extends AbstractEnv
{
    /** @var array<float> $p */
    protected array $p;
    /** @var array<int> $thresholds */
    protected array $thresholds = [];
    protected int $num;
    protected NDArray $obs;

    /**
     * @param array<float> $probabilities
     * @param array<string,mixed> $metadata
     */
    public function __construct(object $la,array $probabilities, ?array $metadata=null)
    {
        parent::__construct($la);
        if($metadata) {
            $this->mergeMetadata($metadata);
        }
        $this->p = $probabilities;
        foreach ($probabilities as $p) {
            $this->thresholds[] = (int)floor($p * getrandmax());
        }
        $this->setActionSpace(new Discrete($la,count($this->thresholds)));
        $this->num = count($this->thresholds);
        $this->obs = $la->array(0,dtype:NDArray::int32);
    }

    /**
    * return {NDArray $observation, float $reward, bool $done, array<string,mixed> $info}
    */
    protected function doStep(NDArray $action) : array
    {
        //if($action<0 || $action>=$this->num) {
        //    throw new InvalidArgumentException('Invalid action');
        //}
        $threshold = $this->thresholds[$this->la->scalar($action)];
        if( $threshold > mt_rand()) {
            $reward = 1.0;
        } else {
            $reward = 0.0;
        }
        // [obs, reward, done, info]
        return [$this->obs,$reward,true,[]];
     }

    /**
    * return $observation
    **/
    protected function doReset() : NDArray
    {
        return $this->obs;
    }

}
