<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Controller\Adminhtml;

use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Abstract admin controller
 */
abstract class Actions extends Action
{
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
     * Collection class name
     * @var string
     */
    protected $_collectionClass;

    /**
     * Active menu key
     * @var string
     */
    protected $_activeMenu;

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
     * Model Object
     * @var \Magento\Framework\Model\AbstractModel
     */
    protected $_model;

    /**
     * @var Filter
     */
    private $_filter;

    /**
     * @param Context $context
     * @param Filter $filter
     */
    public function __construct(
        Context $context,
        Filter  $filter
    )
    {
        $this->_filter = $filter;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $allActions = ['index', 'add', 'edit', 'save', 'delete', 'massStatus'];
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
     * Categories listing
     *
     * @return void
     */

    protected function indexAction()
    {
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
     * @param boolean $plural
     * @return string
     */
    protected function _getModelName(\Magento\Framework\Model\AbstractModel $model)
    {
        return $model->getName() ?: $model->getTitle();
    }

    /**
     * Save action
     * @return void
     */
    public function saveAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $this->getResponse()->setRedirect($this->getUrl('*/*'));
        }
        $model = $this->_getModel();

        try {
            $params = $request->getParams();

            $idFieldName = $model->getResource()->getIdFieldName();
            if (isset($params[$idFieldName]) && empty($params[$idFieldName])) {
                unset($params[$idFieldName]);
            }
            $model->addData($params);
            $this->_beforeSave($model, $request);
            $model->save();
            $this->_afterSave($model, $request);

            $this->messageManager->addSuccess(__('%1 has been saved.', $model->getOwnTitle()));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError(nl2br($e->getMessage()));
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __(
                    'Something went wrong while saving this %1. %2',
                    strtolower($model->getOwnTitle()),
                    $e->getMessage()
                )
            );
        }

        $hasError = (bool)$this->messageManager->getMessages()->getCountByType(
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );

        if ($hasError || $request->getParam('back')) {
            $this->_redirect('*/*/edit', [$this->_idKey => $model->getId()]);
        } else {
            $this->_redirect('*/*');
        }
    }

    /**
     * Before model Save action
     * @return void
     */
    protected function _beforeSave($model, $request)
    {
    }

    /**
     * After model action
     * @return void
     */
    protected function _afterSave($model, $request)
    {
    }

    /**
     * Delete action
     * @return void
     */
    protected function deleteAction()
    {
        $ids = (array)$this->getRequest()->getParam($this->_idKey);
        if (!$ids) {
            $collection = $this->_filter->getCollection($this->_objectManager->create($this->_collectionClass));
            $ids = $collection->getAllIds();
        }

        $error = false;
        try {
            foreach ($ids as $id) {
                $this->_objectManager->create($this->_modelClass)->load($id)->delete();
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $error = true;
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $error = true;
            $this->messageManager->addException(
                $e,
                __(
                    "We can't delete %1 right now. %2",
                    strtolower($this->_getModel(false)->getOwnTitle()),
                    $e->getMessage()
                )
            );
        }

        if (!$error) {
            $this->messageManager->addSuccess(
                __('%1 have been deleted.', $this->_getModel(false)->getOwnTitle(count($ids) > 1))
            );
        }

        $this->_redirect('*/*');
    }

    /**
     * Change status action
     * @return void
     */
    protected function massStatusAction()
    {
        $collection = $this->_filter->getCollection($this->_objectManager->create($this->_collectionClass));
        $ids = $collection->getAllIds();

        $model = $this->_getModel(false);

        $error = false;

        try {
            $status = $this->getRequest()->getParam('status');
            $statusFieldName = $this->_statusField;

            if (is_null($ids)) {
                throw new \Exception(__('Parameter "Status" missing in request data.'));
            }

            if (is_null($statusFieldName)) {
                throw new \Exception(__('Status Field Name is not specified.'));
            }

            foreach ($ids as $id) {
                $this->_objectManager->create($this->_modelClass)
                    ->load($id)
                    ->setData($this->_statusField, $status)
                    ->save();
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $error = true;
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $error = true;
            $this->messageManager->addException(
                $e,
                __(
                    "We can't change status of %1 right now. %2",
                    strtolower($model->getOwnTitle()),
                    $e->getMessage()
                )
            );
        }

        if (!$error) {
            $this->messageManager->addSuccess(
                __('%1 status have been changed.', $model->getOwnTitle(count($ids) > 1))
            );
        }

        $this->_redirect('*/*');
    }
}
