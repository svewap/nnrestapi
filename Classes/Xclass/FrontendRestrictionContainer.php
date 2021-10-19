<?php

namespace Nng\Nnrestapi\Xclass;

use TYPO3\CMS\Core\Database\Query\Restriction\DefaultRestrictionContainer as BaseRestrictionContainer;

/**
 * 
 */
class FrontendRestrictionContainer extends BaseRestrictionContainer {

    /**
     * Default restriction classes.
     *
     * @var string[]
     */
    protected $defaultRestrictionTypes = [
        //\TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction::class,
        //\TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction::class,
        //\TYPO3\CMS\Core\Database\Query\Restriction\StartTimeRestriction::class,
        //\TYPO3\CMS\Core\Database\Query\Restriction\EndTimeRestriction::class
    ];

    public function __construct() {

    }
}