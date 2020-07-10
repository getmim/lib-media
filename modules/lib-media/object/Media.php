<?php
/**
 * Media object format
 * @package lib-media
 * @version 0.0.1
 */

namespace LibMedia\Object;

class Media implements \JsonSerializable
{
    private $target;
    private $target_webp;
    private $file;
    private $width;
    private $height;

    public function __construct($file, $width=null, $height=null){
        $this->file   = $file;
        $this->height = $height;
        $this->width  = $width;

        $handlers = \Mim::$app->config->libMedia->handlers;
        $hdl_opts = (object)[
            'file' => $file
        ];

        if($width || $height)
            $hdl_opts->size = (object)[];

        if($width)
            $hdl_opts->size->width = $width;
        if($height)
            $hdl_opts->size->height = $height;

        $result = null;
        foreach($handlers as $name => $class){
            $result = $class::get($hdl_opts);
            if($result)
                break;
        }

        if(!$result)
            return;

        $this->target = $this->target_webp = $result->none;
        if(isset($result->webp))
            $this->target_webp = $result->webp;

        if(isset($result->size)){
            if(isset($result->size->width))
                $this->width = $result->size->width;
            if(isset($result->size->height))
                $this->height = $result->size->height;
        }
    }

    public function __get($name){
        if($name === 'target')
            return $this->target ?? null;

        if($name === 'value')
            return $this->file;

        if($name === 'webp')
            return $this->target_webp;
        
        if(substr($name, 0, 1) === '_'){
            if(false === strstr($name, 'x'))
                $name.= 'x';
            $sizes = explode('x', substr($name,1));
            $width = $sizes[0] ? $sizes[0] : null;
            $height= $sizes[1] ? $sizes[1] : null;

            if($width === $this->width && $height === $this->height)
                return $this;

            return new Media($this->file, $width, $height);
        }
        
        return null;
    }

    public function __toString(){
        return $this->target ?? '';
    }

    public function jsonSerialize(){
        return $this->__toString();
    }
}