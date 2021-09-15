<?php

namespace Nng\Nnrestapi\Routing\Enhancer;

/**
 * Helper for processing various variables within a Route Enhancer
 */
class VariableProcessor extends \TYPO3\CMS\Core\Routing\Enhancer\VariableProcessor
{
	/**
	 * Wird aufgerufen, wenn die URL in `$_GET`-Variablen zurück übersetzt wird.
	 * Sorgt dafür, dass `$_GET['controller']` und `$_GET['action']` korrekt zugeordnet werden,
	 * auch wenn im ersten Pfadsegment ein bestimmter Endpoint erreicht werden soll.
	 * 
	 * Die Endpoints werden über `\nn\rest::Endpoint()->register()` in der `ext_localconf.php` registriert.
	 * Dieses Beispiel zeigt, wie die URL in `$_GET`-Variablen umgewandelt werden muss, wenn ein Endpoint
	 * mit dem Slug `nnbeispiel` über `\nn\rest::Endpoint()->register(['slug'=>'nnbeispiel', ...])`
	 * registriert wurde:
	 * 
	 * `/api/test/run` 				=> `['controller'=>'test', 'action'=>'run']`
	 * `/api/nnbeispiel/test/run` 	=> `['ext'=>'nnbeispiel', 'controller'=>'test', 'action'=>'run']`
	 * 
	 * @param array $items
	 * @param string|null $namespace
	 * @param array $arguments
	 * @param bool $hash = true
	 * @return array
	 */
	public function inflateKeys(array $items, string $namespace = null, array $arguments = [], bool $hash = true): array
	{
		if (empty($items) || empty($arguments) && empty($namespace)) {
			return $items;
		}

		$keys = $this->inflateValues(array_keys($items), $namespace, $arguments, $hash);
		$items = array_values($items);
		$params = array_combine( $keys, $items );

		// Slugs, die über `\nn\rest::Endpoint()->register()` registriert wurden, z.B. ['nnrestdemo', 'nnrestapi']
		$endpointSlugs = array_column( \nn\rest::Endpoint()->getAll(), 'slug' );

		// War der URL-Pfad `api/{slug}/...` statt `api/{controller}/...`?
		if (in_array($params['controller'], $endpointSlugs)) {

			// dann schieben wir noch ein `ext` vor die keys. Und einen leeren Wert in die Values
			array_unshift( $keys, 'ext' );
			array_push( $items, '' );

			// damit sind alle Parameter um eine Position verschoben und `controller` und `action` bekommen die korrekten Werte
			$params = array_combine( $keys, $items );
		}

		return $params;
	}

}
