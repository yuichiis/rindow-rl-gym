<?php
namespace Rindow\RL\Gym\Core\Graphics;

use GdImage;
use RuntimeException;

class Image
{
    protected ?GdImage $img=null;

    public function load(string $fname) : void
    {
        $data = file_get_contents($fname);
        if($data===false) {
            throw new RuntimeException("image file not found: ".$fname);
        }
        $img = imagecreatefromstring($data);
        if($img===false) {
            throw new RuntimeException("unknown format: ".$fname);
        }
        imagealphablending($img,false);
        //imageflip($img,IMG_FLIP_VERTICAL);
        unset($data);
        $this->img = $img;
    }

    //public function blit(int $centerx, int $centery, int $width, int $height)
    //{
    //}
    public function img() : ?GdImage
    {
        return $this->img;
    }

    public function width() : int
    {
        return imagesx($this->img);
    }

    public function height() : int
    {
        return imagesy($this->img);
    }

    public function __destruct()
    {
        if($this->img) {
            imagedestroy($this->img);
            $this->img = null;
        }
    }
}
