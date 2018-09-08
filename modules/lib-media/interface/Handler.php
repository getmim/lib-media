<?php
/**
 * Handler
 * @package lib-media
 * @version 0.0.1
 */

namespace LibMedia\Iface;

interface Handler
{

    static function get(object $file): ?object;
}