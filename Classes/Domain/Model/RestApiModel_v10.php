<?php
namespace Nng\Nnrestapi\Domain\Model;

/**
 * This Model is only included in TYPO3 >= 10
 * 
 */
class RestApiModel_v10 extends RestApiModelBase
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
	public function setPid(int $pid):void {
		$this->pid = $pid;
	}

}
