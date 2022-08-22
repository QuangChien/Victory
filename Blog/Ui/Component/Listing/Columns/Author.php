<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\User\Model\ResourceModel\User\CollectionFactory as UserCollectionFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Author Status
 */
class Author extends Column
{
    /**
     * @var UserCollectionFactory
     */
    protected $_userFactory;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     * @param UserCollectionFactory $userFactory
     */
    public function __construct(
        ContextInterface      $context,
        UiComponentFactory    $uiComponentFactory,
        array                 $components = [],
        array                 $data = [],
        UserCollectionFactory $userFactory
    )
    {
        $this->_userFactory = $userFactory;
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
                    $item['author_id'] = $this->getAuthor($item['author_id']);
                }
            }
        }

        return $dataSource;
    }

    /**
     * @param $authorId
     * @return string|null
     */
    public function getAuthor($authorId)
    {
        $adminUsers = $this->_userFactory->create();
        foreach ($adminUsers as $adminUser) {
            if ($adminUser->getUserId() == $authorId) {
                return (string)$adminUser->getUsername();
                break;
            }
        }
        return null;
    }
}
