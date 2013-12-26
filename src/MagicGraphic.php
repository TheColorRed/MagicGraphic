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

    public function anchorLayer(Layer $layer, $anchor){
        $layer->setAnchor($anchor);
    }

    /**
     * Duplicates an existing layer.
     * @param string $name
     * @param resource $resource
     * @return \Layer
     */
    public function duplicateLayer(Layer $layer, $name){
        $layer          = new Layer();
        $layer->loadFromResource($layer->getData());
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
        $this->_createStage();
        $this->_createGraphic();
        $this->_genImageType($type, null, (int)$quality);
    }

    public function save($filename, $quality = 100, $type = MagicGraphic::JPG){
        $this->_createStage();
        $this->_createGraphic();
        $this->_genImageType($type, $filename, (int)$quality);
    }

    /**
     * Recalculates the image's size
     */
    public function recalcSize(){
        $this->width  = imagesx($this->stage);
        $this->height = imagesy($this->stage);
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
    protected function _createGraphic(){
        /**
         * @var $layer Layer
         * @var $li LayerInfo
         */
        $this->_anchor();
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
    protected function _createStage(){
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
        $this->recalcSize();
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

    protected function _createBg($width, $height, $color = 0x000000){
        $im = imagecreatetruecolor($width, $height);
        imagesavealpha($im, true);
        imagefill($im, 0, 0, $color);
        return $im;
    }

    protected function _crop(){
        if(empty($this->stageSettings["crop"])){
            return;
        }
        $width       = (int)$this->stageSettings["crop"]["width"];
        $height      = (int)$this->stageSettings["crop"]["height"];
        $x           = (int)$this->stageSettings["crop"]["x"];
        $y           = (int)$this->stageSettings["crop"]["y"];
        $im          = $this->_createTransBg($width, $height);
        imagecopy($im, $this->stage, 0, 0, $x, $y, $width, $height);
        $this->stage = $im;
        $this->recalcSize();
    }

    protected function _anchor(){
        foreach($this->layers as $li){
            /* @var $layer Layer */
            $layer  = $li->resource;
            $anchor = $layer->getAnchor();
            if($anchor === null){
                continue;
            }
            $width       = $layer->getWidth();
            $height      = $layer->getHeight();
            $stageWdith  = $this->width;
            $stageHeight = $this->height;
            switch($anchor){
                case Layer::AnchorTopLeft:
                    $layer->setOffset(0, 0);
                    break;
                case Layer::AnchorTopMid:
                    $layer->setOffset(($stageWdith - $width) / 2, 0);
                    break;
                case Layer::AnchorTopRight:
                    $layer->setOffset($stageWdith - $width, 0);
                    break;
                case Layer::AnchorMidLeft:
                    $layer->setOffset(0, ($stageHeight - $height) / 2);
                    break;
                case Layer::AnchorCenter:
                    $layer->setOffset(($stageWdith - $width) / 2, ($stageHeight - $height) / 2);
                    break;
                case Layer::AnchorMidRight:
                    $layer->setOffset($stageWdith - $width, ($stageHeight - $height) / 2);
                    break;
                case Layer::AnchorBottomLeft:
                    $layer->setOffset(0, $stageHeight - $height);
                    break;
                case Layer::AnchorBottomMid:
                    $layer->setOffset(($stageWdith - $width) / 2, $stageHeight - $height);
                    break;
                case Layer::AnchorBottomRight:
                    $layer->setOffset($stageWdith - $width, $stageHeight - $height);
                    break;
            }
        }
    }

}

class LayerInfo{

    public $resource;
    public $depth;
    public $name;

}

class Color{

    const AQUA        = 0x00FFFF;
    const BEIGE       = 0xF5F5DC;
    const BLACK       = 0x000000;
    const BLUE        = 0x0000FF;
    const CYAN        = 0x00FFFF;
    const DARKCYAN    = 0x008B8B;
    const DARKGRAY    = 0xA9A9A9;
    const DARKGREEN   = 0x006400;
    const DEEPPINNK   = 0xFF1493;
    const DIMGRAY     = 0x696969;
    const DODGERBLUE  = 0x1E90FF;
    const FORESTGREEN = 0x228B22;
    const FUCHSIA     = 0xFF00FF;
    const GOLD        = 0xFFD700;
    const GRAY        = 0x808080;
    const GREEN       = 0x008000;
    const HOTPINK     = 0xFF69B4;
    const INDIGO      = 0x4B0082;
    const IVORY       = 0xFFFFF0;
    const LIGHTGRAY   = 0xD3D3D3;
    const LIGHTGREEN  = 0x90EE90;
    const LIME        = 0x00FF00;
    const LIMEGREEN   = 0x32CD32;
    const MAROON      = 0x800000;
    const NAVY        = 0x000080;
    const OLIVE       = 0x808000;
    const ORANGE      = 0xFFA500;
    const PINK        = 0xFFC0CB;
    const PURPLE      = 0x800080;
    const RED         = 0xFF0000;
    const ROYALBLUE   = 0x4169E1;
    const SEASHELL    = 0xFFF5EE;
    const SKYBLUE     = 0x87CEEB;
    const SILVER      = 0xC0C0C0;
    const STEELBLUE   = 0x4682B4;
    const TEAL        = 0x008080;
    const TURQUOISE   = 0x40E0D0;
    const WHITE       = 0xFFFFFF;
    const YELLOW      = 0xFFFF00;
    const VIOLET      = 0xEE82EE;

    public static function getColor($r, $g, $b){
        $hex = str_pad(dechex($r), 2, "0", STR_PAD_LEFT);
        $hex .= str_pad(dechex($g), 2, "0", STR_PAD_LEFT);
        $hex .= str_pad(dechex($b), 2, "0", STR_PAD_LEFT);
        return hexdec($hex);
    }

}

spl_autoload_register(function($class){
    require_once __DIR__ . "/lib/$class.php";
});
