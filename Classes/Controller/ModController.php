<?php

namespace Nng\Nnrestapi\Controller;

use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Site\SiteFinder;

/**
 * Backend Module
 * 
 */
class ModController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

    /**
	 * Backend Template Container
	 * @var string
	 */
	protected $defaultViewObjectName = \TYPO3\CMS\Backend\View\BackendTemplateView::class;

	/** 
	 * 	Cache des Source-Codes für die Doku
	 * 	@var array
	 */
	protected $sourceCodeCache = [];
	protected $maxTranslationsPerLoad = 10;

	/**
	 * 	Initialize View
	 */
	public function initializeView ( ViewInterface $view ) {
		parent::initializeView($view);

		if (!$view->getModuleTemplate()) return;
		
		$pageRenderer = $view->getModuleTemplate()->getPageRenderer();
		
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/Nnrestapi/NnrestapiBackendModule');

		$pageRenderer->addCssFile('typo3conf/ext/nnhelpers/Resources/Public/Vendor/prism/prism.css');
		$pageRenderer->addJsFile('typo3conf/ext/nnhelpers/Resources/Public/Vendor/prism/prism.js');
		$pageRenderer->addJsFile('typo3conf/ext/nnhelpers/Resources/Public/Vendor/prism/prism.download.js');

        $template = $view->getModuleTemplate();
        $template->setFlashMessageQueue($this->controllerContext->getFlashMessageQueue());
        $template->getDocHeaderComponent()->disable();
	}

	/**
	 * @return void
	 */
	public function indexAction () {
		
        $settings = \nn\t3::Settings()->getPlugin('nnrestapi');
        if (!$settings) {
            return 'No TypoScript Configuration found. Make sure you included the RestApi templates in the root page template.';
        }

		//$args = $this->request->getArguments();
		//$isDevMode = \nn\t3::Environment()->getExtConf('nnhelpers', 'devModeEnabled');

		$site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId( 1 );

// $url = $site->getRouter()->generateUri( 3, ['david'=>time()] );
// \nn\t3::debug($url);
		
// $url = $site->getRouter()->generateUri( 3, ['controller'=>'controller'.time(), 'action'=>'action', 'uid'=>'1', 'param1'=>'2', 'param2'=>'3'] );
// \nn\t3::debug($url);
		$this->view->assignMultiple([
			'test' 	    => 123,
			'settings' 	=> $settings,
            'link'      => [
                \nn\rest::Api()->uri(['controller'=>'controller'.time(), 'action'=>'action', 'uid'=>'1', 'param1'=>'2', 'param2'=>'3']),
                \nn\rest::Api()->uri(['controller'=>'controller'.time(), 'action'=>'action', 'uid'=>'1', 'param1'=>'2' ]),
                \nn\rest::Api()->uri(['controller'=>'controller'.time(), 'action'=>'action', 'uid'=>'1' ]),
                \nn\rest::Api()->uri(['controller'=>'controller'.time(), 'action'=>'action' ]),
                \nn\rest::Api()->uri(['controller'=>'controller' ]),
                \nn\rest::Api()->uri([]),

                \nn\rest::Api()->uri(['ext'=>'nnrestapi', 'controller'=>'controller'.time(), 'action'=>'action', 'uid'=>'1', 'param1'=>'2', 'param2'=>'3']),
                \nn\rest::Api()->uri(['ext'=>'nnrestapi', 'controller'=>'controller'.time(), 'action'=>'action', 'uid'=>'1', 'param1'=>'2' ]),
                \nn\rest::Api()->uri(['ext'=>'nnrestapi', 'controller'=>'controller'.time(), 'action'=>'action', 'uid'=>'1' ]),
                \nn\rest::Api()->uri(['ext'=>'nnrestapi', 'controller'=>'controller'.time(), 'action'=>'action' ]),
                \nn\rest::Api()->uri(['ext'=>'nnrestapi', 'controller'=>'controller' ]),
                \nn\rest::Api()->uri([]),

				\nn\rest::Api('nnrestapi')->uri(['controller'=>'controller'.time(), 'action'=>'action', 'uid'=>'1', 'param1'=>'2', 'param2'=>'3']),
                \nn\rest::Api('nnrestapi')->uri(['controller'=>'controller'.time(), 'action'=>'action', 'uid'=>'1', 'param1'=>'2' ]),
                \nn\rest::Api('nnrestapi')->uri(['controller'=>'controller'.time(), 'action'=>'action', 'uid'=>'1' ]),
                \nn\rest::Api('nnrestapi')->uri(['controller'=>'controller'.time(), 'action'=>'action' ]),
                \nn\rest::Api('nnrestapi')->uri(['controller'=>'controller' ]),
                \nn\rest::Api('nnrestapi')->uri([]),
            ]
		]);

		return $this->view->render();
	}

}
