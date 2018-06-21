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

class Ajax extends \Magento\Framework\App\Action\Action
{

    /**
     *
     * @var \Magento\Framework\Session\Generic
     */
    private $multifilterSession;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry = null;

    /**
     * Json factory
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory = null;

    /**
     * Url Interface factory
     *
     * @var \Magento\Framework\UrlInterface $urlInterface
     */
    private $urlInterface;
    
    /**
     *
     * @var \Magento\Framework\Session\SessionManager
     */
    protected $sessionManager;

    /**
     *
     * @param Context $context
     * @param \Magento\Framework\Session\Generic $session
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\UrlInterface $urlInterface
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Session\Generic $session,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\Session\SessionManager $sessionManager
    ) {
        $this->multifilterSession = $session;
        
        $this->coreRegistry = $coreRegistry;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->urlInterface = $urlInterface;
        $this->sessionManager = $sessionManager;
        parent::__construct($context);
    }

    /**
     * Intialization of request
     */
    public function execute()
    {
        $filters = $this->getRequest()->getParam('checkedFilter');
        $filterResult = $this->getFilterValues($filters);
        $categories = $filterResult['categories'];
        $attributes = $filterResult['attributes'];

        /** Fetching product collection based on selected filters */
        $activeLimit = $this->getRequest()->getParam('currentLimit');
        $activeSortOpt = $this->getRequest()->getParam('currentSortOpt');
        $viewMode = $this->getRequest()->getParam('viewmode');
        $currentPage = $this->getRequest()->getParam('currentPage');

        $this->multifilterSession->setType('custom');
        if ($currentPage) {
            $this->sessionManager->setCurrentPage($currentPage);
        } else {
            $this->sessionManager->setCurrentPage(1);
        }

        if ($activeLimit) {
            $this->multifilterSession->setActiveLimit($activeLimit);
        }

        if ($activeSortOpt) {
            $this->multifilterSession->setActiveSort($activeSortOpt);
        }

        if ($viewMode) {
            $this->multifilterSession->setViewMode($viewMode);
        }

        if (empty($categories)) {
            $this->multifilterSession->setTopCategory($this->getRequest()->getParam('categoryFilter'));
        } else {
            $this->multifilterSession->unsTopCategory();
        }

        $this->multifilterSession->setCategories(array_unique($categories));
        $this->multifilterSession->setAtrributes($attributes);
        $this->coreRegistry->register('type', '');
        $this->_view->loadLayout();
        $layout = $this->_view->getLayout();
        $block_list = $layout->getBlock('category.products.list');
        $block_contnt = $layout->getBlock('catalog.navigation.state');
        $data = [];
        $data['list'] = $block_list->toHtml();
        $data['content'] = $block_contnt->toHtml();
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($data);
    }

    /**
     * GetFilter values
     *
     * @param type $filters
     *
     * @return type
     */
    private function getFilterValues($filters)
    {
        $categories = [];
        $attributes = [];
        $result = [];
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
        $result['categories'] = $categories;
        $result['attributes'] = $attributes;
        return $result;
    }
}
