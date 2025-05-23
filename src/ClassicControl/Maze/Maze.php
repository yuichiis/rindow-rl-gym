<?php
namespace Rindow\RL\Gym\ClassicControl\Maze;

use Rindow\RL\Gym\Core\AbstractEnv;
use Rindow\RL\Gym\Core\Spaces\Discrete;
use Rindow\RL\Gym\ClassicControl\Rendering\RenderFactory;
use Rindow\RL\Gym\ClassicControl\Rendering\Transform;
use Rindow\RL\Gym\ClassicControl\Rendering\Geom;
use Interop\Polite\Math\Matrix\NDArray;
use InvalidArgumentException;
use RuntimeException;
use LogicException;

class Maze extends AbstractEnv
{
    protected array $metadata = ["render.modes"=> ["human", "rgb_array"], "video.frames_per_second"=> 50];

    const UP    = 0;
    const DOWN  = 1;
    const RIGHT = 2;
    const LEFT  = 3;

    protected int $maxEpisodeSteps=500;

    protected object $la;
    protected NDArray $policy;
    protected ?int $observation=null;
    protected bool $throwInvalidAction = true;
    protected object $renderingFactory;
    protected int $width;
    protected int $height;
    protected int $exit;
    protected Geom $man;
    protected Transform $mantrans;

    /**
     * @param array<string,mixed> $metadata
     */
    public function __construct(
        object $la,NDArray $policy,
        int $width,int $height,int $exit,
        ?int $throwInvalidAction=null,?int $maxEpisodeSteps=null,
        ?array $metadata=null, ?object $renderer=null)
    {
        parent::__construct($la);
        if($metadata) {
            $this->mergeMetadata($metadata);
        }
        if($renderer===null) {
            $renderer = new RenderFactory($la,'gd',$this->metadata);
        }
        $this->renderingFactory = $renderer;
        if($policy->ndim()!=2) {
            throw new InvalidArgumentException('policy must be 2D NDArray');
        }
        if($throwInvalidAction===null) {
            $throwInvalidAction = true;
        }
        $this->policy = $policy;
        $this->width = $width;
        $this->height = $height;
        $this->exit = $exit;
        $this->throwInvalidAction = $throwInvalidAction;
        if($maxEpisodeSteps!==null) {
            $this->maxEpisodeSteps = $maxEpisodeSteps;
        }
        [$states,$actions] = $policy->shape();
        $this->reset();
        $this->setActionSpace(new Discrete($la,$actions));
        $this->setObservationSpace(new Discrete($la,$states));
        $this->setThrowObservationSpaceError(true);
    }

    protected function doStep(NDArray $action) : array
    {
        $la = $this->la;
        $action = $la->scalar($action);
        $observation = $this->observation;
        if($this->exit==$observation) {
            throw new LogicException('Please do after reset');
        }
        if(!($this->policy[$observation][$action]>0)) {
            if($this->throwInvalidAction) {
                throw new RuntimeException('Unauthorized action: s='.$observation.',a='.$action);
            }
            $observation = $la->array($observation,dtype:NDArray::int32);
            return [$observation,$reward=-1.0,$done=true,[]];
        }
        $observation = $this->nextStep($observation,$action);
        $this->observation = $observation;
        $done = ($this->exit==$observation);
        $reward = -1.0;
        $observation = $la->array($observation,dtype:NDArray::int32);
        return [$observation,$reward,$done,[]];
    }

    protected function nextStep(int $position, int $action) : int
    {
        switch ($action) {
            case self::UP:
                return $position - $this->width;
            case self::DOWN:
                return $position + $this->width;
            case self::RIGHT:
                return $position + 1;
            case self::LEFT:
                return $position - 1;
            default:
                throw new InvalidArgumentException('Invalid action');
        }
    }

    protected function doReset() : NDArray
    {
        $this->observation = 0;
        $observation = $this->la->array($this->observation,dtype:NDArray::int32);
        return $observation;
    }

    public function render(?string $mode=null) : mixed
    {
        $mode ??= "human";
        $policy = $this->policy;
        $width = $this->width;
        $height = $this->height;

        $line_width = 10;
        $screen_width = 500;
        $screen_height = (int)($screen_width*$height/$width);
        $scalex = ($screen_width-$line_width)/$width;
        $offsetx = $line_width/2;
        $scaley = -$scalex;
        $offsety = $screen_height-$line_width/2;

        if($this->viewer===null)
        {
            $wall_lines = [
                [[-0.48, -0.48], [+0.48, -0.48]],  // UP
                [[-0.48, +0.48], [+0.48, +0.48]],  // DOWN
                [[+0.48, -0.48], [+0.48, +0.48]],  // RIGHT
                [[-0.48, -0.48], [-0.48, +0.48]],  // LEFT
            ];
            $rendering = $this->renderingFactory->factory();
            $this->viewer = $rendering->Viewer($screen_width, $screen_height);
            for($y=0;$y<$height;$y++) {
                for($x=0;$x<$width;$x++) {
                    $pos = $y*$width+$x;
                    foreach ($policy[$pos] as $key => $value) {
                        if($value==1) {
                            continue;
                        }
                        [$s,$e] = $wall_lines[$key];
                        $wall = $rendering->Line(
                            [($s[0]+$x+0.5)*$scalex+$offsetx,($s[1]+$y+0.5)*$scaley+$offsety],
                            [($e[0]+$x+0.5)*$scalex+$offsetx,($e[1]+$y+0.5)*$scaley+$offsety]);
                        $wall->set_color(0, 0, 0);
                        $wall->set_linewidth($line_width);
                        $this->viewer->add_geom($wall);
                    }
                }
            }
            $goalx = $this->exit % $width;
            $goaly = floor($this->exit / $width);
            $goal = $rendering->make_circle($radius=0.2*$scalex);
            $goal->set_color(1,0,0);
            $goaltrans = $rendering->Transform([(0.5+$goalx)*$scalex+$offsetx, (0.5+$goaly)*$scaley+$offsety]);
            $goal->add_attr($goaltrans);
            $this->viewer->add_geom($goal);

            $this->man = $rendering->make_circle($radius=0.2*$scalex);
            $this->man->set_color(0,0,1);
            $this->mantrans = $rendering->Transform([0.5*$scalex+$offsetx, 0.5*$scaley+$offsety]);
            $this->man->add_attr($this->mantrans);
            $this->viewer->add_geom($this->man);
        }
        if($this->observation===null) {
            return null;
        }
        $manx = $this->observation % $width;
        $many = floor($this->observation / $width);
        $this->mantrans->set_translation((0.5+$manx)*$scalex+$offsetx, (0.5+$many)*$scaley+$offsety);
        return $this->viewer->render($mode);
    }
}
