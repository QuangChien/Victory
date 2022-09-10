<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManager;
use Victory\Blog\Model\Config;

class Topmenu implements ObserverInterface
{
    /**
     * @var StoreManager
     */
    protected $_storeManager;

    /**
     * @var Config
     */
    protected $_config;

    public function __construct(
        StoreManager $storeManager,
        Config       $config
    )
    {
        $this->_config = $config;
        $this->_storeManager = $storeManager;
    }

    /**
     * @param EventObserver $observer
     * @return $this|void
     */
    public function execute(EventObserver $observer)
    {
//        var_dump(Config::XML_PATH_EXTENSION_ENABLED);die();
        if($this->_config->getConfig(Config::XML_PATH_EXTENSION_ENABLED)) {
            $menu = $observer->getMenu();
            $tree = $menu->getTree();
            $data = [
                'name' => __('Blog'),
                'id' => 'blog',
                'url' => $this->getUrlBlogMenu(),
                'is_active' => false
            ];
            $node = new Node($data, 'id', $tree, $menu);
            $menu->addChild($node);
        }

        return $this;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * @return string
     */
    public function getBlogRouteName()
    {
        return (string) $this->_config->getConfig(Config::XML_PATH_BLOG_ROUTE);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getUrlBlogMenu()
    {
        return $this->getBaseUrl() . $this->getBlogRouteName();
    }
}
