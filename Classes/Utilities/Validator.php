<?php 

namespace Nng\Nnrestapi\Utilities;

use TYPO3\CMS\Extbase\Validation\ValidatorResolver;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Helper to validate Models
 * 
 */
class Validator extends \Nng\Nnhelpers\Singleton 
{
	/**
	 * Return a list of validation errors for a given model.
	 * 
	 * ```
	 * \nn\rest::Validator()->validateModel( $model );
	 * ```
	 * 
	 * The validators can be defined using the standard TYPO3 annotations in your model.
	 * Refer to the TYPO3 docs https://bit.ly/3gpI5jQ for details.
	 *
	 * Example return array:
	 * ```
	 * [
	 * 	'title' => [
	 * 		1428504122 => 'The length of the given string was not between 3 and 50 characters.',
	 * 		1221559976 => 'The given subject was not a valid email address.'
	 * 	]
	 * ]
	 * ```
	 * 
	 * @param \TYPO3\CMS\Extbase\DomainObject\AbstractEntity $model
	 * @return array
	 */
	public function validateModel( $model = null ) 
	{
		$modelName = get_class( $model );
	
		// create validator for given model
		$validatorResolver = GeneralUtility::makeInstance( ValidatorResolver::class );
		$validator = $validatorResolver->getBaseValidatorConjunction( $modelName );
		if (!$validator) return [];

		// check if there were errors
		$validation = $validator->validate( $model );
		if (!$validation->hasErrors()) return [];

		// flatten errors to make it easier to interpret and parse them
		$errorsByProperty = [];
		foreach ($validation->getFlattenedErrors() as $k=>$errorList) {
			$messages = [];
			foreach ($errorList as $error) {
				$messages[$error->getCode()] = $error->getMessage();
			}
			$errorsByProperty[$k] = $messages;
		}

		return $errorsByProperty;
	}

}