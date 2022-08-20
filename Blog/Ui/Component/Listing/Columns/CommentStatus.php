<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * AuthorType
 */
class CommentStatus extends Column
{
    /**
     * @const string
     */
    const PENDING = 0;

    /**
     * @const int
     */
    const APPROVED = 1;

    /**
     * @const int
     */
    const NOT_APPROVED = 2;


    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if ($item) {
                    if($item['status'] == self::PENDING) {
                        $item['status'] = __('Pending');
                    } elseif ($item['status'] == self::APPROVED){
                        $item['status'] = __('Approved');
                    }elseif ($item['status'] == self::NOT_APPROVED) {
                        $item['status'] = __('Not Approved');
                    }
                }
            }
        }
//        echo "<pre>";
//        print_r($dataSource); die();
        return $dataSource;
    }
}
