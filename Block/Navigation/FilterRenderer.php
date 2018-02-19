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
 * @copyright  Copyright (c) 2014 Cybage Software Pvt. Ltd., India
 *             http://www.cybage.com/pages/centers-of-excellence/ecommerce/ecommerce.aspx
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Cybage Software Pvt. Ltd. <Support_ecom@cybage.com>
 */

namespace Cybage\Layermultifilter\Block\Navigation;

use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Framework\View\Element\Template;
use Cybage\Layermultifilter\Block\Navigation\FilterRendererInterface;

class FilterRenderer extends Template implements FilterRendererInterface
{
    const MINPRICE = 0;
    const MAXPRICE = 0;
    const GENERICPRICE = 10000000000;
    /**
     * Logging instance
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     *
     * @var \Magento\Framework\Session\Generic
     */
    protected $_multifilterSession;
    
    public function __construct(
        Template\Context $context,
        \Cybage\Layermultifilter\Helper\Data $helper,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Session\Generic $multifilterSession,
        array $data = []
    ) {

        parent::__construct($context, $data);
        $this->_multifilterSession = $multifilterSession;
        $this->_logger = $logger;
    }

    /**
     * @param FilterInterface $filter
     * @return string
     */
    public function render(FilterInterface $filter)
    {
        $this->assign('filter', $filter);
        $this->assign('filterItems', $filter->getItems());
        $html = $this->_toHtml();
        $this->assign('filterItems', []);
        return $html;
    }
    /**
     * Function to get min max price range of current filter
     *
     * @param $filter: filter instance
     * @return array of filter price
     */
    public function getPriceRange($filter)
    {
        $filterPrice = array('min' => self::MINPRICE, 'max' => self::MAXPRICE);
        if ($filter->getName() == 'Price') {
            $priceArr = $filter->getResource()->loadPrices(self::GENERICPRICE);
            $filterPrice['min'] = reset($priceArr);
            $filterPrice['max'] = end($priceArr);
        }
        return $filterPrice;
    }
    /**
     * Function to get filter url
     *
     * @param $filter: filter instance
     * @return filter url
     */
    public function getFilterUrl($filter)
    {
        $query = ['price' => ''];
        return $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => $query]);
    }
    
    /**
     * Function to get session categories
     */
    public function getSessionCategories()
    {
        return $this->_multifilterSession->getCategories();
    }
    
    /**
     * Function to get session attributes
     */
    public function getSessionAttributes()
    {
        return $this->_multifilterSession->getAtrributes();
    }
}
