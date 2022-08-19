<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Controller\Adminhtml\Post;

use Victory\Blog\Controller\Adminhtml\Post;


/**
 * Blog post save
 */
class Save extends Post
{

    /**
     * @param $model
     * @param $request
     * @return void
     */
    protected function _beforeSave($model, $request)
    {
        /* Prepare author */
        if (!$model->getAuthorId()) {
            $authSession = $this->_objectManager->get(\Magento\Backend\Model\Auth\Session::class);
            $model->setAuthorId($authSession->getUser()->getId());
        }

        /* Prepare images */
        $data = $model->getData();
        if (isset($data['featured_img']) && is_array($data['featured_img'])) {
            if (!empty($data['featured_img']['delete'])) {
                $model->setData('featured_img', null);
            } else {
                if (isset($data['featured_img'][0]['name']) && isset($data['featured_img'][0]['tmp_name'])) {
                    $image = $data['featured_img'][0]['name'];

                    $model->setData('featured_img', $image);
                } else {
                    if (isset($data['featured_img'][0]['name'])) {
                        $model->setData('featured_img', $data['featured_img'][0]['name']);
                    }
                }
            }
        } else {
            $model->setData('featured_img', null);
        }
    }
}


