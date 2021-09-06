<?php
namespace Nng\Nnrestapi\ViewHelpers\Uri;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Erzeugt ein URL zur Api.
 * ```
 * {rest:uri.api(controller:'test')}
 * {rest:uri.api(controller:'test', action:'index', uid:123, param1:'one', param2:'two')}
 * {rest:uri.api(controller:'test', action:'index', uid:123, param1:'one', param2:'two', type:'20220101')}
 * {rest:uri.api(controller:'test', action:'index', additionalParams:'{a:123}')}
 * ```
 * Ergibt die URL:
 * ```
 * /api/test/index/123/one/two
 * ```
 */
class ApiViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Uri\PageViewHelper {
	
	public function initializeArguments() {
		parent::initializeArguments();
        $this->registerArgument('controller', 'string', 'Controller', false, 'index');
        $this->registerArgument('action', 'string', 'Action', false, 'index');
        $this->registerArgument('uid', 'string', 'uid', false);
        $this->registerArgument('param1', null, 'Parameter 1', false, '');
        $this->registerArgument('param2', null, 'Parameter 2', false, '');
        $this->registerArgument('type', 'string', 'pageType', false, '20210904');
   }

	public static function renderStatic( array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext ) {

		$apiArgs = ['controller', 'action', 'uid', 'param1', 'param2'];
		$args = ['additionalParams', 'type', 'absolute'];
        
        $linkParams = [];
		foreach ($apiArgs as $arg) {
			$linkParams[$arg] = $arguments[$arg];
		}

		foreach ($args as $arg) {
			${$arg} = $arguments[$arg] ?? '';
		}

        // type ist als default `20210904` - siehe TypoScript Setup bzw. yaml siteconfig
        $linkParams['type'] = $type;

        return \nn\rest::Api()->uri( $linkParams, $additionalParams );
	}
	
}