<?php

namespace Nng\Nnrestapi\Middleware;

use Nng\Nnrestapi\Mvc\Response;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Context\Context;

/**
 * 
 * 
 */
class NnrestapiAuthenticator implements MiddlewareInterface {
	
	/**
     * @var Context
     */
    protected $context;

    public function __construct(Context $context) {
        $this->context = $context;
    }

	/**
	 * 
	 *	@param ServerRequestInterface $request
	 *	@param RequestHandlerInterface $handler
	 *	@return ResponseInterface
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		

		$frontendUser = GeneralUtility::makeInstance(FrontendUserAuthentication::class);

        // Authenticate now
        $frontendUser->start();
        $frontendUser->unpack_uc();
        $frontendUser->fetchGroupData();

        $userAspect = $frontendUser->createUserAspect();
        $this->context->setAspect('frontend.user', $userAspect);
        $request = $request->withAttribute('frontend.user', $frontendUser);

        if ($this->context->getAspect('frontend.user')->isLoggedIn()) {
            $rateLimiter->reset();
        }

        $response = $handler->handle($request);

        // Store session data for fe_users if it still exists
        if ($frontendUser instanceof FrontendUserAuthentication) {
            $frontendUser->storeSessionData();
        }

		\nn\t3::debug('AUTH');

        return $response;
/*
		// Falls kein Login über fe_user-Cookie passiert ist, Json Web Token (JWT) prüfen
		if (!\nn\t3::FrontendUser()->isLoggedIn()) {
			$tokenData = \Nng\Nnrestapi\Service\TokenService::getFromRequest();
			if ($tokenData['uid'] ?? false) {
				\nn\t3::FrontendUserAuthentication()->loginField( $tokenData['token'], 'nnrestapi_jwt' );
			}
		}
		
		\nn\t3::debug('AUTH');die();
		return $handler->handle($request);
*/
	}

}