<?php

namespace Nng\Nnrestapi\ViewHelpers\Widget;

use Nng\Nnhelpers\ViewHelpers\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Widget zur Darstellung eines Akkordeons.
 * 
 * ```
 * <rest:widget.accordion title="Titel" icon="fas fa-plus" class="nice-thing">
 *   ...
 * </rest:widget.accordion>
 * ```
 * @return string
 */
class AccordionViewHelper extends AbstractViewHelper
{

    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('template', 'string', 'Pfad zum Template', false, 'EXT:nnrestapi/Resources/Private/Backend/Partials/Accordion.html');
        $this->registerArgument('title', 'string', 'Titel des Akkordeons');
        $this->registerArgument('reqType', 'string', 'Art des Requests (get, post, ...)');
        $this->registerArgument('icon', 'string', 'Icon-Klasse');
        $this->registerArgument('class', 'string', 'Accordeon-Klasse');
        $this->registerArgument('content', 'string', 'Inhalt');
    }

    /**
     * Render everything
     *
     * @param string $title
     * @return string
     */
    public static function renderStatic( array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext ) {

        $vars = array_merge(
            $renderingContext->getVariableProvider()->getAll(), 
            $arguments, [
            'renderedChildren'  => $arguments['content'] ?: $renderChildrenClosure(),
            'uniqid'            => uniqid('acc-'),
        ]);

        return \nn\t3::Template()->render( $arguments['template'], $vars );
    }
}