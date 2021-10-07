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

		$pageRenderer->loadRequireJsModule('TYPO3/CMS/Nnrestapi/Axios');
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/Nnrestapi/Nnrestapi');
		
		$pageRenderer->addCssFile('typo3conf/ext/nnhelpers/Resources/Public/Vendor/prism/prism.css');

		$pageRenderer->addJsFile('typo3conf/ext/nnrestapi/Resources/Public/Vendor/axios.min.js');
		$pageRenderer->addJsFile('typo3conf/ext/nnhelpers/Resources/Public/Vendor/prism/prism.js');

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
			\nn\t3::Message()->ERROR('Where\'s my TypoScript?', 'No TypoScript Configuration found. Make sure you included the RestApi templates in the root page template.');
			return \nn\t3::Template()->render('EXT:nnrestapi/Resources/Private/Backend/Templates/Mod/Error.html');
		}
		

		$classMap = \nn\rest::Annotations()->getClassMapWithDocumentation();
		$urlBase = \nn\t3::Environment()->getBaseUrl();

		$this->view->assignMultiple([
			'enhancerExists'	=> \Nng\Nnrestapi\Service\EnvironmentService::enhancerExists(),
			'rewriteCondExists'	=> \Nng\Nnrestapi\Service\EnvironmentService::rewriteCondExists(),
			'feUser'			=> \nn\t3::FrontendUser()->get(),
			'urlBase'			=> $urlBase,
			'absApiUrlPrefix'	=> $urlBase . \nn\rest::Settings()->getApiUrlPrefix(),
			'endpoints' 		=> $classMap,
		]);

		return $this->view->render();
	}

}
