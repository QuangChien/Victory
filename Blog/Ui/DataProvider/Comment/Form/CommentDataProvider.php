<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Ui\DataProvider\Comment\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Victory\Blog\Model\ResourceModel\Comment\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Escaper;

/**
 * Class DataProvider
 */
class CommentDataProvider extends AbstractDataProvider
{
    /**
     * @var CollectionFactory
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * construct
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $commentCollectionFactory,
        DataPersistorInterface $dataPersistor,
        UrlInterface $url,
        array $meta = [],
        array $data = [],
        Escaper $escaper = null
    ) {
        $this->collection = $commentCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->meta = $this->prepareMeta($this->meta);
        $this->url = $url;
        $this->escaper = $escaper ?: \Magento\Framework\App\ObjectManager::getInstance()->create(
            \Magento\Framework\Escaper::class
        );
    }

    /**
     * Prepares Meta
     *
     * @param array $meta
     * @return array
     */
    public function prepareMeta(array $meta)
    {
        return $meta;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var $comment \Victory\Blog\Model\Comment */
        foreach ($items as $comment) {
            $this->loadedData[$comment->getId()] = $comment->getData();

            $post = $comment->getPost();
            $this->loadedData[$comment->getId()]['post_url'] = [
                'url' => $this->url->getUrl('blog/post/edit', ['id' => $post->getId()]),
                'title' => $post->getTitle(),
                'text' => '#' . $post->getId() . '. ' . $post->getTitle(),
            ];

            $author = $comment->getAuthor();
            $guestData = [
                'url' => 'mailto:' . $author->getEmail(),
                'title' => $author->getNickname(),
                'text' => $author->getNickname() .
                    ' - ' . $author->getEmail() .
                    ' (' . __('Guest')  . ')',
            ];

            switch ($comment->getAuthorType()) {
                case \Victory\Blog\Model\Config\Source\AuthorType::GUEST:
                    $this->loadedData[$comment->getId()]['author_url'] = $guestData;
                    break;
                case \Victory\Blog\Model\Config\Source\AuthorType::CUSTOMER:
                    if ($author->getCustomer()) {
                        $this->loadedData[$comment->getId()]['author_url'] = [
                            'url' => $this->url->getUrl(
                                'customer/index/edit',
                                ['id' => $comment->getCustomerId()]
                            ),
                            'title' => $author->getNickname(),
                            'text' => '#' . $comment->getCustomerId() .
                                '. ' . $author->getNickname() .
                                ' (' . __('Customer') . ')',
                        ];
                    } else {
                        $this->loadedData[$comment->getId()]['author_url'] = $guestData;
                    }

                    break;
                case \Victory\Blog\Model\Config\Source\AuthorType::ADMIN:
                    if ($author->getAdmin()) {
                        $this->loadedData[$comment->getId()]['author_url'] = [
                            'url' => $this->url->getUrl(
                                'admin/user/edit',
                                ['id' => $comment->getAdminId()]
                            ),
                            'title' => $author->getNickname(),
                            'text' => '#' . $comment->getAdminId() .
                                '. ' . $author->getNickname() .
                                ' (' . __('Admin') . ')',
                        ];
                    } else {
                        $this->loadedData[$comment->getId()]['author_url'] = $guestData;
                    }
                    break;
            }

            if ($comment->getParentId()
                && ($parentComment = $comment->getParentComment())
            ) {
                $text = (mb_strlen($parentComment->getContent()) > 200) ?
                    (mb_substr($parentComment->getContent(), 0, 200) . '...') :
                    $parentComment->getContent();
                $text = $this->escaper->escapeHtml($text);
                $this->loadedData[$comment->getId()]['parent_url'] = [
                    'url' => $this->url->getUrl('blog/comment/edit', ['id' => $parentComment->getId()]),
                    'title' => $this->escaper->escapeHtml($parentComment->getContent()),
                    'text' => '#' . $parentComment->getId() . '. ' . $text,
                ];
            } else {
                $this->loadedData[$comment->getId()]['parent_url'] = [
                    'url' => '',
                    'title' => '',
                    'text' => '',
                ];
            }
        }

        $data = $this->dataPersistor->get('blog_comment_form_data');
        if (!empty($data)) {
            $comment = $this->collection->getNewEmptyItem();
            $comment->setData($data);
            $this->loadedData[$comment->getId()] = $comment->getData();
            $this->dataPersistor->clear('blog_comment_form_data');
        }

        return $this->loadedData;
    }
}
