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

    private static function compress(object $result): object{
        return $result;
    }

    private static function makeWebP(object $result): object{
        if(!preg_match('!\.png$!i', $result->none))
            return self::compress($result);

        $file_abs_webp = $result->base . '.webp';
        if(!is_file($file_abs_webp)){
            (new SimpleImage)
                ->fromFile($result->base)
                ->toFile($file_abs_webp, 'image/webp');
        }

        if(is_file($file_abs_webp))
            $result->webp = $result->none . '.webp';

        return self::compress($result);
    }

    static function get(object $opt): ?object {
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

        $file_mime  = mime_content_type($file_abs);
        $is_image   = fnmatch('image/*', $file_mime);

        $result = (object)[
            'base' => $file_abs,
            'none' => $base->host . $base_file
        ];

        if(!$is_image)
            return self::compress($result);

        list($i_width, $i_height) = getimagesize($file_abs);
        $result->size = (object)[
            'width'  => $i_width,
            'height' => $i_height
        ];

        if(!isset($opt->size))
            return self::makeWebP($result);

        $t_width = $opt->size->width ?? null;
        $t_height= $opt->size->height ?? null;

        if(!$t_width)
            $t_width = ceil($i_width * $t_height / $i_height);
        if(!$t_height)
            $t_height = ceil($i_height * $t_width / $i_width);

        if($t_width == $i_width && $t_height == $i_height)
            return self::makeWebP($result);

        $suffix       = '_' . $t_width . 'x' . $t_height;
        $base_file    = preg_replace('!\.[a-zA-Z]+$!', $suffix . '$0', $base_file);

        $result->none = $base->host . $base_file;
        $file_abs     = $base->local . '/' . $base_file;
        $file_ori_abs = $result->base;

        $result->base = $file_abs;

        if(is_file($file_abs))
            return self::makeWebP($result);

        // resize the image
        $image = (new SimpleImage)
            ->fromFile($file_ori_abs)
            ->thumbnail($t_width, $t_height)
            ->toFile($file_abs);
            
        return self::makeWebP($result);
    }
}