<?php
namespace Nng\Nnrestapi\Domain\Model;

/**
 * This Model is only included in TYPO3 < v10
 * 
 */
class RestApiModel_v9 extends RestApiModelBase
{
	/**
	 * @var int
	 */
	protected $pid;

	/**
	 * @return  int
	 */
	public function getPid(): ?int {
		return $this->pid;
	}

	/**
	 * @param   int  $pid  
	 * @return  self
	 */
	public function setPid($pid) {
		$this->pid = $pid;
	}

}
