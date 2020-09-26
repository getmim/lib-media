<?php
/**
 * Handler
 * @package lib-media
 * @version 0.0.1
 */

namespace LibMedia\Iface;

interface Handler
{
    /**
     * Get registered path of the URL
     * @param string $full URL
     * @return string on found, null on not match
     */
    static function getPath(string $url): ?string;

    /**
     * Download remote URL to local ( /tmp ) for local
     * processing.
     * @param string $path Path key of the file.
     * @return string on success, null otherwise.
     */
    static function getLocalPath(string $path): ?string;

    /**
     * Check if the file should be resize/compress lazyly ( with media-sizer or other )
     * @param string $path Path key of the media
     * @param int $width Target width, null for original
     * @param int $height Target height, null for original
     * @param string $compress Target compression, it can be null for original
     */
    static function isLazySizer(string $path, int $width=null, int $height=null, string $compress=null): ?string;

    /**
     * Upload local file to remote
     * @param string $local Absolute path to local file
     * @param string $name Expected target name
     * @return string Final URL of uploaded file or null on error
     */
    static function upload(string $local, string $name): ?string;
}