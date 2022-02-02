<?php

namespace Nng\Nnrestapi\Distiller;

use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;

/**
 * Generic Distiller for a Model.
 * 
 */
class ModelDistiller 
{
	/**
	 * ## Destill array-data created from a Model
	 * 
	 * Recursive method for postprocessing array data that was generated from models.
	 * Using `\nn\t3::Convert()->toArray()` will result in a big array where every key
	 * of the model is converted to a string, boolean or array.
	 * 
	 * Some of the data might be unnecessary – or even sensitive like password.
	 * By defining `tx_nnrestapi.settings.globalDestillers.[modelName]` you can set postprocessing
	 * instruction, e.g. `exclude` to unset certain fields in the model.
	 * 
	 * This method needs the original `$model` as retrieved from the Repository and
	 * the array-data from the model which was converted using `\nn\t3::Convert()->toArray()`.
	 * 
	 * It recursivly runs through the model and processes the array according to the
	 * settings defined for the indiviual models in TypoScript.
	 * 
	 * ```
	 * \Nng\Nnrestapi\Distiller\ModelDistiller::process( $model, $array, $config );
	 * ```
	 * @param 	mixed 	$model 	the orignal model
	 * @param	array	$data	the array the model was converted in
	 * @param	array	$config	the configuration for destilling the array
	 * @return void
	 */
	public static function process( $model, &$data = [], $config = [], $flattenFileReferences = null ) {
		
		if (!$model || (!is_object($model) && !is_array($model))) return;
		
		$distillers = $config ?: \nn\t3::Settings()->get('tx_nnrestapi')['globalDistillers'] ?: [];

		// find configuration for current Model, if defined in typscript `settings.globalDistillers.Some\Model\Name`
		$distillerConfigForClass = [];
		foreach ($distillers as $className=>$config) {
			if (is_a( $model, $className, true )) {
				$distillerConfigForClass = $config;
			}
		}
		
		// reduce FileReferences to publicUrl? If set in configuration, this will be passed on recursively from here
		if ($distillerConfigForClass && isset($distillerConfigForClass['flattenFileReferences'])) {
			$flattenFileReferences = $distillerConfigForClass['flattenFileReferences'];
		}

		// flattenFileReference was set for current Model OR by parent Model. Reduce to publicUrl.
		if ($flattenFileReferences && \nn\t3::Obj()->isFileReference($model)) {
			$data = $data['publicUrl'] ?? null;
		}

		// Convert QueryResult and ObjectStorage to Array
		if (is_a($model, QueryResult::class, true) || \nn\t3::Obj()->isStorage($model)) {
			$model = $model->toArray();
		}
		
		if (is_array($model)) {
			foreach ($model as $n=>$item) {
				self::process( $item, $data[$n], $distillers, $flattenFileReferences );
			}
			return;
		}

		// exclude certain fields?
        if (isset($distillerConfigForClass['exclude']) && $exclude = \nn\t3::Arrays($distillerConfigForClass['exclude'])->trimExplode()) {
			foreach ($exclude as $key) {
				unset( $data[$key] );
			}
		}

		// only include certain fields?
        $include = isset($distillerConfigForClass['include'])?\nn\t3::Arrays($distillerConfigForClass['include'])->trimExplode():false;
		
		$props = \nn\t3::Obj()->getKeys( $model );

		foreach ($props as $key) {
			$val = \nn\t3::Obj()->get( $model, $key );
			if ($include && !in_array($key, $include)) {
				unset($data[$key]);
			}
			if (!isset($data[$key]) || $data[$key] == null) continue;
			self::process( $val, $data[$key], $distillers, $flattenFileReferences );
		}

	}

}