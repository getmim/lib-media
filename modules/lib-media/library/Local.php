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
        if(module_exists('lib-compress')){
            // brotli
            $brotli_base = $result->base . '.br';
            if(!is_file($brotli_base))
                Compressor::brotli($result->base, $brotli_base);

            // gzip
            $gzip_base = $result->base . '.gz';
            if(!is_file($gzip_base))
                Compressor::gzip($result->base, $gzip_base);
        }

        return $result;
    }

    private static function makeWebP(object $result): object{
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
        $base = \Mim::$app->config->libUpload->base->local ?? 'media';
        if(substr($base,0,1) != '/')
            $base = realpath(BASEPATH . '/' . $base);

        $file_abs = $base . '/' . $opt->file;
        if(!is_file($file_abs))
            return null;

        $file_mime  = mime_content_type($file_abs);
        $url_base   = substr($base, strlen(BASEPATH));
        $is_image   = fnmatch('image/*', $file_mime);

        $result = (object)[
            'base' => $file_abs,
            'none' => $url_base . '/' . $opt->file
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
        $opt->file    = preg_replace('!\.[a-zA-Z]+$!', $suffix . '$0', $opt->file);

        $result->none = $url_base . '/' . $opt->file;
        $file_abs     = $base . '/' . $opt->file;

        if(is_file($file_abs))
            return self::makeWebP($result);

        // resize the image
        $image = (new SimpleImage)
            ->fromFile($result->base)
            ->bestFit($t_width, $t_height);

        $c_width  = $image->getWidth();
        $c_height = $image->getHeight();

        if($c_width == $t_width && $c_height == $t_height){
            $image->toFile($file_abs);
            return self::makeWebP($result);
        }

        // add grey background if the image size is not equal to requested one
        $image = (new SimpleImage)
            ->fromNew($t_width, $t_height, '#CECECE')
            ->overlay($image, 'center')
            ->toFile($file_abs);
            
        $result->base = $file_abs;

        return self::makeWebP($result);
    }
}