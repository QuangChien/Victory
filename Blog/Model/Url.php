<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Model;

use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Blog url model
 */
class Url
{
    /**
     * Permalink Types
     */
    const PERMALINK_TYPE_DEFAULT = 'default';
    const PERMALINK_TYPE_SHORT = 'short';

    /**
     * Objects Types
     */
    const CONTROLLER_INDEX = 'blog_index';
    const CONTROLLER_POST = 'post';
    const CONTROLLER_CATEGORY = 'category';
    const CONTROLLER_AUTHOR = 'author';

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var UrlInterface
     */
    protected $_url;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Store id
     * @var int | null
     */
    protected $storeId;

    /**
     * @var mixed
     */
    protected $originalStore;

    /**
     * @param Registry $registry
     * @param UrlInterface $url
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Registry              $registry,
        UrlInterface          $url,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface  $scopeConfig
    )
    {
        $this->_registry = $registry;
        $this->_url = $url;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Retrieve permalink type
     * @return string
     */
    public function getPermalinkType()
    {
        return $this->_getConfig('type');
    }

    /**
     * @param $controllerName
     * @param $skip
     * @return mixed|string|null
     */
    public function getRoute($controllerName = null, $skip = true)
    {
        if ($controllerName) {
            $controllerName .= '_';
        }

        if ($route = $this->_getConfig($controllerName . 'route')) {
            return $route;
        } else {
            return $skip ? $controllerName : null;
        }
    }

    /**
     * @param $route
     * @param $skip
     * @return mixed|string|null
     */
    public function getControllerName($route, $skip = true)
    {
        foreach ([
                     self::CONTROLLER_POST,
                     self::CONTROLLER_CATEGORY,
                     self::CONTROLLER_AUTHOR,
                 ] as $controllerName) {
            if ($this->getRoute($controllerName) == $route) {
                return $controllerName;
            }
        }

        return $skip ? $route : null;
    }

    /**
     * Retrieve blog page url
     * @param string | object $urlKey
     * @param string $controllerName
     * @return string
     */
    public function getUrl($urlKey, $controllerName)
    {
        $url = $this->_url->getUrl('', [
            '_direct' => $this->getUrlPath($urlKey, $controllerName),
            '_nosid' => $this->storeId ?: null
        ]);

        return $url;
    }


    /**
     * Retrieve blog url path
     * @param string | object $urlKey
     * @param string $controllerName
     * @return string
     */
    public function getUrlPath($urlKey, $controllerName)
    {
        $urlKey = $this->getExpandedItentifier($urlKey);
        switch ($this->getPermalinkType()) {
            case self::PERMALINK_TYPE_DEFAULT:
                $path = $this->getRoute() .
                    '/' . $this->getRoute($controllerName) .
                    '/' . $urlKey . ($urlKey ? '/' : '');
                break;
            case self::PERMALINK_TYPE_SHORT:
                if (
                    $controllerName == self::CONTROLLER_AUTHOR
                ) {
                    $path = $this->getRoute() .
                        '/' . $this->getRoute($controllerName) .
                        '/' . $urlKey . ($urlKey ? '/' : '');
                } else {
                    $path = $this->getRoute() . '/' . $urlKey . ($urlKey ? '/' : '');
                }
                break;
        }

        $path = $this->addUrlSufix($path, $controllerName);

        return $path;
    }

    /**
     * Retrieve itentifier what include parent categories itentifier
     * @param \Magento\Framework\Model\AbstractModel || string $urlKey
     * @return string
     */
    protected function getExpandedItentifier($urlKey)
    {
        if (is_object($urlKey)) {
            $object = $urlKey;
            $urlKey = $urlKey->getUrlKey();
        }

        return $urlKey;
    }

    /**
     * Add url sufix
     * @param string $url
     * @param string $controllerName
     * @return string
     */
    protected function addUrlSufix($url, $controllerName)
    {
        if (in_array($controllerName, [
            self::CONTROLLER_POST,
            self::CONTROLLER_CATEGORY,
            self::CONTROLLER_AUTHOR,
        ])) {
            if ($sufix = $this->getUrlSufix($controllerName)) {
                $char = false;
                foreach (['#', '?'] as $ch) {
                    if (false !== strpos($url, $ch)) {
                        $char = $ch;
                    }
                }
                if ($char) {
                    $data = explode($char, $url);
                    $data[0] = trim($data[0], '/') . $sufix;
                    $url = implode($char, $data);
                } else {
                    $url = trim($url, '/') . $sufix;
                }
            }
        }

        return $url;
    }

    /**
     * Retrieve trimmed url without sufix
     * @param string $identifier
     * @param string $sufix
     * @return string
     */
    public function trimSufix($identifier, $sufix)
    {
        if ($sufix) {
            $p = mb_strrpos($identifier, $sufix);
            if (false !== $p) {
                $li = mb_strlen($identifier);
                $ls = mb_strlen($sufix);
                if ($p + $ls == $li) {
                    $identifier = mb_substr($identifier, 0, $p);
                }
            }
        }

        return $identifier;
    }

    /**
     * Retrieve post url sufix
     * @return string
     */
    public function getUrlSufix($controllerName)
    {
        return trim((string)$this->_getConfig($controllerName . '_sufix'));
    }


    /**
     * @return int|null
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * @param $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
        return $this;
    }

    /**
     * Retrieve blog permalink config value
     * @param string $key
     * @return string || null || int
     */
    protected function _getConfig($key)
    {
        return $this->_scopeConfig->getValue(
            'gvblog/permalink/' . $key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
    }
}
