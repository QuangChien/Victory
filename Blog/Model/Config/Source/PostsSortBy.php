<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class PostsSortBy Model
 */
class PostsSortBy implements ArrayInterface
{
    /**
     * @const int
     */
    const PUBLISH_DATE = 0;

    /**
     * @const int
     */
    const POSITION = 1;


    /**
     * Options int
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::PUBLISH_DATE, 'label' => __('Publish Date (default)')],
            ['value' => self::POSITION, 'label' => __('Position')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $array = [];
        foreach ($this->toOptionArray() as $item) {
            $array[$item['value']] = $item['label'];
        }
        return $array;
    }
}
