<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Categories ResourceModel
 */
class Post extends AbstractDb
{
    protected $_date;

    /**
     * Initialize dependencies
     *
     * @param Context $context
     */
    public function __construct(
        Context  $context,
        DateTime $date
    )
    {
        $this->_date = $date;
        parent::__construct($context);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('gv_blog_post', 'post_id');
    }

    /**
     * Process post data before deleting
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeDelete(
        \Magento\Framework\Model\AbstractModel $object
    )
    {
        $condition = ['post_id = ?' => (int)$object->getId()];
        $tableSufixs = [
            'store',
            'category'
        ];
        foreach ($tableSufixs as $sufix) {
            $this->getConnection()->delete(
                $this->getTable('gv_blog_post_' . $sufix),
                $condition
            );
        }

        return parent::_beforeDelete($object);
    }

//    /**
//     * Process post data before saving
//     *
//     * @param \Magento\Framework\Model\AbstractModel $object
//     * @return $this
//     * @throws \Magento\Framework\Exception\LocalizedException
//     */
//    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
//    {
//        foreach (['publish_time', 'custom_theme_from', 'custom_theme_to'] as $field) {
//            $value = $object->getData($field) ?: null;
//            $object->setData($field, $this->dateTime->formatDate($value));
//        }
//
//        $identifierGenerator = \Magento\Framework\App\ObjectManager::getInstance()
//            ->create(\Magefan\Blog\Model\ResourceModel\PageUrlKeyGenerator::class);
//        $identifierGenerator->generate($object);
//
//        if (!$this->isValidPageIdentifier($object)) {
//            throw new \Magento\Framework\Exception\LocalizedException(
//                __('The post URL key contains disallowed symbols.')
//            );
//        }
//
//        if ($this->isNumericPageIdentifier($object)) {
//            throw new \Magento\Framework\Exception\LocalizedException(
//                __('The post URL key cannot be made of only numbers.')
//            );
//        }
//
//        $id = $this->checkIdentifier($object->getData('identifier'), $object->getData('store_ids'));
//        if ($id && $id !== $object->getId()) {
//            throw new \Magento\Framework\Exception\LocalizedException(
//                __('URL key is already in use by another blog item.')
//            );
//        }
//
//        $gmtDate = $this->_date->gmtDate();
//
//        if ($object->isObjectNew() && !$object->getCreationTime()) {
//            $object->setCreationTime($gmtDate);
//        }
//
//        if (!$object->getPublishTime()) {
//            $object->setPublishTime($object->getCreationTime());
//        }
//
//        $object->setUpdateTime($gmtDate);
//
//        return parent::_beforeSave($object);
//    }

    /**
     * Assign post to store views, categories, related posts, etc.
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $oldIds = $this->lookupStoreIds($object->getId());
        $newIds = (array)$object->getStoreIds();
        if (!$newIds || in_array(0, $newIds)) {
            $newIds = [0];
        }

        $this->_updateLinks($object, $newIds, $oldIds, 'gv_blog_post_store', 'store_id');

        /* Save category */
        $newIds = (array)$object->getData('categories');
        foreach ($newIds as $key => $id) {
            if (!$id) {
                unset($newIds[$key]);
            }
        }
        if (is_array($newIds)) {
            $lookup = 'lookup' . ucfirst('categoryIds');
            $oldIds = $this->$lookup($object->getId());
            $this->_updateLinks(
                $object,
                $newIds,
                $oldIds,
                'gv_blog_post_category',
                'category_id'
            );
        }

        return parent::_afterSave($object);
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $postId
     * @return array
     */
    public function lookupStoreIds($postId)
    {
        return $this->_lookupIds($postId, 'gv_blog_post_store', 'store_id');
    }

    /**
     * Get category ids to which specified item is assigned
     *
     * @param int $postId
     * @return array
     */
    public function lookupCategoryIds($postId)
    {
        return $this->_lookupIds($postId, 'gv_blog_post_category', 'category_id');
    }

    /**
     * Get ids to which specified item is assigned
     * @param int $postId
     * @param string $tableName
     * @param string $field
     * @return array
     */
    protected function _lookupIds($postId, $tableName, $field)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()->from(
            $this->getTable($tableName),
            $field
        )->where(
            'post_id = ?',
            (int)$postId
        );

        return $adapter->fetchCol($select);
    }

    /**
     * Update post connections
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param Array $newIds
     * @param Array $oldIds
     * @param String $tableName
     * @param String $field
     * @param Array $rowData
     * @return void
     */
    protected function _updateLinks(
        \Magento\Framework\Model\AbstractModel $object,
        array                                  $newIds,
        array                                  $oldIds,
                                               $tableName,
                                               $field,
                                               $rowData = []
    )
    {
        $table = $this->getTable($tableName);

        if ($object->getId() && empty($rowData)) {
            $currentData = $this->_lookupAll($object->getId(), $tableName, '*');
            foreach ($currentData as $item) {
                $rowData[$item[$field]] = $item;
            }
        }

        $insert = $newIds;
        $delete = $oldIds;

        if ($delete) {
            $where = ['post_id = ?' => (int)$object->getId(), $field . ' IN (?)' => $delete];

            $this->getConnection()->delete($table, $where);
        }

        if ($insert) {
            $data = [];

            foreach ($insert as $id) {
                $id = (int)$id;
                $data[] = array_merge(
                    ['post_id' => (int)$object->getId(), $field => $id],
                    (isset($rowData[$id]) && is_array($rowData[$id])) ? $rowData[$id] : []
                );
            }

            /* Fix if some rows have extra data */
            $allFields = [];
            foreach ($data as $i => $row) {
                foreach ($row as $key => $value) {
                    $allFields[$key] = $key;
                }
            }
            foreach ($data as $i => $row) {
                foreach ($allFields as $key) {
                    if (!array_key_exists($key, $row)) {
                        $data[$i][$key] = null;
                    }
                }
            }
            /* End fix */

            $this->getConnection()->insertMultiple($table, $data);
        }
    }

    /**
     * Get rows to which specified item is assigned
     * @param int $postId
     * @param string $tableName
     * @param string $field
     * @return array
     */
    protected function _lookupAll($postId, $tableName, $field)
    {
        $adapter = $this->getConnection();

        $select = $adapter->select()->from(
            $this->getTable($tableName),
            $field
        )->where(
            'post_id = ?',
            (int)$postId
        );

        return $adapter->fetchAll($select);
    }

    /**
     * Check if post identifier exist for specific store
     * return post id if post exists
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
        $select->reset(\Zend_Db_Select::COLUMNS)->columns('cp.post_id')->order('cps.store_id DESC')->limit(1);

        return $this->getConnection()->fetchOne($select);
    }

    /**
     * Check if post identifier exist for specific store
     * return post id if post exists
     *
     * @param string $identifier
     * @param int $storeId
     * @return int
     */
    protected function _getLoadByUrlKeySelect($identifier, $storeIds)
    {
        $select = $this->getConnection()->select()->from(
            ['cp' => $this->getMainTable()]
        )->join(
            ['cps' => $this->getTable('gv_blog_post_store')],
            'cp.post_id = cps.post_id',
            []
        )->where(
            'cp.url_key = ?',
            $identifier
        )->where(
            'cps.store_id IN (?)',
            $storeIds
        );

        return $select;
    }

    /**
     *  Check whether post url key is numeric
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    protected function isNumericPageUrlKey(\Magento\Framework\Model\AbstractModel $object)
    {
        return preg_match('/^[0-9]+$/', $object->getData('url_key'));
    }

    /**
     *  Check whether post url key is valid
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    protected function isValidPageUrlKey(\Magento\Framework\Model\AbstractModel $object)
    {
        return preg_match('/^([^?#<>@!&*()$%^\\+=,{}"\']+)?$/', $object->getData('url_key'));
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

            $categories = $this->lookupCategoryIds($object->getId());
            $object->setCategories($categories);

        }

        return parent::_afterLoad($object);
    }

    /**
     * Load an object using url key field if there's no field specified and value is not numeric
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

    /**
     * Process post data before saving
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $urlKeyGenerator = \Magento\Framework\App\ObjectManager::getInstance()
            ->create(\Victory\Blog\Model\ResourceModel\PageUrlKeyGenerator::class);
        $urlKeyGenerator->generate($object, 'title');

        if (!$this->isValidPageUrlKey($object)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The post URL key contains disallowed symbols.')
            );
        }

        if ($this->isNumericPageUrlKey($object)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The post URL key cannot be made of only numbers.')
            );
        }

        $id = $this->checkUrlKey($object->getData('identifier'), $object->getData('store_ids'));
        if ($id && $id !== $object->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('URL key is already in use by another blog item.')
            );
        }

        $gmtDate = $this->_date->gmtDate();

        if ($object->isObjectNew() && !$object->getCreationTime()) {
            $object->setCreationTime($gmtDate);
        }

        if (!$object->getPublishTime()) {
            $object->setPublishTime($object->getCreationTime());
        }

        $object->setUpdateTime($gmtDate);

        return parent::_beforeSave($object);
    }

}
