<?php
/**
 * Media format type
 * @package lib-media
 * @version 1.3.1
 */

namespace LibMedia\Formatter;

use LibMedia\Object\Media as _Media;
use LibUpload\Model\Media as _UMedia;
use LibMedia\Model\MediaSize as MSize;

class Media
{
    static function single(array $values, string $field, array $object, object $format, $options){
        $handlers = \Mim::$app->config->libMedia->handlers;
        $val_path = [];
        $all_path = [];

        foreach($values as $value){
            if(is_null($value))
                continue;

            foreach($handlers as $name => $handler){
                $res = $handler::getPath($value);
                if(!$res)
                    continue;

                $val_path[$value] = (object)[
                    'origin'   => $value,
                    'handler' => (object)[
                        'name' => $name,
                        'class'=> $handler
                    ],
                    'path'    => $res
                ];
                $all_path[] = $res;
                break;
            }
        }

        if(!$val_path)
            return [];

        $media = _UMedia::get(['path'=>$all_path]);
        if(!$media)
            return [];

        $media_id = array_column($media, 'id');
        $media_sizes = MSize::get(['media'=>$media_id]) ?? [];
        if($media_sizes)
            $media_sizes = group_by_prop($media_sizes, 'media');

        $media = prop_as_key($media, 'path');
        foreach($val_path as $value => &$opt){
            if(!isset($media[ $opt->path ]))
                continue;

            $opt->media = $media[ $opt->path ];

            $opt->sizes = (object)[];
            if(isset($media_sizes[ $opt->media->id ])){
                $sizes = $media_sizes[ $opt->media->id ];
                foreach($sizes as $size)
                    $opt->sizes->{$size->size} = json_decode($size->urls);
            }
        }
        unset($opt);

        $result   = [];
        foreach($val_path as $value => $opt)
            $result[$value] = new _Media($opt);

        return $result;
    }

    static function multiple(array $values, string $field, array $object, object $format, $options){
        if(!$values)
            return [];

        $file_urls = [];
        $result = [];
        foreach($values as $value){
            $hash  = md5($value);
            $value = json_decode($value);
            $result[$hash] = $value;

            if($value){
                foreach($value as $val)
                    $file_urls[] = $val;
            }
        }

        $file_fmt = self::single($file_urls, $field, $object, $format, $options);
        foreach($result as $hash => $value){
            $final_value = [];
            if($value){
                foreach($value as $val){
                    if(isset($file_fmt[$val]))
                        $final_value[] = $file_fmt[$val];
                }
            }
            $result[$hash] = $final_value;
        }

        return $result;
    }
}
