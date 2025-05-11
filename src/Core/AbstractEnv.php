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
    /**
     * {NDArray $observation, float $reward, bool $done, array<string,mixed> $info}
     * @return array{NDArray, float, bool, array<string,mixed>}
     */
    abstract protected function doStep(NDArray $action) : array;
    abstract protected function doReset() : NDArray;

    protected ?Space $actionSpace = null;
    protected ?Space $observationSpace = null;
    protected bool $throwObservationSpaceError = false;
    protected int $maxEpisodeSteps = 0;
    protected int $elapsedSteps = 0;
    protected float $rewardThreshold = 0.0;   //  N/A
    protected object $la;
    protected ?object $viewer=null;
    /** @var array<string,mixed> $metadata */
    protected array $metadata = [];

    public function __construct(object $la)
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

    public function observationSpace() : ?Space
    {
        return $this->observationSpace;
    }

    public function actionSpace() : ?Space
    {
        return $this->actionSpace;
    }

    /**
     * @param array<string,mixed> $metadata
     */
    protected function mergeMetadata(array $metadata) : void
    {
        $this->metadata = array_replace_recursive($this->metadata,$metadata);
    }

    /**
     * {NDArray $observation, float $reward, bool $done, array<string,mixed> $info}
     * @return array{NDArray, float, bool, array<string,mixed>}
     */
    public function step(mixed $action) : array
    {
        if(!($action instanceof NDArray)) {
            $type = is_object($action) ? get_class($action) :gettype($action);
            throw new InvalidArgumentException("Action must be NDArray. $type given.");
        }
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

    protected function setActionSpace(Space $space) : void
    {
        $this->actionSpace = $space;
    }

    protected function setObservationSpace(Space $space) : void
    {
        $this->observationSpace = $space;
    }

    public function setThrowObservationSpaceError(bool $switch) : void
    {
        $this->throwObservationSpaceError = $switch;
    }

    protected function checkActionSpace(NDArray $action) : void
    {
        if($this->actionSpace===null) {
            return;
        }
        $error = $this->checkSpace($this->actionSpace,$action,'Action');
        if($error) {
            throw new RuntimeException($error);
        }
    }

    protected function checkObsSpace(NDArray $observation) : bool
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

    protected function checkSpace(Space $space, NDArray $value, string $type) : ?string
    {
        $la = $this->la;
        try {
            $space->contains($value,throw:true,type:$type);
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
    * return NDArray $observation
    **/
    public function reset() : mixed
    {
        $this->elapsedSteps = 0;
        return $this->doReset();
    }

    /**
    * return mixed $depends on vender
    */
    public function render(?string $mode=null) : mixed
    {
        throw new LogicException("Not implemented.");
    }

    /**
    * return mixed $depends on vender
    */
    public function show(?bool $loop=null,?int $delay=null) : mixed
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
    * @return array<int> $seeds
    */
    public function seed(?int $seed=null) : array
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
    public function exit(?Throwable $e=null) : bool
    {
        return true;
    }
}
