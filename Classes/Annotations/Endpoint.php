<?php

namespace Nng\Nnrestapi\Annotations;

/**
 * ## Api\Endpoint
 * 
 * Annotation for adding an example to the automatically generated documentation
 * in the TYPO3 backend module.
 * 
 * Use like this inside of your annotation:
 * ```
 * @Api\Endpoint("entry")
 * @Api\Endpoint({"name":"entry"})
 * ```
 * 
 * @IgnoreAnnotation("Api\Endpoint")
 * @Annotation
 */
class Endpoint
{
    public $value;

    public function __construct( $arr ) {
        $value = $arr['value'] ?? [];
        $this->value = !is_array($value) ? ['name'=>$value] : $value;
    }

    public function mergeDataForClassInfo( &$data ) {
        $data['customClassName'] = $this->value['name'] ?? '';
    }
}