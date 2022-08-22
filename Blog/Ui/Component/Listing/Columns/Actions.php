<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

/**
 * Class ProductActions for Listing Columns
 */
abstract class Actions extends Column
{
    /**
     * Controller name
     *
     * @var string
     */
    protected $_controllerName;

    /**
     * category primary
     *
     * @var string
     */
    protected $_primary;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * construct
     */
    public function __construct(
        ContextInterface   $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface       $urlBuilder,
        array              $components = [],
        array              $data = []
    )
    {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')]['edit'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'blog/' . $this->_controllerName . '/edit',
                        ['id' => $item[$this->_primary]]
                    ),
                    'label' => __('Edit'),
                    'hidden' => false,
                ];

                $item[$this->getData('name')]['delete'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'blog/' . $this->_controllerName . '/delete',
                        ['id' => $item[$this->_primary]]
                    ),
                    'label' => __('Delete'),
                    'hidden' => false,
                ];
            }
        }

        return $dataSource;
    }
}
