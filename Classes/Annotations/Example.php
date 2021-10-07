<?php

namespace Nng\Nnrestapi\Annotations;

/**
 * ## Api\Example
 * 
 * Annotation for adding an example to the automatically generated documentation
 * in the TYPO3 backend module.
 * 
 * Use like this inside of your annotation:
 * ```
 * @Api\Example("this is an example")
 * @Api\Example("{'username':'david', 'password':'mypassword'}")
 * ```
 * 
 * @Annotation
 */
class Example
{
    public $value;

    public function __construct( $arr ) {
        $this->value = $arr['value'];
    }
}