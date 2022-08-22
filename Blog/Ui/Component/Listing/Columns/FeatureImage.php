<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Ui\Component\Listing\Columns;

use Magento\Catalog\Helper\Image;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Asset\Repository;

/**
 * FeatureImage class
 */
class FeatureImage extends Column
{
    /**
     * folder feature image
     */
    const PATH_FOLDER_IMAGE = 'catalog/tmp/category/';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Repository
     */
    protected $moduleAssetDir;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Image $imageHelper
     * @param UrlInterface $urlBuilder
     * @param StoreManagerInterface $storeManager
     * @param array $components
     * @param array $data
     * @param Repository $moduleAssetDir
     */
    public function __construct(
        ContextInterface      $context,
        UiComponentFactory    $uiComponentFactory,
        Image                 $imageHelper,
        UrlInterface          $urlBuilder,
        StoreManagerInterface $storeManager,
        array                 $components = [],
        array                 $data = [],
        Repository            $moduleAssetDir
    )
    {
        $this->storeManager = $storeManager;
        $this->imageHelper = $imageHelper;
        $this->urlBuilder = $urlBuilder;
        $this->moduleAssetDir = $moduleAssetDir;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $url = '';
                if (!is_null($item[$fieldName])) {
                    /* Set your image physical path here */
                    $url = $this->storeManager->getStore()->getBaseUrl(
                            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                        ) . self::PATH_FOLDER_IMAGE . $item[$fieldName];
                } else {
                    $url = $this->moduleAssetDir->getUrl("Victory_Blog::images/default.jpg");
                }
                $item[$fieldName . '_src'] = $url;
                $item[$fieldName . '_alt'] = $item[$fieldName];

                $item[$fieldName . '_orig_src'] = $url;
            }
        }

        return $dataSource;
    }
}
