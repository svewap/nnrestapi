<?php

namespace Nng\Nnrestapi\Annotations;

/**
 * ## Api\Route
 * 
 * Define a custom URI to an endpoint by using this annotation.
 * 
 * Examples for `@Api\Route(...)`:
 * ```
 * @Api\Route("/test/demo")
 * @Api\Route("/test/demo/{uid}") 
 * @Api\Route("/test/demo/{uid?}")
 * @Api\Route("/test/demo/{uid}/{test}")
 * @Api\Route("/test/demo/{uid?}/{test?}")
 * @Api\Route("GET /test/demo/something")
 * @Api\Route("GET|POST|PUT /test/demo/something")
 * @Api\Route("GET /auth/log_me_out/{uid}/{something}")
 * ```
 * 
 * @Annotation
 */
class Route
{
	public $value;

	public $route;

	public $regex;

	public $arguments;

	public $reqTypes;

	public function __construct( $arr ) {
		$this->value = $arr['value'];
		$this->parse();
	}

	public function parse() {
		$supportedMethodPrefixes = \Nng\Nnrestapi\Utilities\Endpoint::SUPPORTED_METHOD_PREFIXES;

		// The RegEx used for parsing the `@Api\Route("...")` annotations
		$routeAnnotationRegex = '/((' . join('\|?|', $supportedMethodPrefixes) . ')*)\s*\/*(.*)/i';

		// `@Api\Route("...")` was defined in annotation
		preg_match($routeAnnotationRegex, $this->value, $matches);

		// get`, `post`... from `@Api\Route GET|POST ...`
		$this->reqTypes = $matches[1] ? \nn\t3::Arrays(strtolower($matches[1]))->trimExplode('|') : ['get'];

		$route = $matches[3];
		$this->route = $route;

		// `path/to/{uid?}/{test?}` => `path/to[/]?([^/]*)[/]?([^/]*)`
		$pattern = preg_replace('/\/\{[^\?\}]*\?\}/i', '[/]*([^/]*)', $route);

		// `path/to/{uid}/{test}` => `path/to/([^\/]*)/([^\/]*)`
		$pattern = preg_replace('/\{[^\}]*\}/i', '([^/]*)', $pattern);

		$this->regex = '/(.*)\/' . str_replace('/', '\/', $pattern) . '$/i';

		// Argumente ermitteln
		preg_match_all( '/\{([^\?\}]*)[\?]*\}/i', $route, $matches );
		$arguments = $matches[1] ?? [];
		$this->arguments = array_combine( $arguments, array_fill(0, count($arguments), '') );
		
	}

	public function mergeDataForEndpoint ( &$data ) {
		$data['route'] = [
			'path' 		=> $this->route,
			'match' 	=> $this->regex,
			'arguments' => $this->arguments,
			'reqTypes' 	=> $this->reqTypes,
		];
	}
}