<?php

namespace Nng\Nnrestapi\Annotations;

/**
 * ## Api\Upload
 * 
 * Define how and where to copy / move uploaded files.
 * 
 * Examples for `@Api\Upload(...)`:
 * ```
 * @Api\Upload("default")
 * ```
 * 
 * @Annotation
 */
class Upload
{
    public $value;

    public function __construct( $arr ) {
        $this->value = $arr['value'] ?? null;
    }

    public function mergeDataForEndpoint( &$data ) {
        $data['uploadConfig'] = $this->value;
    }
}