<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\User\Model\ResourceModel\User\CollectionFactory as UserCollectionFactory;

/**
 * AuthorType
 */
class AuthorType extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @const string
     */
    const GUEST = 0;

    /**
     * @const string
     */
    const CUSTOMER = 1;

    /**
     * @const string
     */
    const ADMIN = 2;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var UserCollectionFactory
     */
    protected $_userFactory;

    public function __construct(
        ContextInterface      $context,
        UiComponentFactory    $uiComponentFactory,
        StoreManagerInterface $storeManager,
        array                 $components = [],
        array                 $data = [],
        UserCollectionFactory $userFactory
    )
    {
        $this->_userFactory = $userFactory;
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
                    if($item['author_type'] == self::GUEST) {
                        $item['author_type'] = __('Guest');
                    } elseif ($item['author_type'] == self::CUSTOMER){
                        $item['author_type'] = __('Customer');
                    }elseif ($item['author_type'] == self::ADMIN) {
                        $item['author_type'] = __('Admin');
                    }
                }
            }
        }
//        echo "<pre>";
//        print_r($dataSource); die();
        return $dataSource;
    }
}
