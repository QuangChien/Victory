<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;

class AbstractBlog extends Template
{
    /**
     * folder feature image
     */
    const PATH_FOLDER_IMAGE = 'catalog/tmp/category/';

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var UrlInterface
     */
    protected $_urlInterface;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context               $context,
        StoreManagerInterface $storeManager,
        UrlInterface          $urlInterface
    )
    {
        $this->_storeManager = $storeManager;
        $this->_urlInterface = $urlInterface;
        parent::__construct($context);
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        return $storeId;
    }

    /**
     * @param $imageName
     * @return string
     */
    public function getPathImage($imageName)
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
            . self::PATH_FOLDER_IMAGE . $imageName;
    }

}
