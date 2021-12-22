<?php
namespace Nng\Nnrestapi\Domain\Model;

/**
 * A base model that can be extended in your own extensions.
 * 
 * ```
 * class MyModel extends \Nng\Nnrestapi\Domain\Model\AbstractRestApiModel {}
 * ```
 * 
 */
class AbstractRestApiModel extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
	/**
	 * @var int
	 */
	protected $tstamp;

	/**
	 * @var int
	 */
	protected $crdate;

	/**
	 * @var int
	 */
	protected $pid;
	
	/**
	 * @return  int
	 */
	public function getTstamp() {
		return $this->tstamp;
	}

	/**
	 * @param   int  $tstamp  
	 * @return  self
	 */
	public function setTstamp($tstamp) {
		$this->tstamp = $tstamp;
		return $this;
	}

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

	/**
	 * @return  int
	 */
	public function getCrdate() {
		return $this->crdate;
	}

	/**
	 * @param   int  $crdate  
	 * @return  self
	 */
	public function setCrdate($crdate) {
		$this->crdate = $crdate;
		return $this;
	}
}
