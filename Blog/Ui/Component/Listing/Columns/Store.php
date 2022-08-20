<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Ui\Component\Listing\Columns;

use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\System\Store as SystemStore;
use Magento\Framework\ObjectManagerInterface;


/**
 * Store in Grid
 */
class Store extends \Magento\Store\Ui\Component\Listing\Column\Store
{
    /**
     * Categories id
     */
    const ID_FIELD_NAME_CATEGORY = 'category_id';

    /**
     * Categories resource
     */
    const CATEGORY_RESOURCE = \Victory\Blog\Model\ResourceModel\Category::class;

    /**
     * Post resource
     */
    const POST_RESOURCE = \Victory\Blog\Model\ResourceModel\Post::class;
    /**
     * @var CategoryFactory
     */
    protected $_resource;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param SystemStore $systemStore
     * @param Escaper $escaper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface       $context,
        UiComponentFactory     $uiComponentFactory,
        SystemStore            $systemStore,
        Escaper                $escaper,
        array                  $components = [],
        array                  $data = [],
        ObjectManagerInterface $objectManager
    )
    {
        $this->_objectManager = $objectManager;
        parent::__construct($context, $uiComponentFactory, $systemStore, $escaper, $components, $data);
    }

    /**
     * @param array $item
     * @return \Magento\Framework\Phrase|string
     */
    protected function prepareDataItem(array $item, $fieldId, $idFieldName)
    {
        $content = $origStores = '';
        if ($idFieldName == self::ID_FIELD_NAME_CATEGORY) {
            $origStores = $this->_objectManager->create(self::CATEGORY_RESOURCE)->lookupStoreIds($fieldId);
        } else {
            $origStores = $this->_objectManager->create(self::POST_RESOURCE)->lookupStoreIds($fieldId);
        }

        if (in_array(0, $origStores) && count($origStores) == 1) {
            return __('All Store Views');
        }

        $data = $this->systemStore->getStoresStructure(false, $origStores);

        foreach ($data as $website) {
            $content .= $website['label'] . "<br/>";
            foreach ($website['children'] as $group) {
                $content .= str_repeat('&nbsp;', 3) . $this->escaper->escapeHtml($group['label']) . "<br/>";
                foreach ($group['children'] as $store) {
                    $content .= str_repeat('&nbsp;', 6) . $this->escaper->escapeHtml($store['label']) . "<br/>";
                }
            }
        }
        return $content;
    }


    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if ($item) {
                    $item['stores_view'] =
                        $this->prepareDataItem(
                            $item,
                            $item['id_field_name'] == self::ID_FIELD_NAME_CATEGORY
                                ? $item['category_id'] : $item['post_id'],
                            $item['id_field_name']);
                }

            }
        }
        return $dataSource;
    }
}
