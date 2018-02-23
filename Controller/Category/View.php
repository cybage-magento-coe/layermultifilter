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

class View extends \Magento\Framework\App\Action\Action {

    /**
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
     * @var \Magento\Catalog\Model\Product
     */
    protected $_productModel;

    /**
     * @var \Cybage\Layermultifilter\Helper\Data
     */
    protected $_helper;

    /**
     * 
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Session\Generic $multifilterSession
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Catalog\Model\Product $productModel
     * @param \Cybage\Layermultifilter\Helper\Data $helper
     */
    public function __construct(
    \Magento\Framework\App\Action\Context $context, \Magento\Framework\Session\Generic $multifilterSession, \Magento\Framework\Registry $coreRegistry, \Magento\Catalog\Model\Product $productModel, \Cybage\Layermultifilter\Helper\Data $helper
    ) {
        $this->_multifilterSession = $multifilterSession;
        $this->_coreRegistry = $coreRegistry;
        $this->_productModel = $productModel;
        $this->_helper = $helper;
        parent::__construct($context);
    }

    /**
     * Intialization of abstract methode for \Magento\Framework\App\Action\Action
     */
    public function execute() {
        
    }

    /**
     * Manipulating core excute funtion for displaying multifiltered products
     *
     * @param $subject: instance of core controller excute function
     * @param $proceed: closure to decide after which step control will be jumped to core
     * @return ajax response of product collection
     */
    public function aroundExecute(\Magento\Catalog\Controller\Category\View $subject, \Closure $proceed) {
        $moduleActivation = $this->_helper->getConfig('multifilter/general/active');
        if ($moduleActivation == '1') {
            $returnValue = $proceed();
            $currentCat = $this->_coreRegistry->registry('current_category');
            if (!empty($currentCat) && !empty($this->_multifilterSession->getCurrentCategory())) {
                if ($currentCat->getId() != $this->_multifilterSession->getCurrentCategory()) {
                    $this->_multifilterSession->unsCategories();
                    $this->_multifilterSession->unsAtrributes();
                    $this->_multifilterSession->unsTopCategory();
                }
            }
            if (!empty($currentCat)) {
                $this->_multifilterSession->unsCurrentCategory();
                $this->_multifilterSession->setCurrentCategory($currentCat->getId());
            }
            $filters = $this->getRequest()->getParam('checkedFilter');
            $this->_multifilterSession->setType('custom');
            $this->_view->loadLayout();
            $layout = $this->_view->getLayout();
            $block = $layout->getBlock('category.products.list');
            $this->_view->loadLayoutUpdates();
            return $returnValue;
        } else {
            $this->_multifilterSession->setType('coreblock');
            $returnValue = $proceed();
            return $returnValue;
        }
    }

}
