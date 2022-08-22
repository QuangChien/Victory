<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Category ResourceModel
 */
class Category extends AbstractDb
{

    /**
     * Initialize dependencies
     *
     * @param Context $context
     */
    public function __construct(
        Context $context
    )
    {
        parent::__construct($context);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('gv_blog_category', 'category_id');
    }

    /**
     * Process category data before deleting
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        $condition = ['category_id = ?' => (int)$object->getId()];

        $this->getConnection()->delete($this->getTable('gv_blog_category_store'), $condition);
        $this->getConnection()->delete($this->getTable('gv_blog_post_category'), $condition);

        return parent::_beforeDelete($object);
    }

    /**
     * Assign category to store views
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $oldStoreIds = $this->lookupStoreIds($object->getId());
        $newStoreIds = (array)$object->getStoreIds();
        if (!$newStoreIds || in_array(0, $newStoreIds)) {
            $newStoreIds = [0];
        }

        $table = $this->getTable('gv_blog_category_store');
        $insert = array_diff($newStoreIds, $oldStoreIds);
        $delete = array_diff($oldStoreIds, $newStoreIds);

        if ($delete) {
            $where = ['category_id = ?' => (int)$object->getId(), 'store_id IN (?)' => $delete];

            $this->getConnection()->delete($table, $where);
        }

        if ($insert) {
            $data = [];

            foreach ($insert as $storeId) {
                $data[] = ['category_id' => (int)$object->getId(), 'store_id' => (int)$storeId];
            }

            $this->getConnection()->insertMultiple($table, $data);
        }

        return parent::_afterSave($object);
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $categoryId
     * @return array
     */
    public function lookupStoreIds($categoryId)
    {
        $adapter = $this->getConnection();

        $select = $adapter->select()->from(
            $this->getTable('gv_blog_category_store'),
            'store_id'
        )->where(
            'category_id = ?',
            (int)$categoryId
        );
        return $adapter->fetchCol($select);
    }

    /**
     * Perform operations after object load
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getId()) {
            $storeIds = $this->lookupStoreIds($object->getId());
            $object->setData('store_ids', $storeIds);
        }

        return parent::_afterLoad($object);
    }

    /**
     * Check if category url key exist for specific store
     * return category id if category exists
     *
     * @param string $urlKey
     * @param int $storeId
     * @return int
     */
    protected function _getLoadByUrlKeySelect($urlKey, $storeIds)
    {
        $select = $this->getConnection()->select()->from(
            ['cp' => $this->getMainTable()]
        )->join(
            ['cps' => $this->getTable('gv_blog_category_store')],
            'cp.category_id = cps.category_id',
            []
        )->where(
            'cp.url_key = ?',
            $urlKey
        )->where(
            'cps.store_id IN (?)',
            $storeIds
        );

        return $select;
    }

    /**
     * Process category data before saving
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $urlKeyGenerator = \Magento\Framework\App\ObjectManager::getInstance()
            ->create(\Victory\Blog\Model\ResourceModel\PageUrlKeyGenerator::class);
        $urlKeyGenerator->generate($object, 'name');

        if (!$this->isValidPageUrlKey($object)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The category URL key contains disallowed symbols.')
            );
        }

        if ($this->isNumericPageUrlKey($object)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The category URL key cannot be made of only numbers.')
            );
        }

        $id = $this->checkUrlKey($object->getData('url_key'), $object->getData('store_ids'));
        if ($id && $id !== $object->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('URL key is already in use by another blog item.')
            );
        }

        return parent::_beforeSave($object);
    }

    /**
     *  Check whether category url key is valid
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    protected function isValidPageUrlKey(\Magento\Framework\Model\AbstractModel $object)
    {
        return preg_match('/^([^?#<>@!&*()$%^\\+=,{}"\']+)?$/', $object->getData('url_key'));
    }

    /**
     * Check if category url key exist for specific store
     * return page id if page exists
     *
     * @param string $urlKey
     * @param int|array $storeId
     * @return int
     */
    public function checkUrlKey($urlKey, $storeIds)
    {
        if (!is_array($storeIds)) {
            $storeIds = [$storeIds];
        }
        $storeIds[] = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        $select = $this->_getLoadByUrlKeySelect($urlKey, $storeIds);
        $select->reset(\Zend_Db_Select::COLUMNS)->columns('cp.category_id')->order('cps.store_id DESC')->limit(1);

        return $this->getConnection()->fetchOne($select);
    }

    /**
     *  Check whether category url key is numeric
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    protected function isNumericPageUrlKey(\Magento\Framework\Model\AbstractModel $object)
    {
        return preg_match('/^[0-9]+$/', $object->getData('url_key'));
    }

    /**
     * Load an object using 'url-key' field if there's no field specified and value is not numeric
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param mixed $value
     * @param string $field
     * @return $this
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && null === $field) {
            $field = 'url_key';
        }

        return parent::load($object, $value, $field);
    }

}
