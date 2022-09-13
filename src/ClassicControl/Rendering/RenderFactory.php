<?php
namespace Rindow\RL\Gym\ClassicControl\Rendering;

use Rindow\RL\Gym\Core\Graphics\GDGL;

class RenderFactory
{
    protected $drivers = [
        'gd' => GDGL::class,
    ];
    protected $la;
    protected $driverName;
    protected $metadata;

    public function __construct($la,$driverName,$metadata=null)
    {
        $this->la = $la;
        $this->driverName = $driverName;
        $this->metadata = $metadata;
    }

    public function factory()
    {
        $driverName = $this->driverName;
        if(isset($this->drivers[$driverName])) {
            $driverName = $this->drivers[$driverName];
        }
        $driver = new $driverName($this->la, config:$this->metadata);
        $rendering = new Rendering($driver);
        return $rendering;
    }

    public function Viewer(int $width, int $height, $display=null)
    {
        $rendering = $this->factory();
        return $rendering->Viewer($width, $height, $display);
    }
}
