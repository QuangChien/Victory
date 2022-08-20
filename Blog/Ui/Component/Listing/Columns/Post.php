<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\StoreManagerInterface;
use Victory\Blog\Model\PostFactory;

/**
 * Get Post in Grid
 */
class Post extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var UserCollectionFactory
     */
    protected $postFactory;

    public function __construct(
        ContextInterface      $context,
        UiComponentFactory    $uiComponentFactory,
        StoreManagerInterface $storeManager,
        array                 $components = [],
        array                 $data = [],
        PostFactory $postFactory
    )
    {
        $this->postFactory = $postFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if ($item) {
                    $item['post_id'] = $this->getPostTitle($item['post_id']);
                }
            }
        }
//        echo "<pre>";
//        print_r($dataSource); die();
        return $dataSource;
    }

    /**
     * @param $authorId
     * @return string|null
     */
    public function getPostTitle($postId)
    {
        $post = $this->postFactory->create()->load($postId);
        if($post) {
            return $post->getTitle();
        }
        return null;
    }
}
