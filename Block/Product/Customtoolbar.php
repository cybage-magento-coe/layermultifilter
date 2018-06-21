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

use Magento\Catalog\Helper\Product\ProductList;
use Magento\Catalog\Model\Product\ProductList\Toolbar as ToolbarModel;

class Customtoolbar extends \Magento\Catalog\Block\Product\ProductList\Toolbar
{

    /**
     * Products collection
     *
     * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    private $collection = null;

    /**
     * List of available order fields
     *
     * @var array
     */
    private $availableOrder = null;

    /**
     * List of available view types
     *
     * @var array
     */
    private $availableMode = [];

    /**
     * Is enable View switcher
     *
     * @var bool
     */
    private $enableViewSwitcher = true;

    /**
     * Is Expanded
     *
     * @var bool
     */
    private $isExpanded = true;

    /**
     * Default Order field
     *
     * @var string
     */
    private $orderField = null;

    /**
     * Default direction
     *
     * @var string
     */
    private $direction = ProductList::DEFAULT_SORT_DIRECTION;

    /**
     * Default View mode
     *
     * @var string
     */
    private $viewMode = null;

    /**
     * @var bool $paramsMemorizeAllowed
     */
    private $paramsMemorizeAllowed = true;

    /**
     * @var string
     */
    private $template = 'product/list/toolbar.phtml';

    /**
     * Catalog config
     *
     * @var \Magento\Catalog\Model\Config
     */
    private $catalogConfig;

    /**
     * Catalog session
     *
     * @var \Magento\Catalog\Model\Session
     */
    private $catalogSession;

    /**
     * @var ToolbarModel
     */
    private $toolbarModel;

    /**
     * @var ProductList
     */
    private $productListHelper;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    private $postDataHelper;

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
     * @var \Cybage\Layermultifilter\Helper\Data
     */
    private $helperData;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param ToolbarModel $toolbarModel
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param ProductList $productListHelper
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Catalog\Model\Config $catalogConfig,
        ToolbarModel $toolbarModel,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        ProductList $productListHelper,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Framework\Session\Generic $session,
        \Magento\Framework\Registry $coreRegistry,
        \Cybage\Layermultifilter\Helper\Data $helperData,
        array $data = []
    ) {
        $this->multifilterSession = $session;
        $this->coreRegistry = $coreRegistry;
        $this->helperData = $helperData;
        parent::__construct(
            $context,
            $catalogSession,
            $catalogConfig,
            $toolbarModel,
            $urlEncoder,
            $productListHelper,
            $postDataHelper,
            $data
        );
    }

    /**
     * Function to getPagerHtml to create custom pagination for ajax block
     *
     * @return pager block instance
     */
    public function getPagerHtml()
    {
        $categories = $this->multifilterSession->getCategories();
        $attributes = $this->multifilterSession->getAtrributes();
        $pagerBlock = $this->getChildBlock('product_list_toolbar_pager');
        $currentLimit = $this->multifilterSession->getActiveLimit();
        $currentSortOpt = $this->multifilterSession->getActiveSort();
        $implodedArr = $this->helperData->getProducts($categories, $attributes);
        $productCollection = $this->helperData->getParentCollection($implodedArr, $currentLimit, $currentSortOpt);
        if ($pagerBlock instanceof \Magento\Framework\DataObject) {
            ($currentLimit > 0) ? $pagerBlock->setLimit($currentLimit):$pagerBlock->setLimit(9);
            $pagerBlock->setCollection($productCollection);
            return $pagerBlock->toHtml();
        }
    }
    
    /**
     * Function to get Current category to create custom pagination for ajax block
     *
     * @return pager block instance
     */
    public function getCurrentCat()
    {
        if (empty($this->multifilterSession->getTopCategory()) && empty($this->multifilterSession->getCategories())) {
            $currentCat = $this->coreRegistry->registry('current_category');
            return $currentCat->getId();
        } else {
            return $this->multifilterSession->getTopCategory();
        }
    }
}
