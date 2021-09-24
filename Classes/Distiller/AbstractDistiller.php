<?php

namespace Nng\Nnrestapi\Distiller;

class AbstractDistiller {

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
	public function processData( &$data = [] ) {
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
	public function isAssoc( $arr = [] ) {
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
	 * Array auf einzelne Felder reduzieren.
	 * ```
	 * $this->pluck( $assArr, ['uid', 'title'] );
	 * ```
	 * @return void
	 */
	public function pluck( &$data = [], $keysToKeep = [] ) {
		if (!$keysToKeep) return;
		foreach ($data as $k=>$v) {
			if (!in_array($k, $keysToKeep)) {
				unset($data[$k]);
			}
		}
	}

}