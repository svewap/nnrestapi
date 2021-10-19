<?php
namespace Nng\Nnrestapi\Domain\Model;

use \TYPO3\CMS\Extbase\Domain\Model\FileReference;

/**
 * A simple Model to test things with.
 * 
 */
class ApiTest extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * @var bool
     */
    protected $hidden;

    /**
     * title
     *
     * @var int
     */
    protected $uid;
   
	/**
     * title
     *
     * @var string
     */
    protected $title = '';

	/**
     * image
     *
     * @var \TYPO3\CMS\Extbase\Domain\Model\FileReference
     */
    protected $image;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     */
    protected $files;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Nng\Nnrestapi\Domain\Model\ApiTest>
     */
    protected $children;

	/**
     * @var \Nng\Nnrestapi\Domain\Model\ApiTest
     */
    protected $child;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\Category>
     */
    protected $categories;

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
		$this->categories = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
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

	/**
	 * @param   int  $uid  title
	 * @return  self
	 */
	public function setUid($uid) {
		$this->uid = $uid;
		return $this;
	}

	/**
	 * @return  \TYPO3\CMS\Extbase\Domain\Model\FileReference
	 */
	public function getImage() {
		return $this->image;
	}

	/**
	 * @param   \TYPO3\CMS\Extbase\Domain\Model\FileReference  $image  image
	 * @return  self
	 */
	public function setImage($image) {
		$this->image = $image;
		return $this;
	}

	/**
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Nng\Nnrestapi\Domain\Model\ApiTest>
	 */
	public function getChildren() {
		return $this->children;
	}

	/**
	 * @param  \TYPO3\CMS\Extbase\Persistence\ObjectStorage  $children  
	 * @return  self
	 */
	public function setChildren($children) {
		$this->children = $children;
		return $this;
	}

	/**
	 * @return  \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\Category>
	 */
	public function getCategories() {
		return $this->categories;
	}

	/**
	 * @param   \TYPO3\CMS\Extbase\Persistence\ObjectStorage $categories  
	 * @return  self
	 */
	public function setCategories($categories) {
		$this->categories = $categories;
		return $this;
	}

	/**
	 * @return  \Nng\Nnrestapi\Domain\Model\ApiTest
	 */
	public function getChild() {
		return $this->child;
	}

	/**
	 * @param   \Nng\Nnrestapi\Domain\Model\ApiTest  $child  
	 * @return  self
	 */
	public function setChild($child) {
		$this->child = $child;
		return $this;
	}

	/**
	 * @return  bool
	 */
	public function getHidden() {
		return $this->hidden;
	}

	/**
	 * @param   bool  $hidden  
	 * @return  self
	 */
	public function setHidden($hidden) {
		$this->hidden = $hidden;
		return $this;
	}
}
