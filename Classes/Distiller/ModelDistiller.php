<?php

namespace Nng\Nnrestapi\Distiller;

/**
 * Distiller für ein Model.
 * 
 * ```
 * \Nng\Nnrestapi\Distiller\ModelDistiller::process( $model, $array, $config );
 * ```
 * 
 */
class ModelDistiller {

	/**
	 * 
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
			if (!isset($data[$key])) continue;
			self::process( $val, $data[$key], $distillers );			
		}

	}

}