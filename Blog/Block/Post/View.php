<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Block\Post;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Victory\Blog\Block\AbstractBlog;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Registry;
use Magento\Customer\Model\Session;
use Victory\Blog\Model\ResourceModel\Post\CollectionFactory;

class View extends AbstractBlog
{
    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var Session
     */
    protected $_customerSession;


    /**
     * @var PostCollection
     */
    protected $_postCollection;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param Registry $registry
     * @param UrlInterface $urlInterface
     * @param Session $session
     * @param CollectionFactory $postCollection
     */
    public function __construct(
        Context               $context,
        StoreManagerInterface $storeManager,
        Registry              $registry,
        UrlInterface          $urlInterface,
        Session               $session,
        CollectionFactory     $postCollection

    )
    {
        $this->_registry = $registry;
        $this->_customerSession = $session;
        $this->_postCollection = $postCollection;
        parent::__construct($context, $storeManager, $urlInterface);
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
                    'link' => trim($this->getUrl('blog/posts.html'), '/')
                ]
            );
            $breadcrumbsBlock->addCrumb(
                'item',
                [
                    'label' => $this->getPost()->getTitle(),
                    'title' => $this->getPost()->getTitle()
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
        $this->pageConfig->getTitle()->set($this->getPost()->getMetaTitle());
        $this->pageConfig->setKeywords($this->getPost()->getMetaKeywords());
        $this->pageConfig->setDescription($this->getPost()->getMetaDescription());
        return $this;
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->_customerSession->isLoggedIn();
    }

    /**
     * @return mixed|null
     */
    public function getPost()
    {
        $post = $this->_registry->registry('current_post');
        return $post;
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
    public function getUrlCurrent()
    {
        return $this->_urlInterface->getCurrentUrl();
    }

    /**
     * @param $parentId
     * @return string
     */
    public function renderCommentsHtml($parentId)
    {
        $string = '';
        $comments = $this->getPost()->getComments()
            ->addFieldToFilter('parent_id', ['eq' => $parentId])
            ->addFieldToFilter('status', ['eq' => 1]);
        foreach ($comments as $comment) {
            $date = date_create($comment->getCreationTime());
            if ($comment->getAuthorType() == 1) {
                $authorName = $comment->getAuthor()['nickname'];
            } else {
                $authorName = $comment->getAuthorNickname();
            }
            $string .= '
                <div class="comment-item parent-">
                <div class="comment-detail-wrap">
                    <div class="comment-detail-info">
                        <span class="c-name">' . $authorName . '</span>
                        <span class="publish-date">' . date_format($date, 'd/m/Y H:i') . '</span>
                    </div>
                    <p class="comment-detail-text">' . $comment->getContent() . '</p>
                    <span class="comment-detail-action">
                                    <a class="decoration-none">Reply</a>
                                </span>
                    <div class="comment-reply comment-reply-item">
                        <form class="comment-form" action="' . $this->getFormUrl() . '" method="POST">
                            <input name="post_id" type="hidden" value="' . (int)($this->getPost()->getId()) . '">
                            <input type="hidden" name="parent_id" value="' . $comment->getParentCommentId($comment->getId()) . '" />
                            <textarea name="content" placeholder="Add a comment..." data-validate="{\'required\':true}"></textarea>'
                . $this->commentInfoHtml() .
                '<span class="btn cancel">Cancel</span>
                            <button class="btn submit" type="submit">Submit</button>
                        </form>
                    </div>
                </div>
            ' . $this->renderCommentsHtml($comment->getCommentId()) . '
                    </div>
                ';
        }
        return $string;
    }

    /**
     * Retrieve form url
     * @return string
     */
    public function getFormUrl()
    {
        return $this->getUrl('blog/comment/post');
    }

    /**
     * @return string
     */
    public function commentInfoHtml()
    {
        $tring = '';
        if (!$this->isLoggedIn()) {
            $tring = '
                <div class="comment-info">
                    <div class="comment-fullname">
                       <input type="text" name="author_nickname" placeholder="Full Name" data-validate="{\'required\':true}">
                    </div>
                    <div class="comment-email">
                    <input type="email" name="author_email" placeholder="Email"
                       data-validate="{required:true, \'validate-email\':true}">
                    </div>
                </div>';
        }
        return $tring;
    }

    /**
     * @return \Victory\Blog\Model\ResourceModel\Post\Collection
     */
    public function getRelatedPosts()
    {
        $date = date("Y-m-d H:s:i", time());
        $posts = $this->_postCollection->create();
        $posts->addFieldToFilter('publish_time', array('lteq' => $date))
            ->addFieldToFilter('main_table.is_active', array('eq' => 1))
            ->addFieldToFilter('main_table.post_id', array('nin' => $this->getPost()->getPostId()))
            ->setOrder('publish_time', 'DESC')
            ->getRelatedPosts($this->getPost()->getCategoryIds());
        return $posts;
    }
}
