<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Block\Category;

use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Victory\Blog\Model\ResourceModel\Post\CollectionFactory as PostCollection;
use Victory\Blog\Model\CategoryFactory;
use Victory\Blog\Block\AbstractBlog;
use Victory\Blog\Model\Config;

class View extends AbstractBlog
{
    /**
     * @var PostCollection
     */
    protected $_postCollection;

    /**
     * @var CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var Config
     */
    protected $_config;

    /**
     * @param Context $context
     * @param PostCollection $postCollection
     * @param CategoryFactory $categoryFactory
     * @param StoreManagerInterface $storeManager
     * @param Registry $registry
     * @param UrlInterface $urlInterface
     * @param Config $config
     */
    public function __construct(
        Context               $context,
        PostCollection        $postCollection,
        CategoryFactory       $categoryFactory,
        StoreManagerInterface $storeManager,
        Registry              $registry,
        UrlInterface          $urlInterface,
        Config                $config
    )
    {
        $this->_postCollection = $postCollection;
        $this->_categoryFactory = $categoryFactory;
        $this->_registry = $registry;
        $this->_config = $config;
        parent::__construct($context, $storeManager, $urlInterface);
    }

    /**
     * @return string
     */
    public function getBlogRouteName()
    {
        return (string) $this->_config->getConfig(Config::XML_PATH_BLOG_ROUTE);
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _addBreadcrumbs()
    {
        $isShowBreadcrumbs = 1;
        if ($isShowBreadcrumbs && ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs'))) {
            $breadcrumbsBlock->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            );
            $breadcrumbsBlock->addCrumb(
                'blog',
                [
                    'label' => 'Blog',
                    'title' => 'Blog'
                ]
            );
            $breadcrumbsBlock->addCrumb(
                'post',
                [
                    'label' => 'Post',
                    'title' => 'Post',
                    'link' => $this->_storeManager->getStore()->getBaseUrl(). $this->getBlogRouteName()
                ]
            );
            $breadcrumbsBlock->addCrumb(
                'item',
                [
                    'label' => $this->getCategory()->getName(),
                    'title' => $this->getCategory()->getName()
                ]
            );
        }
    }

    /**
     * @return $this|View
     */
    protected function _prepareLayout()
    {
        $this->_addBreadcrumbs();
        $this->pageConfig->getTitle()->set($this->getCategory()->getMetaTitle());
        $this->pageConfig->setKeywords($this->getCategory()->getMetaKeywords());
        $this->pageConfig->setDescription($this->getCategory()->getMetaDescription());
        parent::_prepareLayout();
        $page_size = $this->getPagerCount();
        $page_data = $this->getPosts();
        if ($this->getPosts()) {
            $blockName = 'category_page';
            if ($this->getLayout()->getBlock($blockName)) {
                $pager = $this->getLayout()->getBlock($blockName)
                    ->setAvailableLimit($page_size)
                    ->setShowPerPage(true)->setCollection(
                        $this->getPosts()
                    );
            } else {
                $pager = $this->getLayout()->createBlock(
                    'Magento\Theme\Block\Html\Pager',
                    $blockName
                )->setAvailableLimit($page_size)
                    ->setShowPerPage(true)->setCollection(
                        $this->getPosts()
                    );
            }
            $this->setChild('pager', $pager);
            $this->getPosts()->load();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return mixed|null
     */
    public function getId()
    {
        return $this->_registry->registry('current_blog_category_id');
    }

    /**
     * @return \Victory\Blog\Model\ResourceModel\Post\Collection
     */
    public function getPosts()
    {
        $date = date("Y-m-d", time());
        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        $pageSize = ($this->getRequest()->getParam('limit'))
            ? $this->getRequest()->getParam('limit') :
            $this->_config->getConfig(Config::XML_PATH_POST_PER_PAGE);

        $posts = $this->_postCollection->create();

        $posts->addFieldToFilter('publish_time', array('lteq' => $date))
            ->addFieldToFilter('main_table.is_active', array('eq' => 1))
            ->joinWithAuthor()
            ->getPostsByCategory($this->getId())
            ->filterStore($this->getStoreId())
            ->setOrder('main_table.post_id', 'DESC')
            ->setPageSize($pageSize)->setCurPage($page);
        return $posts;
    }

    /**
     * @return array
     */
    public function getPagerCount()
    {
        // get collection
        $minimum_show = $this->_config->getConfig(Config::XML_PATH_POST_PER_PAGE);
        $page_array = [];
        $list_data = $this->_postCollection->create();
        $list_count = ceil(count($list_data->getData()));
        $show_count = $minimum_show + 1;
        if (count($list_data->getData()) >= $show_count) {
            $list_count = $list_count / $minimum_show;
            $page_nu = $total = $minimum_show;
            $page_array[$minimum_show] = $minimum_show;
            for ($x = 0; $x <= $list_count; $x++) {
                $total = $total + $page_nu;
                $page_array[$total] = $total;
            }
        } else {
            $page_array[$minimum_show] = $minimum_show;
            $minimum_show = $minimum_show + $minimum_show;
            $page_array[$minimum_show] = $minimum_show;
        }
        return $page_array;
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

    /**
     * @return string
     */
    public function getCategoryDes()
    {
        $this->check();
        return (string)$this->_categoryFactory->create()->load($this->getId())->getDescription();
    }

    /**
     * @return \Victory\Blog\Model\Category
     */
    public function getCategory()
    {
        return $this->_categoryFactory->create()->load($this->getId());
    }

    /**
     * @return string
     */
    public function getCategoryName()
    {
        return (string)$this->getCategory()->getName();
    }

    /**
     * @return mixed
     */
    public function check()
    {
        return (string)$this->getCategory()->isVisibleOnStore(1);
    }
}
