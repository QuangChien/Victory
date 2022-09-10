<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * AbstractBlog Config Model
 */
class Config
{
    /**
     * Extension enabled config path
     */
    const XML_PATH_EXTENSION_ENABLED = 'gvblog/general/enable';
    const XML_PATH_POST_PER_PAGE = 'gvblog/general/posts_per_page';

    /**
     * AbstractBlog homepage title
     */
    const XML_PATH_BLOG_META_TITLE = 'gvblog/index_page/meta_title';
    const XML_PATH_BLOG_TITLE = 'gvblog/index_page/title';
    const XML_PATH_BLOG_KEYWORDS = 'gvblog/index_page/meta_keywords';
    const XML_PATH_META_DESCRIPTION = 'gvblog/index_page/meta_description';

    const XML_PATH_AUTHOR_TITLE = 'gvblog/author/title';
    const XML_PATH_AUTHOR_META_TITLE = 'gvblog/author/meta_title';
    const XML_PATH_AUTHOR_META_KEYWORDS = 'gvblog/author/meta_keywords';
    const XML_PATH_AUTHOR_META_DESCRIPTION = 'gvblog/author/meta_description';

    const XML_PATH_SIDEBAR_TOTAL_POST = 'gvblog/sidebar/recent_posts_total_number';
    const XML_PATH_BLOG_ROUTE = 'gvblog/permalink/route';

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }


    /**
     * Retrieve true if blog module is enabled
     *
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return (bool)$this->getConfig(
            self::XML_PATH_EXTENSION_ENABLED,
            $storeId
        );
    }

    /**
     * Retrieve store config value
     * @param string $path
     * @param null $storeId
     * @return mixed
     */
    public function getConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
