<?php

namespace Nng\Nnrestapi\Distiller;

/**
 * Distiller für ein Model.
 * 
 */
class ModelDistiller {

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
	public static function process( $model, &$data = [], $config = [] ) {
		
		if (!$model || (!is_object($model) && !is_array($model))) return;

		$distillers = $config ?: \nn\t3::Settings()->get('tx_nnrestapi')['globalDistillers'] ?: [];
		
		if (\nn\t3::Obj()->isStorage($model)) {
			foreach ($model->toArray() as $n=>$item) {
				self::process( $item, $data[$n], $distillers );	
			}
			return;
		}

		foreach ($distillers as $className=>$config) {
			if (is_a( $model, $className, true )) {
				if ($exclude = \nn\t3::Arrays($config['exclude'])->trimExplode()) {
					foreach ($exclude as $key) {
						unset( $data[$key] );
					}
				}
			}
		}
		
		$props = \nn\t3::Obj()->getKeys( $model );

		foreach ($props as $key) {
			$val = \nn\t3::Obj()->get( $model, $key );
			if (!isset($data[$key]) || $data[$key] == null) continue;
			self::process( $val, $data[$key], $distillers );			
		}

	}

}