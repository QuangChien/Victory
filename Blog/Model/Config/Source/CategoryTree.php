<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Victory\Blog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\App\RequestInterface;

/**
 * CategoryTree
 *
 */
class CategoryTree implements ArrayInterface
{
    /**
     * @var CollectionFactory
     */
    protected $_categoryCollectionFactory;

    /**
     * @var array
     */
    protected $_options;

    /**
     * @var array
     */
    protected $_childs;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * Initialize dependencies.
     *
     * @param CollectionFactory $categoryCollectionFactory
     */
    public function __construct(
        CollectionFactory $categoryCollectionFactory,
        RequestInterface  $request
    )
    {
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_request = $request;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->_options === null) {
            $this->_options = $this->_getOptions();
        }
        return $this->_options;
    }

    protected function _getOptions($itemId = 0)
    {
        $childs = $this->_getChilds();
        $options = [];

        if (isset($childs[$itemId])) {
            foreach ($childs[$itemId] as $item) {
                $data = [
                    'label' => $item->getName() .
                        ($item->getIsActive() ? '' : ' (' . __('Disabled') . ')'),
                    'value' => $item->getId(),
                ];
                if (isset($childs[$item->getId()])) {
                    $data['optgroup'] = $this->_getOptions($item->getId());
                }

                $options[] = $data;

            }
        }

        return $options;
    }

    protected function _getChilds()
    {
        if ($this->_childs === null) {
            $this->_childs = $this->_categoryCollectionFactory->create()
                ->getGroupedChilds();
        }
        return $this->_childs;
    }
}
