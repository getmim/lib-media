<?php
/**
 * Local
 * @package lib-media
 * @version 1.0.0
 */

namespace LibMedia\Library;

use \claviska\SimpleImage;
use \LibCompress\Library\Compressor;
use Mim\Library\Fs;

class Local implements \LibMedia\Iface\Handler
{
    static function getPath(string $url): ?string{
        $base = \Mim::$app->config->libUpload->base ?? null;
        if(!$base)
            $base = (object)['local'=>'media','host'=>''];

        $base_file = null;

        if($base->host){
            $host_len = strlen($base->host);
            $file_host= substr($url, 0, $host_len);
            if($file_host != $base->host)
                return null;

            return substr($url, $host_len);
        }

        return $url;
    }

    static function getLocalPath(string $path): ?string{
        $base = \Mim::$app->config->libUpload->base ?? null;
        if(!$base)
            $base = (object)['local'=>'media','host'=>''];

        $target_file = realpath(BASEPATH . '/' . $base->local . '/' . $path);
        if(is_file($target_file))
            return $target_file;
        return null;
    }

    static function getLazySizer(string $path, int $width=null, int $height=null, string $compress=null, bool $force=false): ?string{
        if(!module_exists('media-sizer') || $force)
            return null;

        $base = \Mim::$app->config->libUpload->base ?? null;
        if(!$base)
            $base = (object)['local'=>'media','host'=>''];

        $result = $base->host ? $base->host : '/';
        $result.= $path;

        if($width || $height){
            $suffix = '_' . $width . 'x' . $height;
            $result = preg_replace('!\.[a-zA-Z]+$!', $suffix . '$0', $result);
        }

        if($compress)
            $result.= '.' . $compress;

        return $result;
    }

    static function upload(string $local, string $name): ?string{
        $base = \Mim::$app->config->libUpload->base ?? null;
        if(!$base)
            $base = (object)['local'=>'media','host'=>''];

        $target_dir  = realpath(BASEPATH . '/' . $base->local);
        $target_file = $target_dir . '/' . $name;
        if(!is_file($target_file))
            Fs::copy($local, $target_file);

        $target_url  = $base->host . $name;

        return $target_url;
    }
}