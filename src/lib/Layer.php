<?php

class Layer{

    protected $graphic;
    protected $x      = 0, $y      = 0;
    protected $width  = 0, $height = 0;
    protected $alpha  = 1;
    protected $anchor = null;

    // Layer Anchors
    const AnchorTopLeft     = "AnchorTopLeft";
    const AnchorTopMid      = "AnchorTopMid";
    const AnchorTopRight    = "AnchorTopRight";
    const AnchorMidLeft     = "AnchorMidLeft";
    const AnchorCenter      = "AnchorCenter";
    const AnchorMidRight    = "AnchorMidRight";
    const AnchorBottomLeft  = "AnchorBottomLeft";
    const AnchorBottomMid   = "AnchorBottomMid";
    const AnchorBottomRight = "AnchorBottomRight";

    /**
     * Loads a file
     * @param string $filename
     */
    public function loadFromFile($filename){
        $image = file_get_contents($filename);
        $this->_createGraphic($image);
    }

    /**
     * This loads an image from an existing resource.
     * @param resource $resource
     */
    public function loadFromResource($resource){
        $this->_createGraphic($resource);
    }

    public function loadColor($width, $height, $color){
        $im            = $this->_createBg($width, $height, $color);
        $this->graphic = $im;
        $this->recalcSize();
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
    protected function _createGraphic($image){
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

    public function setAlpha($amount){
        $this->alpha = $amount;
    }

    public function setAnchor($anchor = Layer::AnchorTopLeft){
        $this->anchor = $anchor;
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

    public function getAlpha(){
        return $this->alpha;
    }

    public function getAnchor(){
        return $this->anchor;
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

    /**
     * Rotates image layer
     * @param int $amount
     */
    public function rotate($amount){
        $amount        = $amount * -1;
        imagesavealpha($this->graphic, true);
        $trans_color   = imagecolorallocatealpha($this->graphic, 0, 0, 0, 127);
        $this->graphic = imagerotate($this->graphic, $amount, $trans_color);
        $this->recalcSize();
    }

    public function crop($width, $height, $x = 0, $y = 0){
        $im            = $this->_createBg($width, $height, null, true);
        imagecopy($im, $this->graphic, 0, 0, $x, $y, $width, $height);
        $this->graphic = $im;
        $this->recalcSize();
        return $im;
    }

    /**
     * Recalculates the image's size
     */
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
        $im = $this->_createBg($neww, $newh, null, true);
        imagecopyresampled($im, $this->graphic, 0, 0, 0, 0, $neww, $newh, $origw, $origh);

        $this->graphic = $im;
        $this->recalcSize();
        return $im;
    }

    protected function _alpha(){
        
    }

    /**
     * Physically creates a new image resource for the layer.
     * @param resource $image
     * @param integer $width
     * @param integer $height
     * @return resourece
     */
    protected function _createFromSoruce($image, $width, $height){
        $im = $this->_createBg($width, $height, null, true);
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
        $im            = imagecreatefromstring($string);
        $this->graphic = $im;
        $this->recalcSize();
        return $im;
    }

    protected function _createBg($width, $height, $color = null, $trans = false){
        $im = imagecreatetruecolor($width, $height);
        imagesavealpha($im, true);
        if($trans){
            $color = imagecolorallocatealpha($im, 0, 0, 0, 127);
        }
        imagefill($im, 0, 0, $color);
        return $im;
    }

    /*
      protected function _createBg($width, $height, $color = 0x000000){
      $im = imagecreatetruecolor($width, $height);
      imagesavealpha($im, true);
      imagefill($im, 0, 0, $color);
      return $im;
      }
     */
}
