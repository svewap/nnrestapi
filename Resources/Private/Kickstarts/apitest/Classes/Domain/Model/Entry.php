<?php
namespace Nng\Apitest\Domain\Model;

use \TYPO3\CMS\Extbase\Domain\Model\FileReference;
use \Nng\Nnrestapi\Domain\Model\AbstractRestApiModel;

/**
 * A simple Model to test things with.
 * 
 */
class Entry extends AbstractRestApiModel
{
    /**
     * title
     *
     * @var string
     */
    protected $title = '';

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     */
    protected $files;

    /**
	 * constructor
     * 
	 */
	public function __construct() {
		$this->initStorageObjects();
	}
	
	/**
	 * Initializes all \TYPO3\CMS\Extbase\Persistence\ObjectStorage properties.
	 *
	 * @return void
	 */
	protected function initStorageObjects() {
		$this->files = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
	}

	/**
	 * @return  string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @param   string  $title  title
	 * @return  self
	 */
	public function setTitle($title) {
		$this->title = $title;
		return $this;
	}

    /**
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
	 */
	public function getFiles() {
		return $this->files;
	}
	
	/**
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $files
	 * @return self
	 */
	public function setFiles(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $files) {
		$this->files = $files;
        return $this;
	}

	/**
	 * @param FileReference $files
     * @return self
	 */
	public function addFiles(FileReference $files) {
		if ($this->getFiles() === NULL) {
			$this->files = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
		}
		$this->files->attach($files);
        return $this;
	}
}
