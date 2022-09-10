<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\App\Action;

use Magento\Framework\App\Action\Action as ActionClass;

/**
 * AbstractBlog frontend action controller
 */
abstract class Action extends ActionClass
{
    /**
     * Retrieve true if blog extension is enabled.
     *
     * @return bool
     */
    protected function moduleEnabled()
    {
        return (bool) $this->getConfigValue(
            \Victory\Blog\Model\Config::XML_PATH_EXTENSION_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve store config value
     *
     * @return string | null | bool
     */
    protected function getConfigValue($path)
    {
        $config = $this->_objectManager->get(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        return $config->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Throw control to cms_index_noroute action.
     *
     * @return void
     */
    protected function _forwardNoroute()
    {
        $this->_forward('index', 'noroute', 'cms');
    }
}
