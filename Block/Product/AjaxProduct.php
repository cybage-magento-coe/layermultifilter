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

namespace Cybage\Layermultifilter\Block\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;

class AjaxProduct extends \Magento\Catalog\Block\Product\ListProduct implements IdentityInterface
{

    /**
     * Default toolbar block name
     *
     * @var string
     */
    protected $_defaultToolbarBlock = 'Customtoolbar';

    /**
     * Product Collection
     *
     * @var AbstractCollection
     */
    protected $_productCollection;

    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    protected $_catalogLayer;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $_postDataHelper;

    /**
     *
     * @var \Magento\Framework\Session\Generic
     */
    public $multifilterSession;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    public $coreRegistry = null;

    /**
     * @param Context $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Framework\Session\Generic $multifilterSession,
        \Magento\Framework\Registry  $coreRegistry,
        array $data = []
    ) {
        $this->multifilterSession = $multifilterSession;
        $this->coreRegistry = $coreRegistry;
        
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data
        );
    }

    /**
     * Function to check wheather the request is from Ajax
     * @return boolean
     */
    public function isAjax()
    {
        return $this->_request->isXmlHttpRequest() || $this->_request->getParam('isAjax');
    }
}
