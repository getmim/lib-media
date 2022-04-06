<?php
/**
 * Media object format
 * @package lib-media
 * @version 1.3.0
 */

namespace LibMedia\Object;

use \claviska\SimpleImage;
use LibMedia\Model\MediaSize as MSize;

class Media implements \JsonSerializable
{
    private $force;

    private $handler;
    private $media;
    private $origin;
    private $path;
    private $sizes;

    private $is_image;

    private $target;
    private $target_webp;
    private $target_avif;

    private $width;
    private $height;

    private $size;

    private function _applyTarget(string $target): void{
        $this->size = $target;

        $this->target = $this->origin;
        if(!isset($this->sizes->$target))
            return;

        $sizes = $this->sizes->$target;

        if(isset($sizes->origin))
            $this->target = $sizes->origin;

        if(isset($sizes->webp))
            $this->target_webp = $sizes->webp;

        if(isset($sizes->avif))
            $this->target_avif = $sizes->avif;
    }

    private function _manipulate(int $width=null, int $height=null, string $compress=null): ?string{
        $handler  = $this->handler->class;
        $used_url = null;

        $lazy_url = $handler::getLazySizer($this->path, $width, $height, $compress, !!$this->force);
        if($lazy_url)
            $used_url = $lazy_url;

        else{
            // 1. download the image
            $local_file = $handler::getLocalPath($this->path);
            if(!$local_file)
                return null;

            // 2. resize/compress($compress) to /tmp
            $tmp_file = tempnam(sys_get_temp_dir(), 'mim-compress-');

            // resize only
            if(!$compress){
                $si = new SimpleImage;
                $si->fromFile($local_file);
                $si->thumbnail($width, $height);
                $si->toFile($tmp_file);

            }else{
                if($compress === 'webp'){
                    $si = new SimpleImage;
                    $si->fromFile($local_file);
                    if($width && $height)
                        $si->thumbnail($width, $height);
                    $si->toFile($tmp_file, 'image/webp');

                // avif is not yet supported
                }else{
                    return null;
                }
            }

            // 3. upload the image from /tmp ( upload )
            $comp_name = $this->path;
            if($width && $height){
                $suffix    = '_' . $width . 'x' . $height;
                $comp_name = preg_replace('!\.[a-zA-Z]+$!', $suffix . '$0', $comp_name);
            }
            if($compress)
                $comp_name.= '.' . $compress;

            $used_url = $handler::upload($tmp_file, $comp_name);
            unlink($tmp_file);
            if(!$used_url)
                return null;
        }

        if(!$used_url)
            return null;

        if(!isset($this->sizes->{$this->size}))
            $this->sizes->{$this->size} = (object)[];

        $comp_name = $compress ? $compress : 'origin';

        if(!isset($this->sizes->{$this->size}->{$comp_name}))
            $this->sizes->{$this->size}->{$comp_name} = $used_url;

        $db_size = MSize::getOne([
            'media' => $this->media->id,
            'size'  => $this->size
        ]);

        $db_size_id = null;
        $encoded = json_encode($this->sizes->{$this->size});

        if($db_size){
            $db_size_id = $db_size->id;
            MSize::set(['urls'=>$encoded], ['id'=>$db_size->id]);
        }else{
            $new_size = [
                'media' => $this->media->id,
                'size'  => $this->size,
                'urls'  => $encoded
            ];
            $db_size_id = MSize::create($new_size);
        }

        $media_sizes = MSize::get(['media'=>$this->media->id]);
        $this->sizes = (object)[];
        foreach($media_sizes as $size)
            $this->sizes->{$size->size} = json_decode($size->urls);

        return $used_url;
    }

    public function __construct(object $opt, int $t_width=null, int $t_height=null, bool $force=false){
        $this->force   = $force;
        $this->handler = $opt->handler;
        $this->media   = $opt->media;
        $this->origin  = $opt->origin;
        $this->path    = $opt->path;
        $this->sizes   = $opt->sizes;

        if(false === strstr($this->media->mime, 'image'))
            return ( $this->target = $this->origin );

        $this->is_image = true;

        $i_width  = $this->media->width;
        $i_height = $this->media->height;

        $this->width = $i_width;
        $this->height= $i_height;

        if(!$t_width && !$t_height)
            return $this->_applyTarget('origin');

        if(!$t_width)
            $t_width = ceil($i_width * $t_height / $i_height);
        if(!$t_height)
            $t_height = ceil($i_height * $t_width / $i_width);

        $this->width = $t_width;
        $this->height= $t_height;

        if($t_width == $i_width && $t_height == $i_height)
            return $this->_applyTarget('origin');

        $size_key = $t_width . 'x' . $t_height;
        if(!isset($this->sizes->{$size_key})){
            $this->size = $size_key;
            $this->_manipulate($t_width, $t_height);
        }

        $this->_applyTarget($size_key);
    }

    public function __get($name){
        if(isset($this->{$name}))
            return $this->{$name};

        if(in_array($name, ['webp', 'avif'])){
            if(!$this->is_image)
                return null;

            $cpr_key = 'target_' . $name;
            if($this->{$cpr_key})
                return $this->{$cpr_key};

            $width  = $this->size == 'origin' ? null : $this->width;
            $height = $this->size == 'origin' ? null : $this->height;

            $result   = $this->_manipulate($width, $height, $name);
            if(!$result)
                return null;
            return $result;
        }

        if(substr($name, 0, 1) === '_'){
            if(false === strstr($name, 'x'))
                $name.= 'x';

            $sizes  = explode('x', substr($name,1));

            $width  = $sizes[0] ? $sizes[0] : null;
            $height = $sizes[1] ? $sizes[1] : null;

            if($width === $this->width && $height === $this->height)
                return $this;

            return new Media($this, $width, $height, $this->force);
        }

        return null;
    }

    public function __toString(){
        return $this->target ?? '';
    }

    public function getHandlerClass(): string
    {
        return $this->handler->class;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(){
        return $this->__toString();
    }

    public function reManipulate(int $width=null, int $height=null, string $compression=null): ?string{
        return $this->_manipulate($width, $height, $compression);
    }

    public function setForce(bool $force): void{
        $this->force = $force;
    }
}
