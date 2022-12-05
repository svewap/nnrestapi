<?php

namespace Nng\Nnrestapi\Controller;

use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Backend Module
 * 
 */
class ModController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController 
{
    /**
	 * Backend Template Container
	 * 
	 * @var string
	 */
	protected $defaultViewObjectName = \TYPO3\CMS\Backend\View\BackendTemplateView::class;

	/**
	 * Support this project!
	 * 
	 * @var array
	 */
	protected $donations = [
		['icon' => 'fas fa-rainbow', 'title'=>'Kaaarma!', 'content'=>'Karma'],
		['icon' => 'fas fa-cookie', 'title'=>'Gimme Cookie!', 'content'=>'Cookie'],
		['icon' => 'fas fa-coffee', 'title'=>'Coffee!', 'content'=>'Coffee'],
		['icon' => 'fas fa-hand-paper', 'title'=>'DON\'t donate!', 'content'=>'Dont'],
	];

	/**
	 * 	Initialize View
	 * 
	 */
	public function initializeView ( ViewInterface $view ) 
	{
		parent::initializeView($view);

		if (!$view->getModuleTemplate()) return;
		
		$pageRenderer = $view->getModuleTemplate()->getPageRenderer();

		if (\nn\t3::t3Version() < 11) {
			$pageRenderer->loadRequireJsModule('TYPO3/CMS/Nnrestapi/Bootstrap');
		}
		$pageRenderer->loadRequireJsModule('TYPO3/CMS/Nnrestapi/Axios');
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/Nnrestapi/Nnrestapi');
		
		$pageRenderer->addCssFile('/typo3conf/ext/nnhelpers/Resources/Public/Vendor/prism/prism.css');
		$pageRenderer->addJsFile('/typo3conf/ext/nnhelpers/Resources/Public/Vendor/prism/prism.js');

        $template = $view->getModuleTemplate();
        $template->setFlashMessageQueue($this->controllerContext->getFlashMessageQueue());
        $template->getDocHeaderComponent()->disable();
	}

	/**
	 * The main backend view with overview of all API endpoints
	 * and integrated testbed.
	 * 
	 * @return void
	 */
	public function indexAction () 
	{
		// Make sure site config.yaml is loaded and parsed in Settings
		\nn\rest::Settings()->initialize();

		// Collect errors...
		$errors = [];

		// PreCheck can be disabled in the Extension Manager
		$checkErrors = !\nn\rest::Settings()->getExtConf('disablePreCheck');

		if ($checkErrors) {
			// Check, if database-tables were installed
			$tablesExist = \nn\rest::Environment()->sessionTableExists();
			if (!$tablesExist) {
				$errors['missingTables'] = true;
			}

			// Check, if TypoScript template was included
			$settings = \nn\t3::Settings()->getPlugin('nnrestapi');
			if (!$settings) {
				$errors['missingTypoScript'] = true;
			}

			// Check, if RouteEnhancer was included
			$config = \nn\rest::Settings()->getConfiguration();
			if (!$config) {
				$errors['missingYaml'] = true;
			}
		
			// Any errors? Then abort here.
			if ($errors) {
				return \nn\t3::Template()->render('EXT:nnrestapi/Resources/Private/Backend/Templates/Mod/Error.html', ['errors'=>$errors]);
			}
		}
		
		// Everything fine. Render documentation.
		$classMap = \nn\rest::Annotations()->getClassMapWithDocumentation();
		$urlBase = \nn\t3::Environment()->getBaseUrl();

		// Get a random donation message
		$donation = $this->donations[ rand(0, count($this->donations)-1) ];

		$this->view->assignMultiple([
			'enhancerExists'	=> $checkErrors ? \Nng\Nnrestapi\Service\EnvironmentService::enhancerExists() : true,
			'rewriteCondExists'	=> $checkErrors ? \Nng\Nnrestapi\Service\EnvironmentService::rewriteCondExists() : true,
			'feUser'			=> \nn\t3::FrontendUser()->get(),
			'urlBase'			=> $urlBase,
			'absApiUrlPrefix'	=> $urlBase . \nn\rest::Settings()->getApiUrlPrefix(),
			'endpoints' 		=> $classMap,
			'donate'			=> $donation,
			'extConf'			=> \nn\t3::Environment()->getExtConf('nnrestapi')
		]);

		return $this->view->render();
	}

	/**
	 * Get `README.md` for given kickstart-template
	 * 
	 * @param string $identfier
	 * @return string
	 */
	public function readmeAction( string $identifier = '' ) 
	{
		$config = $this->settings['kickstarts'][$identifier] ?? false;
		
		if (!$config || !($config['path'] ?? false)) {
			return 'Kickstart config not defined or no path to kickstarter template set!';
		}

		return \nn\rest::Kickstart()->getReadMe( $config ) ?: 'This package has no README.md';
	}

	/**
	 * Export Kickstarter-Templates
	 * 
	 * @param string $identfier
	 * @param string $extname
	 * @param string $vendorname
	 * @return string
	 */
	public function kickstartAction( string $identifier = '', string $extname = '', string $vendorname = '' ) 
	{
		$config = $this->settings['kickstarts'][$identifier] ?? false;
		
		if (!$config || !($config['path'] ?? false)) {
			return 'Kickstart config not defined or no path to kickstarter template set!';
		}

		$dirName = dirname($config['path']) . '/' . basename($config['path'], '.zip');
		$absPath = \nn\t3::File()->exists($config['path']) ?: \nn\t3::File()->exists($dirName);

		// basic check
		if (!$absPath) {
			return 'Path to kickstarter template invalid.';			
		}

		// Make sure the path is somewhere inside the EXT: or fileadmin folder. Prevents misuse.
		$pathSite = \nn\t3::Environment()->getPathSite();
		$allowedPaths = array_filter([
			$pathSite . 'typo3conf/ext/',
			$pathSite . 'fileadmin/',
		], function ($path) use ($absPath) {
			return strpos($absPath, $path) !== false;
		});

		if (!$allowedPaths) {
			return 'Path to kickstarter template must be in EXT or fileadmin-folder!';
		}

		$extname = GeneralUtility::camelCaseToLowerCaseUnderscored($extname);
		$vendorname = GeneralUtility::camelCaseToLowerCaseUnderscored($vendorname);

		$placeholder = [
			'ext-ucc' 		=> GeneralUtility::underscoredToUpperCamelCase($extname),
			'ext-lower' 	=> $extname,
			'vendor-ucc' 	=> GeneralUtility::underscoredToUpperCamelCase($vendorname),
			'vendor-lower' 	=> $vendorname,
		];

		$marker = [];
		foreach ($placeholder as $k=>$v) {
			$marker["[#{$k}#]"] = $v;
		}

		if ($replace = $config['replace'] ?? false) {
			foreach ($replace as $k=>$v) {

				// enable escaping keys for replacement (e.g. \x22test\x22 => "test")
				$k = preg_replace_callback( "(\\\\x([0-9a-f]{2}))i", function ($a) {
					return chr(hexdec($a[1]));
				}, $k);

				// replace placeholders
				$v = strtr( $v, $marker );
				$marker[$k] = $v;
			}
		}

		$config['path'] = $absPath;
		\nn\rest::Kickstart()->createExtensionFromTemplate( $config, $marker );

		return '';
	}
}