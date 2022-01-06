<?php

namespace Nng\Nnrestapi\Annotations;

/**
 * ## Api\Label
 * 
 * No other function than overriding the default label in the backend module.
 * This text will appear in the collapse-elements. If no `@Api\Label` was defined,
 * the backend module will automatically use the path to the endpoint, e.g.
 * `/api/entry/all`.
 *  
 * Examples for `@Api\Label(...)`:
 * ```
 * @Api\Label("Supernice endpoint!")
 * ```
 * 
 * @Annotation
 */
class Label
{
    public $value;

    public function __construct( $arr ) {
        $this->value = $arr['value'] ?? null;
    }

    public function mergeDataForEndpoint( &$data ) {
        $data['label'] = $this->value;
    }
}