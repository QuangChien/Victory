<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Authors types
 *
 */
class AuthorType implements ArrayInterface
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
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::GUEST, 'label' => __('Guest')],
            ['value' => self::CUSTOMER, 'label' => __('Customer')]
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
