<?php
namespace Rindow\RL\Gym\Core\Graphics;

use LogicException;
use RuntimeException;
use Interop\Polite\Math\Matrix\NDArray;
use Rindow\Math\Matrix\NDArrayPhp;

class GDGL implements GL
{
    const DEG2RAD = 0.017453292519943;

    protected $la;
    protected $currentMatrix;
    protected $viewMatrix;
    protected $stackMatrix = [];
    protected $gd;
    protected $color;
    protected $clearColor;
    protected $fgRealColor;
    protected $bgRealColor;
    protected $currentLineStippleFactor;
    protected $currentLineStipplePattern;

    protected $mode;
    protected $prevRealPoint;
    protected $firstRealPoint;
    protected $cap = [];
    protected $outputFiles = [];
    protected $gifViewer = 'RINDOW_MATH_PLOT_VIEWER';
    protected $blendSrcFactor;
    protected $blendDstFactor;
    protected $imagesDir;
    protected $mkdir;
    protected $skipCleaning = false;
    protected $skipRunViewer = false;

    public function __construct($la,$config=null)
    {
        $this->imagesDir = sys_get_temp_dir().'/rindow/rlgym';
        $this->setConfig($config);
        if(!$this->skipCleaning) {
            $this->cleanUp();
        }

        $this->la = $la;
        $this->currentMatrix = $la->array([
            [1,0,0,0],
            [0,1,0,0],
            [0,0,1,0],
            [0,0,0,1],
        ]);
    }

    protected function setConfig($config) : void
    {
        //var_dump($config);
        if(isset($config['render.skipCleaning']) && $config['render.skipCleaning']) {
            $this->setSkipCleaningUp(true);
        }
        if(isset($config['render.skipRunViewer']) && $config['render.skipRunViewer']) {
            $this->setSkipRunViewer(true);
        }
        
    }

    public function setSkipCleaningUp(bool $switch) : void
    {
        $this->skipCleaning = $switch;
    }

    public function setSkipRunViewer(bool $switch) : void
    {
        $this->skipRunViewer = $switch;
    }

    public function glViewport(int $orginX, int $orginY, int $width, int $height)
    {
        $this->viewMatrix = $this->la->array([
            [$width/2, 0,          0, $width/2+$orginX   ],
            [0,        -$height/2, 0, $this->height-$height/2-$orginY-1],
            [0,        0,          1, 0                  ],
            [0,        0,          0, 1                  ],
        ]);
        //$la = $this->la;
        //$rightedge = ($this->width/2-1)/($this->width/2);
        //$topedge = ($this->height/2-1)/($this->height/2);
        //$bottom = $la->gemv($this->viewMatrix,$la->array([-1,-1,0,0]));
        //$top = $la->gemv($this->viewMatrix,$la->array([$rightedge,$topedge,0,0]));
        //$bottom = $la->astype($bottom,NDArray::int32);
        //$top = $la->astype($top,NDArray::int32);
        //echo "bottomview=".implode(',',$bottom->toArray())."\n";
        //echo "topview=".implode(',',$top->toArray())."\n";
        $clipx1 = $orginX;
        $clipy1 = $this->height-$orginY-$height;
        $clipx2 = $orginX+$width-1;
        $clipy2 = $this->height-1-$orginY;
        imagesetclip($this->gd,$clipx1,$clipy1,$clipx2,$clipy2);
    }
    
    protected function realCoordinate(array $point, bool $realMode=null)
    {
        $la = $this->la;
        array_push($point,1);
        $p = $la->array($point);
        $p = $la->gemv($this->currentMatrix,$p);
        if($this->viewMatrix) {
            $p = $la->gemv($this->viewMatrix,$p);
        }
        if(!$realMode) {
            $p = $la->astype($p,NDArray::int32);
        }
        //$p[1] = $this->height - $p[1] - 1;
        return $p;
    }

    public function glClear(int $mask) : void
    {
        if($mask|GL::GL_COLOR_BUFFER_BIT) {
            $c = $this->clearColor;
            $color = imagecolorallocate($this->gd,$c[0],$c[1],$c[2]);
            imagefilledrectangle($this->gd,0,0,$this->width-1,$this->height-1,
                $color);
        }
    }

    /**
    * GL_QUADS
    * GL_POLYGON
    * GL_TRIANGLES
    * GL_LINES
    * GL_POINTS
    * GL_LINE_STRIP
    * GL_LINE_LOOP
    **/
    public function glBegin(int $mode) : void
    {
        $this->mode = $mode;
        $this->points = [];
        $this->prevRealPoint = null;
        $this->firstRealPoint = null;
}

    public function glEnd() : void
    {
        $this->dispatchPolygon(true);
        $this->mode = null;
        $this->points = [];
        $this->prevRealPoint = null;
        $this->firstRealPoint = null;
}

    public function glVertex2f(float $x, float $y) : void
    {
        $this->points[] = [$x, $y, 0];
        $this->dispatchPolygon(false);
    }

    public function glVertex3f(float $x, float $y, float $z) : void
    {
        $this->points[] = [$x, $y, $z];
        $this->dispatchPolygon(false);
    }

    protected function dispatchPolygon(bool $done)
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
                $this->renderLineStrip($this->mode,$done);
                break;
            }
            case GL::GL_TRIANGLES: {
                $this->renderPolygon(3,$done);
                break;
            }
            case GL::GL_QUADS: {
                $this->renderPolygon(4,$done);
                break;
            }
            case GL::GL_POLYGON: {
                $this->renderPolygon(null,$done);
                break;
            }
            default: {
                throw new LogicException('unknown primitive mode');
            }
        }
    }

    protected function currentRealColor()
    {
        if(isset($this->cap[GL::GL_LINE_STIPPLE])) {
            return IMG_COLOR_STYLED;
        }
        return $this->fgRealColor;
    }

    protected function renderPoint()
    {
        if(count($this->points)<1) {
            return;
        }
        $color = $this->currentRealColor();
        $point = array_shift($this->points);
        $point = $this->realCoordinate($point);
        imagesetpixel($this->gd,$point[0],$point[1],$color);
    }

    protected function renderLine()
    {
        if(count($this->points)<2) {
            return;
        }
        $color = $this->currentRealColor();
        $start = array_shift($this->points);
        $end   = array_shift($this->points);
        $start = $this->realCoordinate($start);
        $end   = $this->realCoordinate($end);
        imageline($this->gd,$start[0],$start[1],$end[0],$end[1],$color);
    }

    protected function renderLineStrip($mode, bool $done)
    {
        if($done) {
            if($mode!=GL::GL_LINE_LOOP) {
                return;
            }
            $start = $this->prevRealPoint;
            $end = $this->firstRealPoint;
            $this->prevRealPoint = null;
            $this->firstRealPoint = null;
        } else {
            if($this->prevRealPoint===null) {
                $start = array_shift($this->points);
                $this->prevRealPoint = $this->realCoordinate($start);
                $this->firstRealPoint = $this->prevRealPoint;
                return;
            }
            $start = $this->prevRealPoint;
            $end = array_shift($this->points);
            $end = $this->realCoordinate($end);
            $this->prevRealPoint = $end;
        }
        $color = $this->currentRealColor();
        imageline($this->gd,$start[0],$start[1],$end[0],$end[1],$color);
    }

    protected function renderPolygon($numVertex, bool $done)
    {
        if($done) {
            if($numVertex!==null) {
                return;
            }
            $numVertex=count($this->points);
        } else {
            if($numVertex===null) {
                return;
            }
            if(count($this->points)<$numVertex) {
                return;
            }
        }
        $color = $this->currentRealColor();
        $points = [];
        $numPoints = 0;
        while($point = array_shift($this->points)) {
            $point = $this->realCoordinate($point);
            $points[] = $point[0];
            $points[] = $point[1];
            $numPoints++;
        }
        $php81 = (version_compare(phpversion(),'8.1.0')>=0);
        if($php81) {
            imagefilledpolygon($this->gd,$points,$color);
        } else {
            imagefilledpolygon($this->gd,$points,$numPoints,$color);
        }
    }

    /**
    * GL_LINE_STIPPLE
    * GL_BLEND
    */
    public function glEnable(int $cap) : void
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
    * GL_BLEND
    */
    public function glDisable(int $cap) : void
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
    public function glBlendFunc(int $sfactor, int $dfactor) : void
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

    public function glColor4f(float $red, float $green, float $blue, float $alpha) : void
    {
        $this->color = $this->transColor($red, $green, $blue, $alpha);
        $this->fgRealColor = imagecolorallocatealpha($this->gd,...$this->color);
        if(isset($this->cap[GL::GL_LINE_STIPPLE])) {
            $this->glLineStipple($this->currentLineStippleFactor,$this->currentLineStipplePattern);
        }
    }

    public function glClearColor(float $red, float $green, float $blue, float $alpha) : void
    {
        $this->clearColor = $this->transColor($red, $green, $blue, $alpha);
        $this->bgRealColor = imagecolorallocatealpha($this->gd,...$this->clearColor);
    }

    public function glLineStipple(int $factor, int $pattern) : void
    {
        $this->currentLineStippleFactor = $factor;
        $this->currentLineStipplePattern = $pattern;
        $style = [];
        for($b=0;$b<16;$b++) {
            for($n=0;$n<$factor;$n++) {
                $style[] = ($pattern&0x0001)?$this->fgRealColor:$this->bgRealColor;
            }
            $pattern = $pattern >> 1;
        }
        imagesetstyle($this->gd,$style);
    }

    public function glLineWidth(float $width) : void
    {
        $width = (int)ceil($width);
        imagesetthickness($this->gd,$width);
    }

    public function glPushMatrix() : void
    {
        array_push($this->stackMatrix,$this->currentMatrix);
    }

    public function glPopMatrix() : void
    {
        if(count($this->stackMatrix)==0) {
            throw new LogicException('Matrix Stack is empty');
        }
        $this->currentMatrix = array_pop($this->stackMatrix);
    }

    public function glTranslatef(float $x, float $y, float $z) : void
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

    public function glRotatef(float $angle, float $x, float $y, float $z) : void
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

    public function glScalef(float $x,float $y,float $z) : void
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

    protected function remainder(float $x, float $y) : float
    {
        $v = floor($x / $y);
        $r = $x - $v*$y;
        $r = ($y>0) ? abs($r) : -abs($r);
        return $r;
    }

    public function renderImage(Image $image, float $centerx, float $centery, float $width, float $height) : void
    {
        $src_x = $centerx-$width/2;
        $src_y = $centery-$height/2;

        $p0 = $this->realCoordinate([-1, -1, 0],realMode:true);
        $p1 = $this->realCoordinate([ 1, -1, 0],realMode:true);
        $p2 = $this->realCoordinate([ 1,  1, 0],realMode:true);
        $p3 = $this->realCoordinate([-1,  1, 0],realMode:true);
        $x = min($p0[0],$p1[0],$p2[0],$p3[0]);
        $y = min($p0[1],$p1[1],$p2[1],$p3[1]);
        $dst_width = max($p0[0],$p1[0],$p2[0],$p3[0])-$x;
        $dst_height = max($p0[1],$p1[1],$p2[1],$p3[1])-$y;

        $dst_diagonal0 = $dst_height**2 - ($p3[1]-$p1[1])**2;
        $dst_diagonal1 = $dst_width**2 -($p2[0]-$p0[0])**2;
        if($dst_diagonal0==0) {
            $dst_diagonal0 = $dst_height**2 - ($p2[1]-$p0[1])**2;
            $dst_diagonal1 = $dst_width**2 -($p3[0]-$p1[0])**2;
        }
        if($dst_diagonal0==0) {
            $oblateness = 1.0;
        } else {
            $oblateness = sqrt($dst_diagonal1/$dst_diagonal0);
        }

        $exp_width  = sqrt(($p1[0]-$p0[0])**2 + (($p1[1]-$p0[1])*$oblateness)**2);
        $exp_height = sqrt(($p2[0]-$p1[0])**2 + (($p2[1]-$p1[1])*$oblateness)**2);
        $rotdeg = atan2(($p0[1]-$p1[1])*$oblateness,$p1[0]-$p0[0])/self::DEG2RAD;
        $v_rotdeg = atan2(($p1[1]-$p2[1])*$oblateness,$p2[0]-$p1[0])/self::DEG2RAD;
        $flip = (($this->remainder(($v_rotdeg+360)-($rotdeg+360)+180,360)-180) < 0);

        // expanding original image
        $img = $image->img();
        $expandedImg = imagecreatetruecolor((int)$exp_width,(int)($exp_height));
        imagealphablending($expandedImg,false);
        imagecopyresampled(
            $expandedImg,                       /// dst img
            $img,                               /// src img
            0,0,                                /// dst pos
            (int)$src_x,(int)$src_y,            /// src pos
            (int)$exp_width,(int)$exp_height,   /// dst width
            $width,$height                      /// src width
        );
        if($flip) {
            imageflip($expandedImg,IMG_FLIP_VERTICAL);
        }

        // rotate original image
        $backcolor = imagecolorallocatealpha($img,0,0,0,127);
        $img = imagerotate($expandedImg,$rotdeg,$backcolor);
        imagedestroy($expandedImg);
        unset($expandedImg);
        imagealphablending($img,false);

        // mapping rotated image
        $imgsx = imagesx($img);
        $imgsy = imagesy($img);
        if($imgsx==(int)$dst_width && $imgsy==(int)$dst_height) {
            imagecopy(
                $this->gd,$img,
                (int)$x,(int)$y,
                0,0,
                $imgsx,$imgsy
            );
        } else {
            imagecopyresampled(
                $this->gd,$img,
                (int)$x,(int)$y,
                0,0,
                (int)$dst_width,(int)$dst_height,
                $imgsx,$imgsy
            );
        }
        imagedestroy($img);
    }

    public function get_display($display)
    {
        return null;
    }

    public function createWindow($width, $height, $display)
    {
        $this->gd = imagecreatetruecolor($width, $height);
        $this->width = $width;
        $this->height = $height;
        $this->glViewport(0, 0, $width, $height);
        return new Window($this, $width, $height, $display);
    }

    public function clear() : void
    {
        $this->glClear(GL::GL_COLOR_BUFFER_BIT|GL::GL_DEPTH_BUFFER_BIT);
    }

    public function flip() : void
    {
        imageflip($this->gd,IMG_FLIP_VERTICAL);
    }

    public function load_image($fname) : Image
    {
        $image = new Image($this->gd);
        $image->load($fname);
        return $image;
    }

    public function output() : string
    {
        $fname = $this->outputFile();
        imagegif($this->gd,$fname);
        $this->outputFiles[] = $fname;
        return $fname;
    }

    public function get_image_data() : NDArray
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

    public function show(bool $loop=null,int $delay=null) : void
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

    protected function executeGifViewer($filename) : void
    {
        if($this->skipRunViewer) {
            return;
        }
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

    public function close() : void
    {
        imagedestroy($this->gd);
    }

    public function currentMatrix() : NDArray
    {
        return $this->currentMatrix;
    }

    public function cleanUp() : void
    {
        $this->deleteTempfiles('plo');
    }

    protected function outputFile() : string
    {
        $imagedir = sys_get_temp_dir().'/rindow/rlgym';
        $this->makeDirectory();
        $filename = tempnam($this->imagesDir,'plo');
        rename($filename, $filename.'.gif');
        $filename = $filename.'.gif';
        return $filename;
    }

    protected function makeDirectory() : void
    {
        if($this->mkdir) {
            return;
        }
        if(!file_exists($this->imagesDir)) {
            @mkdir($this->imagesDir,0777,true);
        }
        $this->mkdir = true;
    }

    protected function deleteTempfiles(string $prefix) : void
    {
        $this->makeDirectory();
        if(($d=opendir($this->imagesDir))==false) {
            return;
        }

        $pattern = '/^'.$prefix.'.*\\.gif$/';
        while ($filename=readdir($d)) {
            if(is_file($this->imagesDir.'/'.$filename) &&
                    preg_match($pattern,$filename)) {
                unlink($this->imagesDir.'/'.$filename);
            }
        }
        closedir($d);
    }
}
