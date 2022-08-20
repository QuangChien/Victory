<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Ui\Component\Listing\Columns;

/**
 * CategoryAction class
 */
class CategoryAction extends Actions
{
    /**
     * Controller name
     *
     * @var string
     */
    protected $_controllerName = 'category';

    /**
     * Categories table primary
     *
     * @var string
     */
    protected $_primary = 'category_id';
}
