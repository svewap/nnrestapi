<?php
namespace Nng\Nnrestapi\Domain\Model;

/**
 * Extending from compatible Classes, due to changes from TYPO3 versions.
 * This will be removed in later versions.
 * 
 */
if (\nn\t3::t3Version() < 10) {
	abstract class AbstractRestApiModel extends RestApiModel_v9 {}
} else {
	abstract class AbstractRestApiModel extends RestApiModel_v10 {}
}
