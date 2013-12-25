<?php

class MagicGraphic{

    protected $stage;
    protected $layers   = [];
    protected $width    = null, $height   = null;
    protected $autosize = false;

    const JPG = "jpg";
    const GIF = "gif";
    const PNG = "png";

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

    public function display($type = MagicGraphic::JPG){
        $this->createStage();
        $this->createGraphic();
        switch($type){
            case MagicGraphic::JPG:
                imagejpeg($this->stage, null, 100);
                break;
            case MagicGraphic::GIF:
                imagegif($this->stage);
                break;
            case MagicGraphic::PNG:
                imagepng($this->stage, null, 9);
                break;
            default:
                imagejpeg($this->stage, null, 100);
                break;
        }
    }

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
    }

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
        $this->stage = imagecreatetruecolor($width, $height);
        imagesavealpha($this->stage, true);
        $trans_color = imagecolorallocatealpha($this->stage, 0, 0, 0, 127);
        imagefill($this->stage, 0, 0, $trans_color);
    }

}

class LayerInfo{

    public $resource;
    public $depth;
    public $name;

}

spl_autoload_register(function($class){
    require_once __DIR__ . "/plugins/$class.php";
});
