<?php

namespace Nng\Nnrestapi\Annotations;

/**
 * ## Api\Distiller
 * 
 * Makes is possible to define a post-processor before the JSON is sent to the frontend.
 * Use this annotation like this:
 * 
 * ```
 * @Api\Distiller( \Nng\Nnrestapi\FeUserDistiller::class )
 * ```
 * 
 * @Annotation
 */
class Distiller
{
    public $value;

    public function __construct( $arr ) {
        $this->value = $arr['value'];
    }

    public function mergeDataForEndpoint( &$data ) {
        if (!class_exists($this->value)) {
            \nn\t3::Exception("Distiller {$this->value} not found in @Api\Distiller()");
        };
        $data['distiller'] = $this->value;
    }
}