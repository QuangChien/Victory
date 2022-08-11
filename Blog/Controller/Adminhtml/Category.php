<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Controller\Adminhtml;

/**
 * Admin blog category edit controller
 */
class Category extends Actions
{
    /**
     * Form session key
     * @var string
     */
    protected $_formSessionKey = 'blog_category_form_data';

    /**
     * Allowed Key
     * @var string
     */
    protected $_allowedKey = 'Victory_Blog::category';

    /**
     * Model class name
     * @var string
     */
    protected $_modelClass = \Victory\Blog\Model\Category::class;

    /**
     * Active menu key
     * @var string
     */
    protected $_activeMenu = 'Victory_Blog::category';

    /**
     * Status field name
     * @var string
     */
    protected $_statusField = 'is_active';
}
