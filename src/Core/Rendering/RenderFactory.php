<?php
namespace Rindow\RL\Gym\Core\Rendering;

use Rindow\RL\Gym\Core\Graphics\GDGL;

class RenderFactory
{
    protected $drivers = [
        'gd' => GDGL::class,
    ];
    protected $la;
    protected $driverName;

    public function __construct($la,$driverName)
    {
        $this->la = $la;
        $this->driverName = $driverName;
    }

    public function factory()
    {
        $driverName = $this->driverName;
        if(isset($this->drivers[$driverName])) {
            $driverName = $this->drivers[$driverName];
        }
        $driver = new $driverName($this->la);
        $rendering = new Rendering($driver);
        return $rendering;
    }

    public function Viewer(int $width, int $height, $display=null)
    {
        $rendering = $this->factory();
        return $rendering->Viewer($width, $height, $display);
    }
}
