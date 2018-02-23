<?php

/**
 * Cybage Layermultifilter Layered Navigation Plugin
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is available on the World Wide Web at:
 * http://opensource.org/licenses/osl-3.0.php
 * If you are unable to access it on the World Wide Web, please send an email
 * To: Support_ecom@cybage.com.  We will send you a copy of the source file.
 *
 * @category   Layermultifilter Layered Navigation Plugin
 * @package    Cybage_Layermultifilter
 * @copyright  Copyright (c) 2016 Cybage Software Pvt. Ltd., India
 *             http://www.cybage.com/pages/centers-of-excellence/ecommerce/ecommerce.aspx
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Cybage Software Pvt. Ltd. <Support_ecom@cybage.com>
 */

namespace Cybage\Layermultifilter\Controller\Category;

use Magento\Framework\App\Action\Context;

class Ajax extends \Magento\Framework\App\Action\Action {

    /**
     *
     * @var \Magento\Framework\Session\Generic
     */
    protected $_multifilterSession;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Json factory
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory = null;

    /**
     * Url Interface factory
     *
     * @var \Magento\Framework\UrlInterface $urlInterface
     */
    protected $_urlInterface;

    /**
     * 
     * @param Context $context
     * @param \Magento\Framework\Session\Generic $multifilterSession
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\UrlInterface $urlInterface
     */
    public function __construct(
    \Magento\Framework\App\Action\Context $context, \Magento\Framework\Session\Generic $multifilterSession, \Magento\Framework\Registry $coreRegistry, \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory, \Magento\Framework\UrlInterface $urlInterface
    ) {
        $this->_multifilterSession = $multifilterSession;
        $this->_coreRegistry = $coreRegistry;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_urlInterface = $urlInterface;
        parent::__construct($context);
    }

    /**
     * Intialization of request
     */
    public function execute() {
        $filters = $this->getRequest()->getParam('checkedFilter');
        $categories = [];
        $attributes = [];

        if (!empty($filters)) {
            foreach ($filters as $data) {
                $filterArr[] = explode('?', $data);
                $i = 0;
                foreach ($filterArr as $key => $value) {
                    if ($value[0] == 'category') {
                        $categories[] = $value[2];
                    }

                    if ($value[0] == 'attribute') {
                        $attributes[$i]['name'] = $value[1];
                        $attributes[$i]['value'] = $value[2];
                    }
                    $i++;
                }
            }
        }

        /** Fetching product collection based on selected filters */
        $activeLimit = $this->getRequest()->getParam('currentLimit');
        $activeSortOpt = $this->getRequest()->getParam('currentSortOpt');
        $viewMode = $this->getRequest()->getParam('viewmode');
        $currentPage = $this->getRequest()->getParam('currentPage');

        $this->_multifilterSession->setType('custom');
        if ($currentPage) {
            $this->_objectManager->get('\Magento\Framework\Session\SessionManager')->setCurrentPage($currentPage);
        } else {
            $this->_objectManager->get('\Magento\Framework\Session\SessionManager')->setCurrentPage(1);
        }

        if ($activeLimit) {
            $this->_multifilterSession->setActiveLimit($activeLimit);
        }

        if ($activeSortOpt) {
            $this->_multifilterSession->setActiveSort($activeSortOpt);
        }

        if ($viewMode) {
            $this->_multifilterSession->setViewMode($viewMode);
        }

        if (empty($categories)) {
            $this->_multifilterSession->setTopCategory($this->getRequest()->getParam('categoryFilter'));
        } else {
            $this->_multifilterSession->unsTopCategory();
        }

        $this->_multifilterSession->setCategories(array_unique($categories));
        $this->_multifilterSession->setAtrributes($attributes);
        $this->_coreRegistry->register('type', '');

        $this->_view->loadLayout();
        $layout = $this->_view->getLayout();
        $block_list = $layout->getBlock('category.products.list');
        $block_contnt = $layout->getBlock('catalog.navigation.state');
        $data = array(
            'list' => $block_list->toHtml(),
            'content' => $block_contnt->toHtml(),
        );
        $resultJson = $this->_resultJsonFactory->create();
        return $resultJson->setData($data);
    }

}
