<?php
namespace Rindow\RL\Gym\Core\Graphics;

use LogicException;
use RuntimeException;
use Interop\Polite\Math\Matrix\NDArray;
use Rindow\Math\Matrix\NDArrayPhp;

class GDGL
{
    const DEG2RAD = 0.017453292519943;

    protected $la;
    protected $currentMatrix;
    protected $viewMatrix;
    protected $stackMatrix = [];
    protected $gd;
    protected $color;
    protected $clearColor;
    protected $mode;
    protected $cap = [];
    protected $outputFiles = [];
    protected $gifViewer = 'RINDOW_MATH_PLOT_VIEWER';
    protected $skipRunViewer=false;
    protected $blendSrcFactor;
    protected $blendDstFactor;

    public function __construct($la)
    {
        $this->la = $la;
        $this->currentMatrix = $la->array([
            [1,0,0,0],
            [0,1,0,0],
            [0,0,1,0],
            [0,0,0,1],
        ]);
    }

    /**
    * GL_QUADS
    * GL_POLYGON
    * GL_TRIANGLES
    * GL_LINES
    * GL_POINTS
    **/
    public function glBegin(int $mode)
    {
        $this->mode = $mode;
        $this->points = [];
    }

    public function glEnd()
    {
        switch($this->mode) {
            case GL::GL_POINTS: {
                $this->renderPoint();
                break;
            }
            case GL::GL_LINES: {
                $this->renderLine();
                break;
            }
            case GL::GL_LINE_STRIP:
            case GL::GL_LINE_LOOP: {
                $this->renderLineStrip($this->mode);
                break;
            }
            case GL::GL_TRIANGLES: {
                $this->renderPolygon(3);
                break;
            }
            case GL::GL_QUADS: {
                $this->renderPolygon(4);
                break;
            }
            case GL::GL_POLYGON: {
                $this->renderPolygon(null);
                break;
            }
            default: {
                throw new LogicException('unknown primitive mode');
            }
        }
        $this->mode = null;
    }

    protected function realColor()
    {
        if(isset($this->cap[GL::GL_LINE_STIPPLE])) {
            return IMG_COLOR_STYLED;
        }
        return imagecolorallocatealpha($this->gd,...$this->color);
    }

    protected function realCoordinate(array $point)
    {
        $la = $this->la;
        array_push($point,1);
        $p = $la->array($point);
        $p = $la->gemv($this->currentMatrix,$p);
        if($this->viewMatrix) {
            $p = $la->gemv($this->viewMatrix,$p);
        }
        $p = $la->astype($p,NDArray::int32);
        return $p;
    }

    protected function renderPoint()
    {
        $color = $this->realColor();
        while($point = array_shift($this->points)) {
            $point = $this->realCoordinate($point);
            imagesetpixel($this->gd,$point[0],$point[1],$color);
        }
    }

    protected function renderLine()
    {
        $color = $this->realColor();
        while($start = array_shift($this->points)) {
            $start = $this->realCoordinate($start);
            $end = array_shift($this->points);
            if($end==null) {
                break;
            }
            $end = $this->realCoordinate($end);
            imageline($this->gd,$start[0],$start[1],$end[0],$end[1],$color);
        }
    }

    protected function renderLineStrip($mode)
    {
        $color = $this->realColor();
        $start = array_shift($this->points);
        $start = $this->realCoordinate($start);
        $last = $start;
        while($end = array_shift($this->points)) {
            $end = $this->realCoordinate($end);
            imageline($this->gd,$start[0],$start[1],$end[0],$end[1],$color);
            $start = $end;
        }
        if($mode==GL::GL_LINE_LOOP) {
            imageline($this->gd,$start[0],$start[1],$last[0],$last[1],$color);
        }
    }

    protected function renderPolygon($numVertex)
    {
        $php81 = (version_compare(phpversion(),'8.1.0')>=0);
        if($numVertex===null) {
            $numVertex=count($this->points);
        }
        $color = $this->realColor();
        $points = [];
        $numPoints = 0;
        while($point = array_shift($this->points)) {
            $point = $this->realCoordinate($point);
            $points[] = $point[0];
            $points[] = $point[1];
            $numPoints++;
            if($numPoints>=$numVertex) {
                if($php81) {
                    imagefilledpolygon($this->gd,$points,$color);
                } else {
                    imagefilledpolygon($this->gd,$points,$numPoints,$color);
                }
                $points = [];
                $numPoints = 0;
            }
        }
    }

    public function renderImage(Image $image, float $centerx, float $centery, float $width, float $height)
    {
        // *** CAUTION ***
        // $centerx, $centery are not supported.
        // the center must be the center of image.

        $la = $this->la;
        $org_img = $image->img();
        $img = $org_img;
        $imgsx = imagesx($img);
        $imgsy = imagesy($img);
        $src_width = imagesx($img);
        $src_height = imagesy($img);

        [$trans,$rotate,$scale] = $this->rotationalDecomposition();
        $flip = [1,1,1];
        $dstw = (int)ceil(abs($scale[0])*$width);
        $dsth = (int)ceil(abs($scale[1])*$height);
        if($imgsx!=$dstw||$imgsy!=$dsth) {
            $newImg = imagecreatetruecolor($dstw,$dsth);
            imagealphablending($newImg,false);
            imagecopyresampled(
                $newImg,
                $img,
                0,0,
                0,0,
                $dstw,$dsth,
                $imgsx,$imgsy
            );
            $img = $newImg;
            $imgsx=$dstw;
            $imgsy=$dsth;
            unset($newImg);
        }
        if($scale[0]<0) {
            if($org_img===$img) {
                $newImg = imagecreatetruecolor($imgsx,$imgsy);
                imagealphablending($newImg,false);
                imagecopy($newImg,$img,0,0,0,0,$imgsx,$imgsy);
                $img = $newImg;
                unset($newImg);
            }
            imageflip($img,IMG_FLIP_HORIZONTAL);
            $flip[0] = -1;
        }
        if($scale[1]<0) {
            if($org_img===$img) {
                $newImg = imagecreatetruecolor($imgsx,$imgsy);
                imagealphablending($newImg,false);
                imagecopy($newImg,$img,0,0,0,0,$imgsx,$imgsy);
                $img = $newImg;
                unset($newImg);
            }
            imageflip($img,IMG_FLIP_VERTICAL);
            $flip[1] = -1;
        }

        $rotdeg = 0;
        if($rotate!=0) {
            $rotdeg = $rotate/self::DEG2RAD;
            $backcolor = imagecolorallocatealpha($img,0,0,0,127);
            $img = imagerotate($img,-$rotdeg,$backcolor);
            imagealphablending($img,false);
            $imgsx = imagesx($img);
            $imgsy = imagesy($img);
        }

        //$dst_center = $this->realCoordinate([$centerx,$centery,0.0]);
        $dst_center = $this->realCoordinate([0.0,0.0,0.0]);
        $dst_center[0] = $dst_center[0]-(int)floor($imgsx/2);
        $dst_center[1] = $dst_center[1]-(int)floor($imgsy/2);

        imagecopy(
            $this->gd,$img,
            $dst_center[0],$dst_center[1],
            0,0,
            $imgsx,$imgsy
        );
    }

    public function rotationalDecomposition()
    {
        $la = $this->la;
        $trans = $la->gemv($this->currentMatrix,$la->array([0,0,0,1]));
        $xbase_r = $la->gemv($this->currentMatrix,$la->array([1,0,0,1]));
        $ybase_r = $la->gemv($this->currentMatrix,$la->array([0,1,0,1]));
        $la->axpy($trans,$xbase_r,-1);
        $la->axpy($trans,$ybase_r,-1);
        
        //echo "xbase_r=[".implode(',',$xbase_r)."],ybase_r=[".implode(',',$ybase_r)."]\n";
        $dxscale=sqrt($xbase_r[0]**2+$xbase_r[1]**2);
        $dyscale=sqrt($ybase_r[0]**2+$ybase_r[1]**2);
    
        $illegal = false;
        if(abs($xbase_r[0])>abs($xbase_r[1])) {
            if($xbase_r[0]>=0&&$ybase_r[1]>=0) {
                $th = atan($xbase_r[1]/$xbase_r[0]);      // ==== Front side 1 ====
                $scale = [$dxscale,$dyscale];             // -45 ~ +45 deg
                $flags = 'F1';
            } elseif($xbase_r[0]<0&&$ybase_r[1]<0) {
                $th = atan($xbase_r[1]/$xbase_r[0])+M_PI; // ==== Front side 2 ====
                $scale = [$dxscale,$dyscale];             // +135 ~ -135 deg
                $flags = 'F2';
            } elseif($xbase_r[0]>=0&&$ybase_r[1]<0) {
                $th = atan($xbase_r[1]/$xbase_r[0])+M_PI; // ==== Back side 1 ====
                $scale = [-$dxscale,$dyscale];            // +135 ~ -135 deg
                $flags = 'B1';
            } elseif($xbase_r[0]<0&&$ybase_r[1]>=0) {
                $th = atan($xbase_r[1]/$xbase_r[0]);      // ==== Back side 2 ====
                $scale = [-$dxscale,$dyscale];            //  -45 ~ +45 deg
                $flags = 'B2';
            } else {
                $illegal = true;
            }
        } else {
            if($xbase_r[1]>=0&&$ybase_r[0]<0) {
                $th = M_PI/2-atan($xbase_r[0]/$xbase_r[1]);  // ==== Front side 3 ====
                $scale = [$dxscale,$dyscale];                // +45 ~ +135 deg
                $flags = 'F3';
            } elseif($xbase_r[1]<0&&$ybase_r[0]>=0) {
                $th = -M_PI/2-atan($xbase_r[0]/$xbase_r[1]); // ==== Front side 4 ====
                $scale = [$dxscale,$dyscale];                // -135 ~ -45 deg
                $flags = 'F4';
            } elseif($xbase_r[1]>=0&&$ybase_r[0]>=0) {
                $th = -M_PI/2-atan($xbase_r[0]/$xbase_r[1]); // ==== Back side 3 ====
                $scale = [-$dxscale,$dyscale];               // -135 ~ -45 deg
                $flags = 'B3';
            } elseif($xbase_r[1]<0&&$ybase_r[0]<0) {
                $th = M_PI/2-atan($xbase_r[0]/$xbase_r[1]);  // ==== Back side 4 ====
                $scale = [-$dxscale,$dyscale];               //  +45 ~ +135 deg
                $flags = 'B4';
            } else {
                $illegal = true;
            }
        }
        if($illegal) {
            echo "scalex=".$scalex.",scaley=".$scaley.",rotate=".sprintf("%2.0f",$theta*180/M_PI)."\n";
            echo "xb=".implode(',',$xbase_r)." yb=".implode(',',$ybase_r)."\n";
            throw new \Exception("Illegal");
        }
        if($th>M_PI) {
            $th -= 2*M_PI;
        }
        if($th<-M_PI) {
            $th += 2*M_PI;
        }
        return [$trans,$th,$scale,$flags];
    }

    public function glVertex2f(float $x, float $y)
    {
        $this->points[] = [$x, $y, 0];
    }

    public function glVertex3f(float $x, float $y, float $z)
    {
        $this->points[] = [$x, $y, $z];
    }

    /**
    * GL_LINE_STIPPLE
    * GL_BLEND
    */
    public function glEnable(int $cap)
    {
        $this->cap[$cap] = true;
        switch($cap) {
            case GL::GL_BLEND: {
                imagealphablending($this->gd,true);
                break;
            }
        }
    }

    /**
    * GL_LINE_STIPPLE
    */
    public function glDisable(int $cap)
    {
        unset($this->cap[$cap]);
        switch($cap) {
            case GL::GL_BLEND: {
                imagealphablending($this->gd,false);
                break;
            }
        }
    }

    /**
    * GL_SRC_ALPHA
    * GL_ONE_MINUS_SRC_ALPHA
    */
    public function glBlendFunc(int $sfactor, int $dfactor)
    {
        $this->blendSrcFactor = $sfactor;
        $this->blendDstFactor = $dfactor;
        if($sfactor==GL::GL_SRC_ALPHA&&$dfactor==GL::GL_ONE_MINUS_SRC_ALPHA) {
            imagelayereffect($this->gd, IMG_EFFECT_ALPHABLEND);
        } elseif ($sfactor==GL::GL_ZERO&&$dfactor==GL::GL_SRC_ALPHA) {
            imagelayereffect($this->gd, IMG_EFFECT_MULTIPLY);
        } elseif ($sfactor==GL::GL_SRC_ONE&&$dfactor==GL::GL_ZERO) {
            imagelayereffect($this->gd, IMG_EFFECT_REPLACE);
        }
    }

    protected function transColor(float $red, float $green, float $blue, float $alpha)
    {
        $phpAlpha = (int)floor(min((1-$alpha)*128,127));
        return [
            (int)floor(min($red*256,255)),
            (int)floor(min($green*256,255)),
            (int)floor(min($blue*256,255)),
            $phpAlpha,
        ];
    }

    public function glColor4f(float $red, float $green, float $blue, float $alpha)
    {
        $this->color = $this->transColor($red, $green, $blue, $alpha);
    }

    public function glClearColor(float $red, float $green, float $blue, float $alpha)
    {
        $this->clearColor = $this->transColor($red, $green, $blue, $alpha);
    }

    public function glLineStipple(int $factor, int $pattern)
    {
        $style = [];
        for($b=0;$b<16;$b++) {
            for($n=0;$n<$factor;$n++) {
                $style[] = ($pattern&0x0001)?$this->color:$this->clearColor;
            }
            $pattern = $pattern >> 1;
        }
        imagesetstyle($this->gd,$style);
    }

    public function glLineWidth(float $width)
    {
        $width = (int)ceil($width);
        imagesetthickness($this->gd,$width);
    }

    public function glPushMatrix()
    {
        array_push($this->stackMatrix,$this->currentMatrix);
    }

    public function glPopMatrix()
    {
        if(count($this->stackMatrix)==0) {
            throw new LogicException('Matrix Stack is empty');
        }
        $this->currentMatrix = array_pop($this->stackMatrix);
    }

    public function glTranslatef(float $x, float $y, float $z)
    {
        $la = $this->la;
        $trans = $la->array([
            [ 1, 0, 0,$x],
            [ 0, 1, 0,$y],
            [ 0, 0, 1,$z],
            [ 0, 0, 0, 1],
        ]);
        $this->currentMatrix = $la->gemm($this->currentMatrix,$trans);
    }

    public function glRotatef(float $angle, float $x, float $y, float $z)
    {
        $la = $this->la;
        $angle = $angle*self::DEG2RAD;
        $c = cos($angle);
        $s = -sin($angle);
        $rotate = $la->array([
            [($x**2)*(1-$c)+$c,  $y*$x*(1-$c)+$z*$s, $x*$z*(1-$c)-$y*$s, 0 ],
            [$x*$y*(1-$c)-$z*$s, ($y**2)*(1-$c)+$c,  $y*$z*(1-$c)+$x*$s, 0 ],
            [$x*$z*(1-$c)+$y*$s, $y*$z*(1-$c)-$x*$s, ($z**2)*(1-$c)+$c,  0 ],
            [ 0, 0, 0, 1],
        ]);
        $this->currentMatrix = $la->gemm($this->currentMatrix,$rotate);
    }

    public function glScalef(float $x,float $y,float $z)
    {
        $la = $this->la;
        $scale = $la->array([
            [$x, 0, 0, 0],
            [ 0,$y, 0, 0],
            [ 0, 0,$z, 0],
            [ 0, 0, 0, 1],
        ]);
        $this->currentMatrix = $la->gemm($this->currentMatrix,$scale);
    }

    public function get_display($display)
    {
        return null;
    }

    public function get_window($width, $height, $display)
    {
        $this->gd = imagecreatetruecolor($width, $height);
        $this->width = $width;
        $this->height = $height;
        //$this->viewMatrix = $this->la->array([
        //    [1, 0,0,0],
        //    [0,-1,0,$height-1],
        //    [0, 0,1,0],
        //    [0, 0,0,1],
        //]);
        return new Window($this, $width, $height, $display);
    }

    public function clear()
    {
        $c = $this->clearColor;
        $color = imagecolorallocate($this->gd,$c[0],$c[1],$c[2]);
        imagefilledrectangle($this->gd,0,0,$this->width-1,$this->height-1,
            $color);
    }

    public function flip()
    {
        imageflip($this->gd,IMG_FLIP_VERTICAL);
    }

    public function load_image($fname)
    {
        $image = new Image($this->gd);
        $image->load($fname);
        return $image;
    }

    protected function outputFile()
    {
        $filename = sys_get_temp_dir().'/rindow/rlgym';
        @mkdir($filename,true);
        $filename = tempnam($filename,'plo');
        rename($filename, $filename.'.gif');
        $filename = $filename.'.gif';
        return $filename;
    }

    public function output()
    {
        $fname = $this->outputFile();
        imagegif($this->gd,$fname);
        $this->outputFiles[] = $fname;
        return $fname;
    }

    public function get_image_data()
    {
        if(!class_exists('Rindow\\Math\\Matrix\\NDArrayPhp')) {
            throw new LogicException('Requires rindow-math-matrix package.');
        }
        $la = $this->la;
        ob_start();
        imagebmp($this->gd);
        $bmp = ob_get_contents();
        ob_end_clean();
        $header1 = unpack("c2type/Vsize/v2rsv/Voffbits",$bmp,0);
        $header2 = unpack("Vbisize/Vwidth/Vheight/vplanes/vbitcount/".
                        "Vcomp/Vsizeimage/Vxpixpm/Vypixpm/".
                        "Vclrused/Vcirimp",$bmp,14);
        if($header2['comp']!=0) {
            throw new RuntimeException('bitmap format must be uncompressed.');
        }
        if($header2['bitcount']==24) {
            $channels = 3;
        } elseif($header2['bitcount']==32) {
            $channels = 4;
        } else {
            throw new RuntimeException('bitmap format must be 24 bit or 32 bit.');
        }
        $width = $header2['width'];
        $height = $header2['height'];
        $pxdata = '';
        $pos = $header1['offbits'];
        $linesize = $width*$channels;
        $boundary = ($linesize&0x03)?(4-($linesize&0x03)):0;
        for($y=0;$y<$height;$y++) {
            $pxdata .= substr($bmp,$pos,$linesize);
            $pos += ($linesize+$boundary);
        }

        $la = $this->la;
        //$img = $la->alloc([$height,$width,$channels],NDArray::uint8);
        $img = new NDArrayPhp(null,NDArray::uint8,[$height,$width,$channels]);
        $buffer = $img->buffer();
        if(method_exists($buffer,'load')) {
            // OpenBlasBuffer
            $buffer->load($pxdata);
        } else {
            // SplFixedArray
            $idx=0;
            foreach(unpack('C*',$pxdata) as $value) {
                $buffer[$idx] = $value;
                $idx++;
            }
        }
        $img = $la->array($img);
        $img = $la->imagecopy($img,null,null,null,null,null,null,$rgbFlip=true);
        return $img;
    }

    public function show(bool $loop=null,int $delay=null)
    {
        if(count($this->outputFiles)==0) {
            throw new LogicException('Image not found');
        } elseif(count($this->outputFiles)==1) {
            $filename = array_shift($this->outputFiles);
            $this->outputFiles = [];
            $this->executeGifViewer($filename);
            return;
        }
        if($loop===null) {
            $loop=true;
        }
        if($delay===null) {
            $delay=5;
        }
        $filename = $this->outputFile();
        if(!($stream=fopen($filename,'wb'))) {
            throw new RuntimeException('animation file open error:'.$filename);
        };
        $zeros=array_fill(0,count($this->outputFiles),0);
        $delay=array_fill(0,count($this->outputFiles),$delay);
        $gifmerge = new GifMerge($this->outputFiles,
            $trans1=-1,$trans2=-1,$trans3=-1,$loop=1,
            $dl=$delay, $xpos=$zeros, $ypos=$zeros,
            $model='C_FILE', $stream, $debug=false
        );
        fclose($stream);
        //file_put_contents($filename,$gifmerge->getAnimation());
        foreach ($this->outputFiles as $fname) {
            @unlink($fname);
        }
        $this->outputFiles = [];
        $this->executeGifViewer($filename);
    }

    protected function executeGifViewer($filename)
    {
        if($viewer = getenv($this->gifViewer)) {
            $filename = '"'.$viewer.'" '.$filename;
        }
        if(!$this->skipRunViewer) {
            system($filename);
        }
    }

    public function handler()
    {
        return $this->gd;
    }

    public function close()
    {
        imagedestroy($this->gd);
    }
}
