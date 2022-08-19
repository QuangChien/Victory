<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Ui\Component\Listing\Columns;

/**
 * PostAction class
 */
class PostAction extends Actions
{
    /**
     * Controller name
     *
     * @var string
     */
    protected $_controllerName = 'post';

    /**
     * Post table primary
     *
     * @var string
     */
    protected $_primary = 'post_id';
}
