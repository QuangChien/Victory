<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Ui\DataProvider\Post\Form;

use Victory\Blog\Model\ResourceModel\Post\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class PostDataProvider
 */
class PostDataProvider extends AbstractDataProvider
{
    /**
     * folder feature image
     */
    const PATH_FOLDER_IMAGE = 'catalog/tmp/category/';

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var
     */
    protected $_storeManager;

    /**
     * @param $name
     * @param $primaryFieldName
     * @param $requestFieldName
     * @param CollectionFactory $postCollectionFactory
     * @param array $meta
     * @param array $data
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $postCollectionFactory,
        array $meta = [],
        array $data = [],
        StoreManagerInterface $storeManager
    )
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $postCollectionFactory->create();
        $this->meta = $this->prepareMeta($this->meta);
        $this->_storeManager = $storeManager;
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
        $posts = $this->collection->getItems();
        /** @var $post \Victory\Blog\Model\Post */
        foreach ($posts as $post) {
            $post = $post->load($post->getId());
            $data = $post->getData();

            if ($post->getFeaturedImg()) {
                $image['featured_img'][0]['name'] = $post->getFeaturedImg();
                $image['featured_img'][0]['url'] = $this->getMediaUrl()
                    . self::PATH_FOLDER_IMAGE . $post->getFeaturedImg();
                $data = array_merge($data, $image);
            }

            $this->loadedData[$post->getId()] = $data;
        }

        return $this->loadedData;
    }

    /**
     * @return string
     */
    public function getMediaUrl()
    {
        return (string)$this->_storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
}
