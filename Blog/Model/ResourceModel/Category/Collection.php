<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Model\ResourceModel\Category;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Category collection
 */
class Collection extends AbstractCollection
{
    /**
     * Initialize collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Victory\Blog\Model\Category', 'Victory\Blog\Model\ResourceModel\Category');
    }

    /**
     * Retrieve grouped category childs
     * @return array
     */
    public function getGroupedChilds()
    {
        $childs = [];
        if (count($this)) {
            foreach ($this as $item) {
                $childs[$item->getParentId()][] = $item;
            }
        }
        return $childs;
    }

    /**
     * @param $postId
     * @return $this
     */
    public function filterCategories($postId)
    {
        $postCategoryTable = $this->getTable('gv_blog_post_category');
        $this->getSelect()
            ->join(array('pct' => $postCategoryTable), 'main_table' . '.category_id = pct.category_id',
                array('pct.post_id')
            );
        $this->getSelect()->where("post_id= " . $postId);
        return $this;
    }
}
