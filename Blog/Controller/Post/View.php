<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Controller\Post;

use Magento\Framework\Registry;
use Victory\Blog\App\Action\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Blog post detail
 */
class View extends Action
{
    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param Registry $registry
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context               $context,
        PageFactory           $pageFactory,
        Registry              $registry,
        StoreManagerInterface $storeManager
    )
    {
        $this->_registry = $registry;
        $this->_storeManager = $storeManager;
        $this->_pageFactory = $pageFactory;
        return parent::__construct($context);
    }

    /**
     * View blog homepage action
     */
    public function execute()
    {
        if (!$this->moduleEnabled()) {
            return $this->_forwardNoroute();
        }
        $post = $this->_initPost();
        if (!$post) {
            return $this->_forwardNoroute();
        }
        $this->_registry->register('current_post', $post);
        $post->getResource()->incrementViewsCount($post);
        return $this->_pageFactory->create();
    }

    /**
     * @return \Victory\Blog\Model\Post|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _initPost()
    {
        $id = (int)$this->getRequest()->getParam('id');
        if (!$id) {
            return $this->_forwardNoroute();
        }
        $post = $this->_objectManager->create(\Victory\Blog\Model\Post::class)->load($id);

        if (!$post->isVisibleOnStore($this->getStoreId())) {
            return $this->_forwardNoroute();
        }
        return $post;
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }
}
