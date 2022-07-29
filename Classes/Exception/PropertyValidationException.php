<?php

namespace Nng\Nnrestapi\Exception;

/**
 * Custom exception that is thrown if mapping the JSON to a Model fails
 * because the value passed was invalid for the Model property. 
 * @see `@TYPO3\CMS\Extbase\Annotation\Validate`
 * 
 */
class PropertyValidationException extends \Exception {}