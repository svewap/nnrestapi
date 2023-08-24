<?php

namespace Nng\Nnrestapi\Annotations\Upload;

/**
 * ## Api\Upload\Encrypt
 * 
 * (!!!) EXPERIMENTAL. NOT IN USE YET.
 * 
 * Allow encrypting uploaded files on-the-fly.
 * 
 * Examples for `@Api\Upload\Encrypt(...)`:
 * ```
 * @Api\Upload\Encrypt("default")
 * ```
 * 
 * @Annotation
 */
class Encrypt
{
    public $value;

    public function __construct( $arr ) {
        $this->value = $arr['value'] ?? null;
    }

    public function mergeDataForEndpoint( &$data ) {
        $data['uploadEncryptConfig'] = $this->value;
    }
}