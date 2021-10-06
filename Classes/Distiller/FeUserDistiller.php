<?php

namespace Nng\Nnrestapi\Distiller;

/**
 * Distiller für FrontendUser-Daten.
 * Entfernt alle Daten, die kritisch – oder unwichtig – sind.
 * 
 * Einbindung über die Annotation direkt an der jeweiligen Api-Methode:
 * ```
 * @api\distiller Nng\Nnrestapi\Distiller\FeUserDistiller
 * ```
 * Siehe `Nng\Nnrestapi\Api\Auth->postIndexAction()` für ein Beispiel.
 * 
 */
class FeUserDistiller extends AbstractDistiller {


	/**
	 * Nur diese Felder behalten.
	 * Siehe `AbstractDistiller` für mehr Infos.
	 * 
	 * @var array
	 */
	public $keysToKeep = ['uid', 'username', 'usergroup', 'token', 'first_name', 'last_name', 'lastlogin'];


	/**
	 * Wird für jedes einzelne Element/Model aufgerufen
	 * 
	 * @return void
	 */
	public function process( &$data = [] ) {
		$data['usergroup'] 	= \nn\t3::Arrays($data['usergroup'])->intExplode();
	}

}