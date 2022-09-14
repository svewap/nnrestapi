<?php
namespace Nng\Nnrestapi\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use Nng\Nnhelpers\ViewHelpers\AbstractViewHelper;

/**
 * Grafische Listung der Benutzergruppen / Zugriffsrechte eines Endpoints
 *
 * ```
 * {access->rest:format.access()}
 * {rest:format.access(access:'...')}
 * ```
 *
 */
class AccessViewHelper extends AbstractViewHelper {

    protected $escapeOutput = false;

    public function initializeArguments() {
        parent::initializeArguments();
        $this->registerArgument('access', 'array', 'Array mit Zugriffsrechten', false);
    }

    public static function renderStatic( array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext ) {

        $access = $arguments['access'] ?: $renderChildrenClosure() ?: [];

        $rights = [];

        $accessNames = [
            'be_users' => 'Backend users',
            'be_admins' => 'Backend administrators',
            'api_users' => 'API users',
            'fe_users' => 'Frontend users',
            'fe_groups' => 'Frontend user groups',
            'ip' => 'IP address(es)',
            'ip_users' => 'IP address(es) or users',
        ];

        foreach ($access as $k=>$v) {
            switch ($k) {
                case 'public':
                    $rights[] = '<i class="fas fa-users"></i> public';
                    break;
                case 'be_users':
                case 'fe_users':
                case 'fe_groups':
                case 'be_admins':
                case 'api_users':
                    $rights[] = sprintf('<i class="fas fa-user-lock"></i> %s <code style="margin:0;padding:0">[%s]</code>: %s', $accessNames[$k], $k, join(',', $v));
                    break;
                case 'ip':
                case 'ip_users':
                    $rights[] = sprintf('<i class="fas fa-lock"></i> %s <code style="margin:0;padding:0">[%s]</code>: %s', $accessNames[$k], $k, join(',', $v));
                    break;
            }
        }

        if (!$rights) {
            $rights[] = '<i class="fas fa-question-circle"></i> Not defined!';
        };

        return '<ul class="access-list"><li>'.join('</li><li>', $rights).'</li></ul>';
    }

}