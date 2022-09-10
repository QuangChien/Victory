<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Victory\Blog\Model\PostFactory;
use Magento\Customer\Model\CustomerFactory;
use Victory\Blog\Model\ResourceModel\Comment\CollectionFactory;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\User\Model\UserFactory;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Comment Model
 */
class Comment extends AbstractModel implements IdentityInterface
{
    /**
     * @var PostFactory
     */
    protected $postFactory;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var UserFactory
     */
    protected $userFactory;

    /**
     * @var CollectionFactory
     */
    protected $commentCollectionFactory;

    /**
     * @var
     */
    protected $author;

    /**
     * @var CollectionFactory
     */
    protected $comments;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param \Victory\Blog\Model\PostFactory $postFactory
     * @param CustomerFactory $customerFactory
     * @param UserFactory $userFactory
     * @param CollectionFactory $commentCollectionFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context           $context,
        Registry          $registry,
        PostFactory       $postFactory,
        CustomerFactory   $customerFactory,
        UserFactory       $userFactory,
        CollectionFactory $commentCollectionFactory,
        AbstractResource  $resource = null,
        AbstractDb        $resourceCollection = null,
        array             $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->postFactory = $postFactory;
        $this->customerFactory = $customerFactory;
        $this->userFactory = $userFactory;
        $this->commentCollectionFactory = $commentCollectionFactory;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Victory\Blog\Model\ResourceModel\Comment');
    }

    /**
     * Retrieve model title
     * @param boolean $plural
     * @return string
     */
    public function getOwnTitle($plural = false)
    {
        return $plural ? 'Comments' : 'Comment';
    }

    /**
     * @return array|mixed|null
     */
    public function getPost()
    {
        if (!$this->hasData('post')) {
            $this->setData('post', false);
            if ($postId = $this->getData('post_id')) {
                $post = $this->postFactory->create()->load($postId);
                if ($post->getId()) {
                    $this->setData('post', $post);
                }
            }
        }

        return $this->getData('post');
    }

    /**
     * Retrieve author
     * @return \Magento\Framework\DataObject
     */
    public function getAuthor()
    {
        if (null === $this->author) {
            $this->author = new \Magento\Framework\DataObject;
            $this->author->setType(
                $this->getAuthorType()
            );

            $guestData = [
                'nickname' => $this->getAuthorNickname(),
                'email' => $this->getAuthorEmail(),
            ];

            switch ($this->getAuthorType()) {
                case \Victory\Blog\Model\Config\Source\AuthorType::GUEST:
                    $this->author->setData($guestData);
                    break;
                case \Victory\Blog\Model\Config\Source\AuthorType::CUSTOMER:
                    $customer = $this->customerFactory->create();
                    $customer->load($this->getCustomerId());
                    if ($customer->getId()) {
                        $this->author->setData([
                            'nickname' => $customer->getName(),
                            'email' => $this->getEmail(),
                            'customer' => $customer,
                        ]);
                    } else {
                        $this->author->setData($guestData);
                    }
                    break;
            }
        }

        return $this->author;
    }

    /**
     * @param $parentId
     * @return mixed|void
     */
    public function getParentCommentId($parentId)
    {
        $comment = clone $this;
        $comment->load($parentId);
        if ($comment->getId()) {
            return $comment->getId();
        }

        return;
    }

    /**
     * Retrieve parent comment
     * @return self || false
     */
    public function getParentComment()
    {
        $k = 'parent_comment';
        if (null === $this->getData($k)) {
            $this->setData($k, false);
            if ($pId = $this->getParentId()) {
                $comment = clone $this;
                $comment->load($pId);
                if ($comment->getId()) {
                    $this->setData($k, $comment);
                }
            }
        }

        return $this->getData($k);
    }

    public function getIdentities()
    {
        // TODO: Implement getIdentities() method.
    }
}
