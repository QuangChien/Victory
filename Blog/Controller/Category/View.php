<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Controller\Category;

use Victory\Blog\App\Action\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;

/**
 * AbstractBlog home page view
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
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        Registry $registry
    )
    {
        $this->_pageFactory = $pageFactory;
        $this->_registry = $registry;
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
        $this->_initCategory();
        return $this->_pageFactory->create();
    }

    /**
     * @return false|void
     */
    protected function _initCategory()
    {
        $id = (int)$this->getRequest()->getParam('id');
        if (!$id) {
            return $this->_forwardNoroute();
        }
        $this->_registry->register('current_blog_category_id', $id);
    }
}
