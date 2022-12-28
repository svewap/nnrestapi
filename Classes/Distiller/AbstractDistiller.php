<?php

namespace Nng\Nnrestapi\Distiller;

class AbstractDistiller 
{
	/**
	 * Definiert, welche Felder/Keys im Array behalten werden sollen.
	 * Wenn leer, wird das komplette Array zurückgegeben.
	 * Wird von den einzelnen Distillern überschrieben.
	 * 
	 * @var array
	 */
	public $keysToKeep = [];


	/**
	 * Wird von ApiController aufgerufen bevor die Daten zurückgegeben werden.
	 * Zentrale Methode zum Bearbeiten / Distillen der Daten.
	 * 
	 * ```
	 * $this->processData( $assArr );
	 * $this->processData( [$assArr, $assArr, ...] );
	 * ```
	 * @return void
	 */
	public function processData( &$data = [] ) 
	{
		if (is_a($data, \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult::class)) {
			$data = $data->toArray();
		} else if (is_a($data, \stdClass::class)) {
			$data = (array) $data;
		}

		if ($this->isAssoc( $data )) {
			$this->process( $data );
			$this->pluck( $data, $this->keysToKeep );
		} else {
			foreach ($data as &$row) {
				$this->process( $row );
				$this->pluck( $row, $this->keysToKeep );
			}
		}
	}

	
	/**
	 * Prüft, ob es sich um ein assoziatives Array handelt.
	 * ```
	 * $this->isAssoc( $arr );
	 * ```
	 * @return boolean
	 */
	public function isAssoc( $arr = [] ) 
	{
		if (array() === $arr) return false;
		return array_keys($arr) !== range(0, count($arr) - 1);
	}
	
	
	/**
	 * Einzelnes Element bearbeiten.
	 * Diese Methode wird von den einzelnen Distillern überschrieben.
	 * ```
	 * $this->process( $assArr );
	 * ```
	 * @return void
	 */
	public function process( &$data = [] ) {}
	
	
	/**
     * Reduce array to single fields.
     * ```
     * // Simple array: Properties of given keys are returned
     * $this->pluck( $arr, ['uid', 'images', 'title'] );
	 * 
     * // Deep array: Properties of nested array are returned. Key is returned in dot-syntax
     * $this->pluck( $arr, ['uid', 'images', 'title', 'images.0.publicUrl'] );
     *
     * // Associative array: Get deep nested property and map it to a new key
     * $this->pluck( $arr, ['uid'=>'uid', 'publicUrl'=>'images.0.publicUrl'] );
     *
     * // Mixture is also possible
     * $this->pluck( $arr, ['uid', 'title', publicUrl'=>'images.0.publicUrl'] );
     * ```
     * @return void
     */
    public function pluck( &$data = [], $keysToKeep = [] ) 
	{
        if (!$keysToKeep) return;

        $helper = \nn\t3::Settings();
		$objHelper = \nn\t3::Obj();
        $flatResult = [];

		$arr = $objHelper->toArray($data, 6);
		if (is_object($data)) {
			\Nng\Nnrestapi\Distiller\ModelDistiller::process( $data, $arr );
		}

        foreach ($keysToKeep as $key=>$path) {
            if (is_numeric($key)) {
				$key = $path;
            }
			$flatResult[$key] = $helper->getFromPath($path, $arr);
        }
        $data = $flatResult;
    }

}