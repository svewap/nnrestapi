<?php
namespace Nng\Nnrestapi\ViewHelpers\Uri;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Erzeugt ein URL zur Api.
 * ```
 * {rest:uri.api(controller:'test')}
 * {rest:uri.api(controller:'test', action:'index', uid:123, param1:'one', param2:'two')}
 * {rest:uri.api(ext:'nnrestapi', controller:'test', action:'index', uid:123, param1:'one', param2:'two')}
 * {rest:uri.api(controller:'test', action:'index', uid:123, param1:'one', param2:'two', type:'20220101')}
 * {rest:uri.api(controller:'test', action:'index', additionalParams:'{a:123}')}
 * ```
 * Ergibt die URL:
 * ```
 * /api/test/index/123/one/two
 * ```
 */
class ApiViewHelper extends AbstractViewHelper {
	
	public function initializeArguments() {
		parent::initializeArguments();
        $this->registerArgument('ext', 'string', 'slug für Endpoint, definiert per \nn\rest::Endpoint()->register()', false);
        $this->registerArgument('controller', 'string', 'Controller', false, 'index');
        $this->registerArgument('action', 'string', 'Action', false, 'index');
        $this->registerArgument('uid', 'string', 'uid', false);
        $this->registerArgument('param1', null, 'Parameter 1', false, '');
        $this->registerArgument('param2', null, 'Parameter 2', false, '');
        $this->registerArgument('param3', null, 'Parameter 3', false, '');
        $this->registerArgument('additionalParams', null, 'additionalParams', false, []);
        $this->registerArgument('type', 'string', 'PageType', false, '');
        $this->registerArgument('absolute', 'boolean', 'Return absolute URL', false, false);
   }

	public static function renderStatic( array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext ) {

		$apiArgs = ['ext', 'controller', 'action', 'uid', 'param1', 'param2', 'param3'];
		$args = ['additionalParams', 'type', 'absolute'];
        
        $linkParams = [];
		foreach ($apiArgs as $arg) {
			$linkParams[$arg] = $arguments[$arg];
		}

		foreach ($args as $arg) {
			${$arg} = $arguments[$arg] ?? '';
		}

        return \nn\rest::Api()->uri( $linkParams, $additionalParams );
	}
	
}