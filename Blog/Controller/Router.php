<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Controller;

use Victory\Blog\Model\Url;
use Victory\Blog\Model\UrlResolver;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\Event\ManagerInterface;
use Victory\Blog\Model\PostFactory;
use Victory\Blog\Model\CategoryFactory;
use Victory\Blog\Model\AuthorFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ResponseInterface;

/**
 * Blog Controller Router
 */
class Router implements RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * Event manager
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Page factory
     *
     * @var \Victory\Blog\Model\PostFactory
     */
    protected $_postFactory;

    /**
     * Category factory
     *
     * @var \Victory\Blog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * Author factory
     *
     * @var   \Victory\Blog\Model\AuthorFactory
     */
    protected $_authorFactory;


    /**
     * Config primary
     *
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * Url
     *
     * @var \Victory\Blog\Model\Url
     */
    protected $_url;

    /**
     * Response
     *
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;

    /**
     * @var array;
     */
    protected $ids;

    /**
     * @var UrlResolverInterface
     */
    protected $urlResolver;

    /**
     * @param ActionFactory $actionFactory
     * @param ManagerInterface $eventManager
     * @param Url $url
     * @param PostFactory $postFactory
     * @param CategoryFactory $categoryFactory
     * @param AuthorFactory $authorFactory
     * @param StoreManagerInterface $storeManager
     * @param ResponseInterface $response
     * @param UrlResolver|null $urlResolver
     */
    public function __construct(
        ActionFactory         $actionFactory,
        ManagerInterface      $eventManager,
        Url                   $url,
        PostFactory           $postFactory,
        CategoryFactory       $categoryFactory,
        AuthorFactory         $authorFactory,
        StoreManagerInterface $storeManager,
        ResponseInterface     $response,
        UrlResolver           $urlResolver = null
    )
    {

        $this->actionFactory = $actionFactory;
        $this->_eventManager = $eventManager;
        $this->_url = $url;
        $this->_postFactory = $postFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->_authorFactory = $authorFactory;
        $this->_storeManager = $storeManager;
        $this->_response = $response;
        $this->urlResolver = $urlResolver ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Victory\Blog\Model\UrlResolver::class
        );
    }

    /**
     * Validate and Match Blog Pages and modify request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $pathInfo = $request->getPathInfo();
        $_identifier = trim($pathInfo, '/');
        $blogPage = $this->urlResolver->resolve($_identifier);

        if (!$blogPage || empty($blogPage['type']) || (empty($blogPage['id']))) {
            return null;
        }
        switch ($blogPage['type']) {
            case Url::CONTROLLER_INDEX:
                $blogPage['type'] = 'index';
                $actionName = 'index';
                $idKey = null;
                break;

            default:
                $actionName = 'view';
                $idKey = 'id';
        }

        $request
            ->setRouteName('blog')
            ->setControllerName($blogPage['type'])
            ->setActionName($actionName);

        if ($idKey) {
            $request->setParam($idKey, $blogPage['id']);
        }

        if (!empty($blogPage['params']) && is_array($blogPage['params'])) {
            foreach ($blogPage['params'] as $k => $v) {
                $request->setParam($k, $v);
            }
        }

        $condition = new \Magento\Framework\DataObject(
            [
                'identifier' => $_identifier,
                'request' => $request,
                'continue' => true
            ]
        );

        $this->_eventManager->dispatch(
            'victory_blog_controller_router_match_before',
            ['router' => $this, 'condition' => $condition]
        );

        if ($condition->getRedirectUrl()) {
            $this->_response->setRedirect($condition->getRedirectUrl());
            $request->setDispatched(true);
            return $this->actionFactory->create(
                \Magento\Framework\App\Action\Redirect::class,
                ['request' => $request]
            );
        }

        if (!$condition->getContinue()) {
            return null;
        }

        if (!$request->getModuleName()) {
            return null;
        }
        $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, ltrim($pathInfo, '/'));

        return $this->actionFactory->create(
            \Magento\Framework\App\Action\Forward::class,
            ['request' => $request]
        );
    }

    /**
     * Retrieve post id by identifier
     * @param string $identifier
     * @return int
     * @deprecated Use URL resolver interface instead
     */
    protected function _getPostId($identifier, $checkSufix = true)
    {
        return $this->getObjectId(
            $this->_postFactory,
            Url::CONTROLLER_POST,
            $identifier,
            $checkSufix
        );
    }

    /**
     * Retrieve category id by identifier
     * @param string $identifier
     * @return int
     * @deprecated Use URL resolver interface instead
     */
    protected function _getCategoryId($identifier, $checkSufix = true)
    {
        return $this->getObjectId(
            $this->_categoryFactory,
            Url::CONTROLLER_CATEGORY,
            $identifier,
            $checkSufix
        );
    }

    /**
     * Retrieve category id by identifier
     * @param string $identifier
     * @param bool $checkSufix
     * @return int
     * @deprecated Use URL resolver interface instead
     */
    protected function _getAuthorId($identifier, $checkSufix = true)
    {
        return $this->getObjectId(
            $this->_authorFactory,
            Url::CONTROLLER_AUTHOR,
            $identifier,
            $checkSufix
        );
    }


    /**
     * @param $factory
     * @param string $controllerName
     * @param string $identifier
     * @param bool $checkSufix
     * @return mixed
     * @deprecated Use URL resolver interface instead
     */
    protected function getObjectId($factory, $controllerName, $identifier, $checkSufix)
    {
        $key = $controllerName . '-' . $identifier . ($checkSufix ? '-checksufix' : '');
        if (!isset($this->ids[$key])) {
            $sufix = $this->_url->getUrlSufix($controllerName);

            $trimmedIdentifier = $this->_url->trimSufix($identifier, $sufix);

            if ($checkSufix && $sufix && $trimmedIdentifier == $identifier) { //if url without sufix
                $this->ids[$key] = 0;
            } else {
                $object = $factory->create();
                $this->ids[$key] = $object->checkUrlKey(
                    $trimmedIdentifier,
                    $this->_storeManager->getStore()->getId()
                );
            }
        }

        return $this->ids[$key];
    }
}
