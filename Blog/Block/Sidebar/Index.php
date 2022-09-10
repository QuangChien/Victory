<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Block\Sidebar;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Victory\Blog\Model\ResourceModel\Post\CollectionFactory as PostCollection;
use Victory\Blog\Model\ResourceModel\Category\CollectionFactory as CategoryCollection;
use Victory\Blog\Block\AbstractBlog;
use Magento\Framework\View\Asset\Repository;
use Victory\Blog\Model\Config;

class Index extends AbstractBlog
{
    /**
     * layout current name
     */
    const BLOG_POST_VIEW_LAYOUT = 'blog_post_view';

    /**
     * @var PostCollection
     */
    protected $_postCollection;

    /**
     * @var CategoryCollection
     */
    protected $_categoryCollection;

    /**
     * @var Repository
     */
    protected $_moduleAssetDir;

    /**
     * @var Config
     */
    protected $_config;

    /**
     * @param Context $context
     * @param PostCollection $postCollection
     * @param CategoryCollection $categoryCollection
     * @param StoreManagerInterface $storeManager
     * @param Repository $moduleAssetDir
     * @param UrlInterface $urlInterface
     * @param Config $config
     */
    public function __construct(
        Context               $context,
        PostCollection        $postCollection,
        CategoryCollection    $categoryCollection,
        StoreManagerInterface $storeManager,
        Repository            $moduleAssetDir,
        UrlInterface          $urlInterface,
        Config                $config
    )
    {
        $this->_postCollection = $postCollection;
        $this->_categoryCollection = $categoryCollection;
        $this->_moduleAssetDir = $moduleAssetDir;
        $this->_config = $config;

        parent::__construct($context, $storeManager, $urlInterface);
    }

    /**
     * @return \Victory\Blog\Model\ResourceModel\Category\Collection
     */
    public function getCategories()
    {
        $categories = $this->_categoryCollection->create()
            ->addFieldToFilter('is_active', array('where' => 1));
        return $categories;
    }

    /**
     * @return mixed
     */
    public function getLayoutName()
    {
        return $this->getRequest()->getFullActionName();
    }

    /**
     * @return bool
     */
    public function isPostItemPage()
    {
        $layoutName = $this->getRequest()->getFullActionName();
        if ($layoutName == self::BLOG_POST_VIEW_LAYOUT) {
            return true;
        }
        return false;
    }

    /**
     * @return \Victory\Blog\Model\ResourceModel\Post\Collection
     */
    public function getRecentPosts()
    {
        $date = date("Y-m-d", time());
        $posts = $this->_postCollection->create();

        $posts->addFieldToFilter('publish_time', array('lteq' => $date))
            ->addFieldToFilter('main_table.is_active', array('eq' => 1))
            ->filterStore($this->getStoreId())
            ->setOrder('publish_time', 'DESC');
        if ($this->isPostItemPage()) {
            $posts->addFieldToFilter('main_table.post_id', ['nin' => $this->getRequest()->getParam('id')]);
        }
        if(!empty(Config::XML_PATH_SIDEBAR_TOTAL_POST)) {
            $posts->setPageSize((int)Config::XML_PATH_SIDEBAR_TOTAL_POST);
        }else{
            $posts->setPageSize(10);
        }

        return $posts;
    }

    /**
     * @param $path
     * @return string
     */
    public function generateSubMenu($path)
    {
        $string = '';
        $categories = $this->_categoryCollection->create()->addFieldToFilter('path', array('eq' => $path))
            ->addFieldToFilter('is_active', array('where' => 1));
        foreach ($categories as $category) {
            if ($category->getPath() == $path) {
                $string .= '<div class="sub-menu-category">
                            <div class="sub-menu-item">
                                <div class="relative">
                                    <a class="sub-menu-item-link decoration-none" href="' . $category->getCategoryUrl() .
                                    '">' . $category->getName() . '</a>
                                    <img class="category-item-icon absolute ' .
                                    $this->checkChild($category->getCategoryId()) . '" src="' .
                                    $this->_moduleAssetDir->getUrl('Victory_Blog::images/line-angle-down.png') . '"
                                         width="100%" alt="">
                                </div>' . $this->generateSubMenu($category->getCategoryId()) . '
                            </div>
                        </div>';
            }
        }
        return $string;
    }

    /**
     * @param $path
     * @return string
     */
    public function generateMenu($path = 0)
    {
        $string = '';
        $categories = $this->_categoryCollection->create()->addFieldToFilter('path', array('eq' => $path))
            ->addFieldToFilter('is_active', array('where' => 1));
        foreach ($categories as $category) {
            if ($category->getPath() == $path) {
                $string .= '
                <div class="category-item">
                        <div class="category-item-title">
                            <a href="' . $category->getCategoryUrl() . '" class="category-item-link decoration-none">' . $category->getName() . '</a>
                            <img class="category-item-icon ' . $this->checkChild($category->getCategoryId()) . '" src="' . $this->_moduleAssetDir->getUrl('Victory_Blog::images/line-angle-down.png') . '"
                                 width="100%" alt="">
                        </div>' . $this->generateSubMenu($category->getCategoryId()) . '
                    </div>
                ';
            }
        }
        return $string;
    }

    /**
     * @param $parentId
     * @return string
     */
    public function checkChild($parentId)
    {
        $categories = $this->_categoryCollection->create()->addFieldToFilter('path', array('like' => $parentId))
            ->addFieldToFilter('is_active', array('where' => 1));
        if ($categories->count() > 0) {
            return '';
        }
        return 'none';
    }
}
