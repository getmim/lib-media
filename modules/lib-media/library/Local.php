<?php
/**
 * Local
 * @package lib-media
 * @version 0.0.1
 */

namespace LibMedia\Library;

use \claviska\SimpleImage;
use \LibCompress\Library\Compressor;

class Local implements \LibMedia\Iface\Handler
{

    private static function compress(object $result, bool $force=false): object{
        return $result;
    }

    private static function imageCompress(object $result, bool $force=false): object{
        if( $webp = self::makeWebP($result, $force) )
            $result->webp = $webp;
        // if( $jp2  = self::makeJp2($result, $force) )
            // $result->jp2  = $jp2;

        return self::compress($result);
    }

    private static function makeJp2(object $result, bool $force=false): ?string{
        if(!class_exists('Imagick'))
            return null;

        if(!preg_match('!\.jpe?g$!i', $result->none))
            return null;

        // not yet fully supported
        // $file_abs_jp2 = $result->base . '.jp2';
        // if(!is_file($file_abs_jp2)){
        //     $img = new \Imagick($result->base);
        //     $img->setImageFormat("jp2");
        //     $img->setOption('jp2:quality', 40);
        //     $img->writeImage($file_abs_jp2);
        // }

        // if(is_file($file_abs_jp2))
        //     return $result->none . '.jp2';
        
        return null;
    }

    private static function makeWebP(object $result, bool $force=false): ?string{
        if(!preg_match('!\.png$!i', $result->none))
            return null;

        if(!$force && module_exists('media-sizer'))
            return $result->none . '.webp';

        $file_abs_webp = $result->base . '.webp';
        if(!is_file($file_abs_webp)){
            (new SimpleImage)
                ->fromFile($result->base)
                ->toFile($file_abs_webp, 'image/webp');
        }

        if(is_file($file_abs_webp))
            return $result->none . '.webp';
        return null;
    }

    static function get(object $opt): ?object {
        if(!isset($opt->force))
            $opt->force = false;

        $base = \Mim::$app->config->libUpload->base ?? null;
        if(!$base)
            $base = (object)['local'=>'media','host'=>''];

        $base_file = $opt->file;

        if($base->host){
            $host_len = strlen($base->host);
            $file_host= substr($opt->file, 0, $host_len);
            if($file_host != $base->host)
                return null;

            $base_file = substr($opt->file, $host_len);
        }
        
        if(substr($base->local,0,1) != '/')
            $base->local = realpath(BASEPATH . '/' . $base->local);

        $file_abs = $base->local . '/' . $base_file;
        if(!is_file($file_abs))
            return null;

        $file_ext = explode('.', $file_abs);
        $file_ext = end($file_ext);
        $file_ext = strtolower($file_ext);

        $is_image  = in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);

        $result = (object)[
            'base' => $file_abs,
            'none' => $base->host . $base_file
        ];

        if(!$is_image)
            return self::compress($result, $opt->force);

        if(!isset($opt->size))
            return self::imageCompress($result, $opt->force);

        list($i_width, $i_height) = getimagesize($file_abs);
        $result->size = (object)[
            'width'  => $i_width,
            'height' => $i_height
        ];

        $t_width = $opt->size->width ?? null;
        $t_height= $opt->size->height ?? null;

        if(!$t_width)
            $t_width = ceil($i_width * $t_height / $i_height);
        if(!$t_height)
            $t_height = ceil($i_height * $t_width / $i_width);

        if($t_width == $i_width && $t_height == $i_height)
            return self::imageCompress($result, $opt->force);

        $suffix       = '_' . $t_width . 'x' . $t_height;
        $base_file    = preg_replace('!\.[a-zA-Z]+$!', $suffix . '$0', $base_file);

        $result->none = $base->host . $base_file;
        $file_abs     = $base->local . '/' . $base_file;
        $file_ori_abs = $result->base;

        $result->base = $file_abs;

        if(is_file($file_abs))
            return self::imageCompress($result, $opt->force);

        if($opt->force || !module_exists('media-sizer')){
            // resize the image
            $image = (new SimpleImage)
                ->fromFile($file_ori_abs)
                ->thumbnail($t_width, $t_height)
                ->toFile($file_abs);
        }
            
        return self::imageCompress($result, $opt->force);
    }
}