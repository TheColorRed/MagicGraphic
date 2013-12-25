<?php

class Layer{

    protected $graphic;
    protected $depth;
    protected $x      = 0, $y      = 0;
    protected $width  = 0, $height = 0;

    /**
     * Loads a file
     * @param string $filename
     */
    public function loadFromFile($filename){
        $image         = file_get_contents($filename);
        $this->graphic = $this->createGraphic($image);
    }

    /**
     * This loads an image from an existing resource.
     * @param resource $resource
     */
    public function loadFromResource($resource){
        $this->graphic = $this->createGraphic($resource);
    }

    /**
     * Gets the layers saved graphic
     * @return resource
     */
    public function getData(){
        return $this->graphic;
    }

    /**
     * Creates a image resource from a string
     * @param string $image
     * @return resource
     */
    protected function createGraphic($image){
        if(is_string($image)){
            $im = $this->_createFromString($image);
        }elseif(is_resource($image)){
            $width  = imagesx($image);
            $height = imagesy($image);
            $im     = $this->_createFromSoruce($image, $width, $height);
        }
        return $im;
    }

    /**
     * Sets the X offset of the layer
     * @param integer $x
     */
    public function setX($x){
        $this->x = $x;
    }

    /**
     * Sets the Y offset of the layer
     * @param integer $y
     */
    public function setY($y){
        $this->y = $y;
    }

    public function setOffset($x, $y){
        $this->setX($x);
        $this->setY($y);
    }

    /**
     * Gets the X offset of the layer
     * @return integer
     */
    public function getX(){
        return (int)$this->x;
    }

    /**
     * Gets the Y offset of the layers
     * @return integer
     */
    public function getY(){
        return (int)$this->y;
    }

    /**
     * Gets the width of the image
     * @return integer
     */
    public function getWidth(){
        return $this->width;
    }

    /**
     * Gets the height of the image
     * @return integer
     */
    public function getHeight(){
        return $this->height;
    }

    /**
     * Resize an image
     * @param integer $width
     * @param integer $height
     */
    public function resize($width, $height){
        $origw = $this->width;
        $origh = $this->height;
        $this->_resize($origw, $origh, $width, $height);
    }

    /**
     * Auto resize image based on width
     * @param integer $width
     */
    public function autoResizeWidth($width){
        $origw  = $this->width;
        $origh  = $this->height;
        $width  = (int)$width;
        $height = (int)round($origh * ($width / $origw));
        $this->_resize($origw, $origh, $width, $height);
    }

    /**
     * Auto resize image based on height
     * @param integer $height
     */
    public function autoResizeHeight($height){
        $origw  = $this->width;
        $origh  = $this->height;
        $width  = (int)round($origw * ($height / $origh));
        $height = (int)$height;
        $this->_resize($origw, $origh, $width, $height);
    }

    public function rotate($amount){
        $amount        = $amount * -1;
        imagesavealpha($this->graphic, true);
        $trans_color   = imagecolorallocatealpha($this->graphic, 0, 0, 0, 127);
        $this->graphic = imagerotate($this->graphic, $amount, $trans_color);
        $this->recalcSize();
    }

    public function recalcSize(){
        $this->width  = imagesx($this->graphic);
        $this->height = imagesy($this->graphic);
    }

    /**
     * Physically resize the image resource
     * @param integer $origw
     * @param integer $origh
     * @param integer $neww
     * @param integer $newh
     * @return resourece
     */
    protected function _resize($origw, $origh, $neww, $newh){
        $im            = imagecreatetruecolor($neww, $newh);
        imagesavealpha($im, true);
        $trans_color   = imagecolorallocatealpha($im, 0, 0, 0, 127);
        imagefill($im, 0, 0, $trans_color);
        imagecopyresampled($im, $this->graphic, 0, 0, 0, 0, $neww, $newh, $origw, $origh);
        $this->graphic = $im;
        $this->recalcSize();
        return $im;
    }

    /**
     * Physically creates a new image resource for the layer.
     * @param resource $image
     * @param integer $width
     * @param integer $height
     * @return resourece
     */
    protected function _createFromSoruce($image, $width, $height){
        $im = imagecreatetruecolor($width, $height);
        imagesavealpha($im, true);

        $trans_color = imagecolorallocatealpha($im, 0, 0, 0, 127);
        imagefill($im, 0, 0, $trans_color);
        imagecopyresampled($im, $image, 0, 0, 0, 0, $width, $height, $width, $height);

        $this->graphic = $im;
        $this->recalcSize();
        return $im;
    }
    
    /**
     * Creates an image from a string such as file_get_contents()
     * @param string $string
     * @return resource
     */
    protected function _createFromString($string){
        $im = imagecreatefromstring($string);
        $this->graphic = $im;
        $this->recalcSize();
        return $im;
    }

}
