<?php
namespace Rindow\RL\Gym\Core\Graphics;

class Image
{
    protected $img;

    public function load($fname)
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
    public function img()
    {
        return $this->img;
    }

    public function width()
    {
        return imagesx($this->img);
    }

    public function height()
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
