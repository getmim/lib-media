<?php
/**
 * Media format type
 * @package lib-media
 * @version 0.0.1
 */

namespace LibMedia\Formatter;

use LibMedia\Object\Media as _Media;

class Media
{
    static function single($value, string $field, object $object, object $format, $options){
        if(!$value)
            return null;
        return new _Media($value);
    }

    static function multiple($value, string $field, object $object, object $format, $options){
        $sep = $format->separator ?? PHP_EOL;
        if($sep == 'json')
            $vals = json_decode($value);
        else
            $vals = explode($sep, $value);
        
        $result = [];
        foreach($vals as $val){
            $val = trim($val);
            if($val)
                $result[] = new _Media($val);
        }
        return $result;
    }
}