<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Controller\Adminhtml;

/**
 * Admin blog comment controller
 */
class Comment extends Actions
{
    /**
     * Form session key
     * @var string
     */
    protected $_formSessionKey = 'blog_comment_form_data';

    /**
     * Allowed Key
     * @var string
     */
    protected $_allowedKey = 'Victory_Blog::comment';

    /**
     * Model class name
     * @var string
     */
    protected $_modelClass = \Victory\Blog\Model\Comment::class;

    /**
     * Active menu key
     * @var string
     */
    protected $_activeMenu = 'Victory_Blog::comment';

    /**
     * Status field name
     * @var string
     */
    protected $_statusField = 'status';
}
