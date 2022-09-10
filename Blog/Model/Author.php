<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Author Model
 */
class Author extends AbstractModel
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * @var string
     */
    protected $controllerName;

    public function __construct(
        Context          $context,
        Registry         $registry,
        Url              $url,
        AbstractResource $resource = null,
        AbstractDb       $resourceCollection = null,
        array            $data = []
    )
    {
        $this->_url = $url;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Victory\Blog\Model\ResourceModel\Author');
        $this->controllerName = URL::CONTROLLER_AUTHOR;
    }

    /**
     * Check if author url-key exist
     * return author id if author exists
     *
     * @param string $urlKey
     * @return int
     */
    public function checkUrlKey($urlKey)
    {
        $authors = $this->getCollection();
        foreach ($authors as $author) {
            if ($author->getUrlKey() == $urlKey) {
                return $author->getId();
            }
        }

        return 0;
    }

    /**
     * Retrieve author identifier
     * @return string | null
     */
    public function getUrlKey()
    {
        return preg_replace(
            "/[^A-Za-z0-9\-]/",
            '',
            strtolower($this->getName('-'))
        );
    }

    /**
     * Retrieve author name
     *
     * @param string $separator
     * @return string
     */
    public function getName($separator = ' ')
    {
        return $this->getFirstname() . $separator . $this->getLastname();
    }

    /**
     * Retrieve author url route path
     * @return string
     */
    public function getUrl()
    {
        return $this->_url->getUrlPath($this, URL::CONTROLLER_AUTHOR);
    }

    /**
     * Retrieve author url
     * @return string
     */
    public function getAuthorUrl()
    {
        return $this->_url->getUrl($this, URL::CONTROLLER_AUTHOR);
    }
}
