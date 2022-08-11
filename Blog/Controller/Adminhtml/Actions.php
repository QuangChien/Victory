<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Controller\Adminhtml;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Backend\App\Action;

/**
 * Abstract admin controller
 */
abstract class Actions extends Action
{
    /**
     * Form session key
     * @var string
     */
    protected $_formSessionKey;

    /**
     * Allowed Key
     * @var string
     */
    protected $_allowedKey;

    /**
     * Model class name
     * @var string
     */
    protected $_modelClass;

    /**
     * Active menu key
     * @var string
     */
    protected $_activeMenu;

    /**
     * Store config section key
     * @var string
     */
    protected $_configSection;

    /**
     * Request id key
     * @var string
     */
    protected $_idKey = 'id';

    /**
     * Status field name
     * @var string
     */
    protected $_statusField = 'status';

    /**
     * Save request params key
     * @var string
     */
    protected $_paramsHolder;

    /**
     * Model Object
     * @var \Magento\Framework\Model\AbstractModel
     */
    protected $_model;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;


    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @param Action\Context $context
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context                $context,
        DataPersistorInterface $dataPersistor
    )
    {
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $allActions = ['index', 'grid', 'add', 'edit', 'save', 'duplicate', 'delete', 'config', 'massStatus'];
        $_action = $this->getRequest()->getActionName();
        if (in_array($_action, $allActions)) {
            $method = $_action . 'Action';
            $this->$method();
        }
    }

    /**
     * Check is allowed access
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed($this->_allowedKey);
    }

    /**
     * Category listing
     *
     * @return void
     */
    protected function indexAction()
    {
        if ($this->getRequest()->getParam('ajax')) {
            $this->_forward('grid');
            return;
        }

        $this->_view->loadLayout();
        $this->_setActiveMenu($this->_activeMenu);
        $title = __('Manage %1', $this->_getModel(false)->getOwnTitle(true));
        $this->_view->getPage()->getConfig()->getTitle()->prepend($title);
        $this->_addBreadcrumb($title, $title);
        $this->_view->renderLayout();
    }

    /**
     * Retrieve model object
     * @return \Magento\Framework\Model\AbstractModel
     */
    protected function _getModel($load = true)
    {
        if (is_null($this->_model)) {
            $this->_model = $this->_objectManager->create($this->_modelClass);

            $id = (int)$this->getRequest()->getParam($this->_idKey);
            $idFieldName = $this->_model->getResource()->getIdFieldName();
            if (!$id && $this->_idKey !== $idFieldName) {
                $id = (int)$this->getRequest()->getParam($idFieldName);
            }

            if ($id && $load) {
                $this->_model->load($id);
            }
        }
        return $this->_model;
    }

    /**
     * New action
     * @return void
     */
    protected function addAction()
    {
        $this->_forward('edit');
    }

    /**
     * Edit action
     * @return void
     */
    public function editAction()
    {
        try {
            $model = $this->_getModel();
            $id = $this->getRequest()->getParam('id');

            if (!$model->getId() && $id) {
                throw new \Exception("Item is not longer exist.", 1);
            }
            $this->_getRegistry()->register('current_model', $model);

            $this->_view->loadLayout();
            $this->_setActiveMenu($this->_activeMenu);

            $title = $model->getOwnTitle();
            if ($model->getId()) {
                $breadcrumbTitle = __('Edit %1', $title);
                $breadcrumbLabel = $breadcrumbTitle;
            } else {
                $breadcrumbTitle = __('Add %1', $title);
                $breadcrumbLabel = __('Create %1', $title);
            }
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__($title));
            $this->_view->getPage()->getConfig()->getTitle()->prepend(
                $model->getId() ? $this->_getModelName($model) : __('Add %1', $title)
            );

            $this->_addBreadcrumb($breadcrumbLabel, $breadcrumbTitle);

            $this->_view->renderLayout();
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __(
                    'Something went wrong: %1',
                    $e->getMessage()
                )
            );
            $this->_redirect('*/*/');
        }
    }

    /**
     * Retrieve model name
     * @param  boolean $plural
     * @return string
     */
    protected function _getModelName(\Magento\Framework\Model\AbstractModel $model)
    {
        return $model->getName() ?: $model->getTitle();
    }

    /**
     * Get core registry
     * @return void
     */
    protected function _getRegistry()
    {
        if (is_null($this->_coreRegistry)) {
            $this->_coreRegistry = $this->_objectManager->get(\Magento\Framework\Registry::class);
        }
        return $this->_coreRegistry;
    }
}
