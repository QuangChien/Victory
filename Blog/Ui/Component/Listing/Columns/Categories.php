<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Victory\Blog\Model\ResourceModel\Category\CollectionFactory;
use Victory\Blog\Model\ResourceModel\PostFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Categories
 */
class Categories extends Column
{
    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var PostFactory
     */
    protected $_postResourceFactory;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     * @param CollectionFactory $collectionFactory
     * @param PostFactory $_postResourceFactory
     */
    public function __construct(
        ContextInterface   $context,
        UiComponentFactory $uiComponentFactory,
        array              $components = [],
        array              $data = [],
        CollectionFactory  $collectionFactory,
        PostFactory        $_postResourceFactory
    )
    {
        $this->_postResourceFactory = $_postResourceFactory;
        $this->_collectionFactory = $collectionFactory;
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
                    $item['categories'] = $this->getCategoriesName($item['post_id']);
                }
            }
        }

        return $dataSource;
    }

    /**
     * @param $postId
     * @return array
     */
    public function getCategoryIds($postId)
    {
        return $this->_postResourceFactory->create()->lookupCategoryIds($postId);
    }

    /**
     * @param $postId
     * @return string
     */
    public function getCategoriesName($postId)
    {
        $categories = [];
        $categoriesFilter = $this->_collectionFactory->create()
            ->addFieldToFilter('category_id', ['in' => $this->getCategoryIds($postId)]);
        foreach ($categoriesFilter as $category) {
            $categories[] = $category->getName();
        }
        return (string)implode(', ', $categories);
    }
}
