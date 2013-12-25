<?php

class Image{

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
        $im           = imagecreatefromstring($image);
        $this->width  = imagesx($im);
        $this->height = imagesy($im);
        return $im;
    }

}
