<?php
namespace Rindow\RL\Gym\ClassicControl\Rendering;

use Rindow\RL\Gym\Core\Graphics\GDGL;

class RenderFactory
{
    /** @var array<string,string> $drivers */
    protected array $drivers = [
        'gd' => GDGL::class,
    ];
    protected object $la;
    protected string $driverName;
    /** @var array<string,mixed> $metadata */
    protected ?array $metadata;

    /**
     * @param array<string,mixed> $metadata
     */
    public function __construct(object $la, string $driverName, ?array $metadata=null)
    {
        $this->la = $la;
        $this->driverName = $driverName;
        $this->metadata = $metadata;
    }

    public function factory() : Rendering
    {
        $driverName = $this->driverName;
        if(isset($this->drivers[$driverName])) {
            $driverName = $this->drivers[$driverName];
        }
        $driver = new $driverName($this->la, config:$this->metadata);
        $rendering = new Rendering($driver);
        return $rendering;
    }

    public function Viewer(int $width, int $height, mixed $display=null) : Viewer
    {
        $rendering = $this->factory();
        return $rendering->Viewer($width, $height, $display);
    }
}
