<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Category Model
 */
class Category extends AbstractModel
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * @var string
     */
    protected $controllerName;

    public function __construct(
        Context          $context,
        Registry         $registry,
        Url              $url,
        AbstractResource $resource = null,
        AbstractDb       $resourceCollection = null,
        array            $data = []
    )
    {
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
        $this->_init('Victory\Blog\Model\ResourceModel\Category');
        $this->controllerName = URL::CONTROLLER_CATEGORY;
    }

    /**
     * Retrieve model title
     * @param boolean $plural
     * @return string
     */
    public function getOwnTitle($plural = false)
    {
        return $plural ? 'Categories' : 'Categories';
    }

    /**
     * Retrieve parent category id
     *
     * @return int|array
     */
    public function getParentId()
    {
        $parentIds = $this->getParentIds();
        if ($parentIds) {
            return $parentIds[count($parentIds) - 1];
        }

        return 0;
    }

    /**
     * Retrieve parent category ids
     * @return array
     */
    public function getParentIds()
    {
        $k = 'parent_ids';
        if (!$this->hasData($k)) {
            $this->setData(
                $k,
                $this->getPath() ? explode('/', $this->getPath()) : []
            );
        }

        return $this->getData($k);
    }

    /**
     * Check if category url key exist for specific store
     * return category id if category exists
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
            && (null === $storeId || array_intersect([0, $storeId], $this->getStoreIds()));
    }

    /**
     * Retrieve category url
     * @return string
     */
    public function getCategoryUrl()
    {
        if (!$this->hasData('category_url')) {
            $url = $this->_url->getUrl($this, $this->controllerName);
            $this->setData('category_url', $url);
        }
        return $this->getData('category_url');
    }
}
