<?php
namespace Rindow\RL\Gym\ClassicControl\MultiarmedBandit;

use Rindow\RL\Gym\Core\AbstractEnv;
use Rindow\RL\Gym\Core\Spaces\Discrete;

class Slots extends AbstractEnv
{
    protected $p;              // List<float>
    protected $thresholds = []; // List<int>
    protected $num;

    public function __construct(object $la,array $probabilities, array $metadata=null)
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
    }

    /**
    * @param Any $action
    * @return Set(Any $observation, Any $reward, bool $done, Dict $info)
    */
    protected function doStep($action) : array
    {
        //if($action<0 || $action>=$this->num) {
        //    throw new InvalidArgumentException('Invalid action');
        //}
        $threshold = $this->thresholds[$action];
        if( $threshold > mt_rand()) {
            $reward = 1.0;
        } else {
            $reward = 0.0;
        }
        return [0,$reward,true,[]];
     }

    /**
    * @return Any $observation
    **/
    protected function doReset()
    {
        return 0;
    }

}
