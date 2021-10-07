<?php 

namespace Nng\Nnrestapi\Utilities;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Composer\Autoload\ClassMapGenerator;

/**
 * Helper for reading / parsing annotations
 * 
 */
class Annotations extends \Nng\Nnhelpers\Singleton {

	/**
	 * Strip these annotation from documentation while
	 * parsing the methods.
	 * 
	 */
	const ANNOTATION_NAMESPACE = 'Api\\';

	/**
	 * Funktioniert wie `\nn\t3::Endpoint()->getClassMap()` – ergänzt aber noch die Kommentare aus
	 * dem DocComment der einzelnen Klassen-Methoden. Wird verwendet für das Backend-Modul
	 * und die Doku der einzelnen Endpoints im Backend.
	 * ```
	 * \nn\rest::Annotations()->getClassMapWithDocumentation();
	 * ```
	 * @return array
	 */
	public function getClassMapWithDocumentation( &$arr = null ) {

		if ($arr === null) {
			$arr = \nn\rest::Endpoint()->getClassMap();
		}

		foreach ($arr as &$v) {
			if (!is_array($v)) continue;
			if (($className = $v['class'] ?? false) && ($methodName = $v['method'] ?? false)) {
				$method = new \ReflectionMethod( $className, $methodName );
				$comment = \Nng\Nnhelpers\Helpers\AnnotationHelper::parse($method->getDocComment(), self::ANNOTATION_NAMESPACE);
				$v += $comment;
				$v['annotations'] = $v['@'] ?? [];
				continue;
			}
			$this->getClassMapWithDocumentation( $v );
		}
		
		return $arr;
	}
	
}