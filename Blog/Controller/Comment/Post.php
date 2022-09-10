<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Controller\Comment;

use Victory\Blog\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Victory\Blog\Model\CommentFactory;
use Victory\Blog\Model\PostFactory;
use Magento\Framework\Message\ManagerInterface;

/**
 * Blog comment post
 */
class Post extends Action
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var CommentFactory
     */
    protected $commentFactory;

    /**
     * @var PostFactory
     */
    protected $postFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param CommentFactory $commentFactory
     * @param PostFactory $postFactory
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Context        $context,
        Session        $customerSession,
        CommentFactory $commentFactory,
        PostFactory    $postFactory,
        ManagerInterface $messageManager
    )
    {
        $this->customerSession = $customerSession;
        $this->commentFactory = $commentFactory;
        $this->postFactory = $postFactory;
        $this->messageManager = $messageManager;

        parent::__construct($context);
    }

    /**
     * execute function
     */
    public function execute()
    {
        $request = $this->getRequest();

        if (!$this->moduleEnabled()) {
            return $this->_forwardNoroute();
        }
        $comment = $this->commentFactory->create();
        $comment->setData($request->getPostValue());
        if ($this->customerSession->getCustomerGroupId()) {
            /* Customer */
            $comment->setCustomerId(
                $this->customerSession->getCustomerId()
            )->setAuthorNickname(
                $this->customerSession->getCustomer()->getName()
            )->setAuthorEmail(
                $this->customerSession->getCustomer()->getEmail()
            )->setAuthorType(
                \Victory\Blog\Model\Config\Source\AuthorType::CUSTOMER
            );
        } else {
            /* Guest can post review */
            if (!trim($request->getParam('author_nickname')) || !trim($request->getParam('author_email'))) {
                $this->messageManager->addError(__("Please enter your name and email"));
                $this->_redirect($this->_redirect->getRefererUrl());
            }

            $comment->setCustomerId(0)->setAuthorType(
                \Victory\Blog\Model\Config\Source\AuthorType::GUEST
            );
        }

        /* Unset sensitive data */
        foreach (['comment_id', 'creation_time', 'update_time', 'status'] as $key) {
            $comment->unsetData($key);
        }

        /* Set default status */
        $comment->setStatus(
            \Victory\Blog\Model\Config\Source\CommentStatus::PENDING
        );

        try {
            $post = $this->initPost();
            if (!$post) {
                throw new \Exception(__('You cannot post comment. Blog post is not longer exist.'), 1);
            }

            if ($request->getParam('parent_id')) {
                $parentComment = $this->initParentComment();
                if (!$parentComment) {
                    throw new \Exception(__('You cannot reply to this comment. Comment is not longer exist.'), 1);
                }

                if (!$parentComment->getPost()
                    || $parentComment->getPost()->getId() != $post->getId()
                ) {
                    throw new \Exception(__('You cannot reply to this comment.'), 1);
                }

                $comment->setParentId($parentComment->getId());
            }

            $comment->save();
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
            $this->_redirect($this->_redirect->getRefererUrl());
        }

        $this->messageManager->addSuccess(__("You submitted your comment for moderation."));
        $this->_redirect($this->_redirect->getRefererUrl());
    }

    /**
     * Initialize and check post
     *
     * @return \Victory\Blog\Model\Post|bool
     */
    protected function initPost()
    {
        $postId = (int)$this->getRequest()->getParam('post_id');
        $post = $this->postFactory->create()->load($postId);
        if (!$post->getIsActive()) {
            return false;
        }
        return $post;
    }

    /**
     * Initialize and check parent comment
     *
     * @return \Victory\Blog\Model\Comment|bool
     */
    protected function initParentComment()
    {
        $commentId = (int)$this->getRequest()->getParam('parent_id');

        $comment = $this->commentFactory->create()->load($commentId);
        if (!$comment->getStatus()) {
            return false;
        }

        return $comment;
    }
}
