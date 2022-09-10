<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Block\Adminhtml\Form\Button;

use Magento\Backend\Block\Widget\Context;

/**
 * Class Generic
 */
class Generic
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * Generic constructor.
     * @param Context $context
     */
    public function __construct(
        Context $context
    )
    {
        $this->context = $context;
    }

    /**
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->context->getRequest()->getParam('id');
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route
     * @param array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
