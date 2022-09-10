<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Model;

use Magento\Framework\Model\AbstractModel;
use Victory\Blog\Model\AuthorFactory;
use Victory\Blog\Model\ResourceModel\Category\CollectionFactory;
use Victory\Blog\Model\ResourceModel\Comment\CollectionFactory as CommentCollection;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\ResourceModel\AbstractResource;

/**
 * Post Model
 */
class Post extends AbstractModel
{
    /**
     * @var AuthorFactory
     */
    protected $_authorFactory;

    /**
     * @var CollectionFactory
     */
    protected $_categoryCollection;

    /**
     * @var CommentCollection
     */
    protected $_commentCollection;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * @var string
     */
    protected $controllerName;

    public function __construct(
        Context           $context,
        Registry          $registry,
        AbstractResource  $resource = null,
        AbstractDb        $resourceCollection = null,
        array             $data = [],
        AuthorFactory     $authorFactory,
        CollectionFactory $categoryCollection,
        CommentCollection $commentCollection,
        Url               $url
    )
    {

        $this->_authorFactory = $authorFactory;
        $this->_categoryCollection = $categoryCollection;
        $this->_commentCollection = $commentCollection;
        $this->_url = $url;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Victory\Blog\Model\ResourceModel\Post');
        $this->controllerName = URL::CONTROLLER_POST;
    }

    /**
     * Retrieve model title
     * @param boolean $plural
     * @return string
     */
    public function getOwnTitle($plural = false)
    {
        return $plural ? 'Posts' : 'Post';
    }

    /**
     * Check if post url key exist for specific store
     * return post id if post exists
     *
     * @param string $urlKey
     * @param int $storeId
     * @return int
     */
    public function checkUrlKey($urlKey, $storeId)
    {
        return $this->_getResource()->checkUrlKey($urlKey, $storeId);
    }

    /**
     * Retrieve if is visible on store
     * @return bool
     */
    public function isVisibleOnStore($storeId)
    {
        return $this->getIsActive()
            && $this->getData('publish_time') <= $this->getResource()->getDate()->gmtDate()
            && (null === $storeId || array_intersect([0, $storeId], $this->getStoreIds()));
    }


    /**
     * @return \Victory\Blog\Model\Author
     */
    public function getAuthor()
    {
        if (!$this->hasData('author')) {
            $author = false;
            if ($authorId = $this->getData('author_id')) {
                $_author = $this->_authorFactory->create();
                $_author->load($authorId);
                if ($_author->getUserId()) {
                    $author = $_author;
                }
            }
            $this->setData('author', $author);
        }
        return $this->getData('author');
    }

    /**
     * Retrieve post parent categories
     * @return \Victory\Blog\Model\ResourceModel\Category\Collection
     */
    public function getParentCategories()
    {
        if (null === $this->_parentCategories) {
            $this->_parentCategories = $this->_categoryCollectionFactory->create()
                ->addFieldToFilter('category_id', ['in' => $this->getCategories()])
                ->addStoreFilter($this->getStoreId())
                ->addActiveFilter()
                ->setOrder('position');
        }

        return $this->_parentCategories;
    }


    /**
     * @return ResourceModel\Category\Collection
     */
    public function getCategories()
    {
        $categories = $this->_categoryCollection->create()
            ->addFieldToFilter('is_active', ['where' => 1])
            ->filterCategories($this->getData('post_id'));
        return $categories;
    }

    /**
     * @return array
     */
    public function getCategoryIds()
    {
        return $this->getCategories()->getAllIds();
    }

    /**
     * @return int|null
     */
    public function getCommentCount()
    {
        $comments = $this->_commentCollection->create()
            ->addFieldToFilter('post_id', ['where' => $this->getData('post_id')])
            ->addFieldToFilter('status', ['where', 1]);
        return count($comments);
    }

    /**
     * @return \Victory\Blog\Model\ResourceModel\Comment\Collection
     */
    public function getComments()
    {
        if (!$this->hasData('comment')) {
            $comments = $this->_commentCollection->create()
                ->addFieldToFilter('post_id', ['where' => $this->getData('post_id')])
                ->addFieldToFilter('status', ['where', 1]);
            $this->setData('comments', $comments);
        }
        return $this->getData('comments');
    }

    /**
     * Retrieve post url path
     * @return string
     */
    public function getUrl()
    {
        return $this->_url->getUrlPath($this, $this->controllerName);
    }

    /**
     * Retrieve post url
     * @return string
     */
    public function getPostUrl()
    {
        if (!$this->hasData('post_url')) {
            $url = $this->_url->getUrl($this, $this->controllerName);
            $this->setData('post_url', $url);
        }

        return $this->getData('post_url');
    }
}
