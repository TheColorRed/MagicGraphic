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
            $im     = imagecreatefromstring($image);
            $width  = imagesx($im);
            $height = imagesy($im);
        }elseif(is_resource($image)){
            $width  = imagesx($image);
            $height = imagesy($image);
            $im = $this->_createFromSoruce($image, $width, $height);
        }
        $this->width  = $width;
        $this->height = $height;
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
        $origw        = $this->width;
        $origh        = $this->height;
        $this->width  = (int)$width;
        $this->height = (int)$height;
        $this->_resize($origw, $origh, $this->width, $this->height);
    }

    /**
     * Auto resize image based on width
     * @param integer $width
     */
    public function autoResizeWidth($width){
        $origw        = $this->width;
        $origh        = $this->height;
        $this->width  = (int)$width;
        $this->height = (int)round($origh * ($width / $origw));
        $this->_resize($origw, $origh, $this->width, $this->height);
    }

    /**
     * Auto resize image based on height
     * @param integer $height
     */
    public function autoResizeHeight($height){
        $origw        = $this->width;
        $origh        = $this->height;
        $this->width  = (int)round($origw * ($height / $origh));
        $this->height = (int)$height;
        $this->_resize($origw, $origh, $this->width, $this->height);
    }

    /**
     * Physically resize the image resource
     * @param integer $origw
     * @param integer $origh
     * @param integer $neww
     * @param integer $newh
     */
    protected function _resize($origw, $origh, $neww, $newh){
        $im            = imagecreatetruecolor($neww, $newh);
        imagesavealpha($im, true);
        $trans_color   = imagecolorallocatealpha($im, 0, 0, 0, 127);
        imagefill($im, 0, 0, $trans_color);
        imagecopyresampled($im, $this->graphic, 0, 0, 0, 0, $neww, $newh, $origw, $origh);
        $this->graphic = $im;
        return $im;
    }

    protected function _createFromSoruce($image, $width, $height){
        $im = imagecreatetruecolor($width, $height);
        imagesavealpha($im, true);

        $trans_color = imagecolorallocatealpha($im, 0, 0, 0, 127);
        imagefill($im, 0, 0, $trans_color);
        imagecopyresampled($im, $image, 0, 0, 0, 0, $width, $height, $width, $height);

        $this->graphic = $im;
        return $im;
    }

}
