<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Category Status
 */
class Status extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        ContextInterface      $context,
        UiComponentFactory    $uiComponentFactory,
        StoreManagerInterface $storeManager,
        array                 $components = [],
        array                 $data = []
    )
    {
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
                    $item['is_active'] = ($item['is_active'] == 1 ? __('Enable') : __('Disable'));
                }
            }
        }
        return $dataSource;
    }
}
