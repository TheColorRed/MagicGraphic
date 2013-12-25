<?php

class MagicGraphic{

    protected $stage;
    protected $layers        = [];
    protected $width         = null, $height        = null;
    protected $autosize      = false;
    protected $stageSettings = [
        "crop" => []
    ];

    const JPG = "jpg";
    const GIF = "gif";
    const PNG = "png";

    /**
     * Sets up the stage size. If the width or height of the stage isn't given, 
     * auto sizing then becomes enabled; this means that your stage is set based
     * on the size of the largest layer (including offsets).
     * @param integer $width
     * @param integer $height
     */
    public function __construct($width = null, $height = null){
        $this->width  = $width;
        $this->height = $height;
        if($width === null || $height === null){
            $this->autosize = true;
        }
    }

    /**
     * Creates a Layer
     * @param string $name
     * @return Layer
     */
    public function createLayer($name){
        $layer          = new Layer();
        $li             = new LayerInfo();
        $li->depth      = count($this->layers);
        $li->name       = $name;
        $li->resource   = $layer;
        $this->layers[] = $li;
        return $layer;
    }

    /**
     * Duplicates an existing layer.
     * @param string $name
     * @param resource $resource
     * @return \Layer
     */
    public function duplicateLayer($name, $resource){
        $layer          = new Layer();
        $layer->loadFromResource($resource);
        $li             = new LayerInfo();
        $li->depth      = count($this->layers);
        $li->name       = $name;
        $li->resource   = $layer;
        $this->layers[] = $li;
        return $layer;
    }

    public function crop($width, $height, $x = 0, $y = 0){
        $this->stageSettings["crop"]["width"]  = $width;
        $this->stageSettings["crop"]["height"] = $height;
        $this->stageSettings["crop"]["x"]      = $x;
        $this->stageSettings["crop"]["y"]      = $y;
    }

    /**
     * This will generate an image to be displayed within a webpage. The image
     * does not get saved, use self::save() to save an image to disk.
     * @param string $type
     */
    public function display($quality = 100, $type = MagicGraphic::JPG){
        $this->createStage();
        $this->createGraphic();
        $this->_genImageType($type, null, (int)$quality);
    }

    public function save($filename, $quality = 100, $type = MagicGraphic::JPG){
        $this->createStage();
        $this->createGraphic();
        $this->_genImageType($type, $filename, (int)$quality);
    }

    /**
     * Recalculates the image's size
     */
    public function recalcSize(){
        $this->width  = imagesx($this->graphic);
        $this->height = imagesy($this->graphic);
    }
    
    protected function _genImageType($type, $filename, $quality){
        switch($type){
            case MagicGraphic::JPG:
                imagejpeg($this->stage, $filename, $quality);
                break;
            case MagicGraphic::GIF:
                imagegif($this->stage, $filename);
                break;
            case MagicGraphic::PNG:
                $quality = round(($quality / 100) * 9);
                imagepng($this->stage, $filename, $quality);
                break;
            default:
                imagejpeg($this->stage, $filename, $quality);
                break;
        }
    }

    /**
     * This creates the final image that will be rendered to the screen or to a
     * file.
     */
    protected function createGraphic(){
        /**
         * @var $layer Layer
         * @var $li LayerInfo
         */
        foreach($this->layers as $li){
            $layer   = $li->resource;
            $graphic = $layer->getData();
            $width   = $layer->getWidth();
            $height  = $layer->getHeight();
            $x       = $layer->getX();
            $y       = $layer->getY();
            imagecopy($this->stage, $graphic, $x, $y, 0, 0, $width, $height);
        }
        $this->_stageFinalize();
    }

    /**
     * Creates the stage where all the layers will be drawn to. If self::autosize 
     * is enabled, the stage will be set to size of the largest layer this 
     * includs the layer's x/y offsets.
     */
    protected function createStage(){
        $width  = (int)$this->width;
        $height = (int)$this->height;
        if($this->autosize){
            /**
             * @var $layer Layer
             * @var $li LayerInfo
             */
            foreach($this->layers as $li){
                $layer = $li->resource;
                $w     = $layer->getWidth();
                $h     = $layer->getHeight();
                $x     = $layer->getX();
                $y     = $layer->getY();
                if($w + $x > $width){
                    $width = $w + $x;
                }
                if($h + $y > $height){
                    $height = $h + $y;
                }
            }
        }
        $this->stage = $this->_createTransBg($width, $height);
    }

    protected function _stageFinalize(){
        $this->_crop();
    }

    protected function _createTransBg($width, $height){
        $im          = imagecreatetruecolor($width, $height);
        imagesavealpha($im, true);
        $trans_color = imagecolorallocatealpha($im, 0, 0, 0, 127);
        imagefill($im, 0, 0, $trans_color);
        return $im;
    }

    protected function _crop(){
        if(empty($this->stageSettings["crop"])){
            return;
        }
        $width  = (int)$this->stageSettings["crop"]["width"];
        $height = (int)$this->stageSettings["crop"]["height"];
        $x      = (int)$this->stageSettings["crop"]["x"];
        $y      = (int)$this->stageSettings["crop"]["y"];
        $im     = $this->_createTransBg($width, $height);
        imagecopy($im, $this->stage, 0, 0, $x, $y, $width, $height);
        $this->stage = $im;
        $this->recalcSize();
    }

}

class LayerInfo{

    public $resource;
    public $depth;
    public $name;

}

spl_autoload_register(function($class){
    require_once __DIR__ . "/lib/$class.php";
});
