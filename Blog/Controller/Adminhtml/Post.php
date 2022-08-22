<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Controller\Adminhtml;

/**
 * Admin blog post controller
 */
class Post extends Actions
{
    /**
     * Allowed Key
     * @var string
     */
    protected $_allowedKey = 'Victory_Blog::post';

    /**
     * Model class name
     * @var string
     */
    protected $_modelClass = \Victory\Blog\Model\Post::class;

    /**
     * Collection class name
     * @var string
     */
    protected $_collectionClass = \Victory\Blog\Model\ResourceModel\Post\Collection::class;

    /**
     * Active menu key
     * @var string
     */
    protected $_activeMenu = 'Victory_Blog::post';

    /**
     * Status field name
     * @var string
     */
    protected $_statusField = 'is_active';
}
