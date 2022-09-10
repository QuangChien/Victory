<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Model\ResourceModel\Author;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Blog author collection
 */
class Collection extends AbstractCollection
{
    /**
     * Constructor
     * Configures collection
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Victory\Blog\Model\Author::class, \Victory\Blog\Model\ResourceModel\Author::class);
    }

}
