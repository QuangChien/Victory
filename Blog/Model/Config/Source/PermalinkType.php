<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Model\Config\Source;

use Victory\Blog\Model\Url;
use Magento\Framework\Option\ArrayInterface;

/**
 * Used in creating options for permalink config value selection
 */
class PermalinkType implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Url::PERMALINK_TYPE_DEFAULT,
                'label' => __('Default: mystore.com/{blog_route}/{post_route}/post-title/')
            ],
            [
                'value' => Url::PERMALINK_TYPE_SHORT,
                'label' => __('Short: mystore.com/{blog_route}/post-title/')
            ],
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
