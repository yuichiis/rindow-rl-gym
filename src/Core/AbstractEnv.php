<?php
namespace Rindow\RL\Gym\Core;

use LogicException;
use RuntimeException;
use InvalidArgumentException;
use Throwable;
use Interop\Polite\AI\RL\Environment;
use Interop\Polite\Math\Matrix\NDArray;
use Rindow\RL\Gym\Core\Spaces\Space;

abstract class AbstractEnv implements Environment
{
    abstract protected function doStep($action) : array;
    abstract protected function doReset();

    protected $actionSpace;
    protected $observationSpace;
    protected $throwObservationSpaceError = false;
    protected $maxEpisodeSteps = 0;
    protected $elapsedSteps = 0;
    protected $rewardThreshold = 0;   //  N/A
    protected $la;
    protected $viewer;
    protected $metadata = [];

    public function __construct($la)
    {
        $this->la = $la;
    }

    public function maxEpisodeSteps() : int
    {
        return $this->maxEpisodeSteps;
    }

    public function rewardThreshold() : float
    {
        return $this->rewardThreshold;
    }

    public function observationSpace() : mixed
    {
        return $this->observationSpace;
    }

    public function actionSpace() : mixed
    {
        return $this->actionSpace;
    }

    protected function mergeMetadata(array $metadata) : void
    {
        $this->metadata = array_replace_recursive($this->metadata,$metadata);
    }

    /**
    * @param Any $action
    * @return Set(Any $observation, Any $reward, bool $done, Dict $info)
    */
    public function step($action) : array
    {
        $this->checkActionSpace($action);
        $results = $this->doStep($action);
        [$observation,$reward,$done,$info] = $results;
        if(!$this->checkObsSpace($observation)) {
            $done = true;
        }
        if(!$this->checkEpisodeSteps()) {
            $done = true;
        }
        return [$observation,$reward,$done,$info];
    }

    protected function setActionSpace($space) : void
    {
        if(!($space instanceof Space)) {
            throw new InvalidArgumentException('Action space is invalid type:'.gettype($space));
        }
        $this->actionSpace = $space;
    }

    protected function setObservationSpace($space) : void
    {
        if(!($space instanceof Space)) {
            throw new InvalidArgumentException('Observation space is invalid type:'.gettype($space));
        }
        $this->observationSpace = $space;
    }

    public function setThrowObservationSpaceError(bool $switch)
    {
        $this->throwObservationSpaceError = $switch;
    }

    protected function checkActionSpace($action) : void
    {
        if($this->actionSpace===null) {
            return;
        }
        $error = $this->checkSpace($this->actionSpace,$action,'Action');
        if($error) {
            throw new RuntimeException($error);
        }
    }

    protected function checkObsSpace($observation) : bool
    {
        if($this->observationSpace===null) {
            return true;
        }
        $error = $this->checkSpace($this->observationSpace,$observation,'Observation');
        if($error) {
            if($this->throwObservationSpaceError) {
                throw new RuntimeException($error);
            }
            return false;
        }
        return true;
    }

    protected function checkSpace($space,$value,$type)
    {
        $la = $this->la;
        try {
            $space->contains($value,$throw=true,$type);
        } catch (RuntimeException $e) {
            return $e->getMessage();
        }
        return null;
    }

    protected function checkEpisodeSteps() : bool
    {
        $this->elapsedSteps++;
        if($this->maxEpisodeSteps > 0) {
            if($this->elapsedSteps >= $this->maxEpisodeSteps) {
                return false;
            }
        }
        return true;
    }

    /**
    * @return Any $observation
    **/
    public function reset() : mixed
    {
        $this->elapsedSteps = 0;
        return $this->doReset();
    }

    /**
    * @return Any $depends on vender
    */
    public function render(string $mode=null) : mixed
    {
        throw new LogicException("Not implemented.");
    }

    /**
    * @return Any $depends on vender
    */
    public function show(bool $loop=null,int $delay=null) : mixed
    {
        if($this->viewer===null) {
            throw new LogicException('Viewer is not ready');
        }
        $this->viewer->show($loop, $delay);
        return null;
    }

    /**
    *
    */
    public function close() : void
    {
        if($this->viewer) {
            $this->viewer->close();
            $this->viewer = null;
        }
    }

    /**
    * @param int $seed
    * @return List<int> $seeds
    */
    public function seed(int $seed=null) : array
    {
        if($seed===null) {
            $seed = random_int(~PHP_INT_MAX,PHP_INT_MAX);
        }
        mt_srand($seed);
        return [$seed];
    }

    protected function remainder(float $x, float $y) : float
    {
        $v = floor($x / $y);
        $r = $x - $v*$y;
        $r = ($y>0) ? abs($r) : -abs($r);
        return $r;
    }

    /**
    *
    */
    public function toString() : string
    {
        throw new LogicException("Not implemented.");
    }

    /**
    *
    */
    public function enter() : void
    {}

    /**
    *
    */
    public function exit(Throwable $e=null) : bool
    {
        return true;
    }
}
