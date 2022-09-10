<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Model\ResourceModel\Post;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Post collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var int
     */
    protected $_storeId;

    /**
     * Initialize collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Victory\Blog\Model\Post', 'Victory\Blog\Model\ResourceModel\Post');
        $this->_map['fields']['store'] = 'store_table.store_id';
    }

    /**
     * @return $this
     */
    public function joinWithAuthor()
    {
        $authorTable = $this->getTable("admin_user");
        $this->getSelect()
            ->joinLeft(array('author' => $authorTable),
                'main_table' . '.author_id = author.user_id',
                array('author.username')
            );
        return $this;
    }

    /**
     * @param $categoryId
     * @return $this
     */
    public function getPostsByCategory($categoryId)
    {
        $postCategoryTable = $this->getTable('gv_blog_post_category');
        $this->getSelect()
            ->join(array('pct' => $postCategoryTable), 'main_table' . '.post_id = pct.post_id',
                array('pct.category_id')
            );
        $this->getSelect()->where("category_id=" . $categoryId);
        return $this;
    }

    /**
     * @param array $categories
     * @return $this
     */
    public function getRelatedPosts(array $categories)
    {
        $postCategoryTable = $this->getTable('gv_blog_post_category');
        $this->getSelect()
            ->join(array('pct' => $postCategoryTable), 'main_table' . '.post_id = pct.post_id',
                array('pct.category_id')
            )->columns(['postCount' => 'COUNT(main_table.post_id)'])
            ->group('main_table.post_id')->having('postCount > ?', 0);
        $this->getSelect()->where("category_id in (?) ", $categories);
        return $this;
    }

    /**
     * @param $storeId
     * @return $this
     */
    public function filterStore($storeId)
    {
        $postStoreTable = $this->getTable('gv_blog_post_store');
        $this->getSelect()
            ->join(array('pst' => $postStoreTable), 'main_table' . '.post_id = pst.post_id',
                array('pst.store_id')
            );
        $this->getSelect()->where("store_id= " . $storeId . " or store_id=0");
        return $this;
    }
}
