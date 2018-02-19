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
 * @package    Layermultifilter
 * @copyright  Copyright (c) 2014 Cybage Software Pvt. Ltd., India
 *             http://www.cybage.com/pages/centers-of-excellence/ecommerce/ecommerce.aspx
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Cybage Software Pvt. Ltd. <Support_ecom@cybage.com>
 */

namespace Cybage\Layermultifilter\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
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
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    
     /**
     * @var \Magento\Catalog\Model\CategoryRepository
     */
    protected $_categoryRepository;

    /**
     * @param Context $context
     * @param \Magento\Framework\Session\Generic $session
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Session\Generic $multifilterSession,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository
    ) {
        $this->_multifilterSession = $multifilterSession;
        $this->_coreRegistry = $coreRegistry;
        $this->_productModel = $productModel;
        $this->_scopeConfig = $scopeConfig;
        $this->_categoryRepository = $categoryRepository;
    }
    /**
     * Functionality to get configuration values of plugin
     *
     * @param $configPath: System xml config path
     * @return value of requested configuration
     */
    public function getConfig($configPath)
    {
        return $this->_scopeConfig->getValue($configPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    /**
     * Function to fetch product collection based on selected filters
     *
     * @param $categories: array of selected category
     * @param $attributes: array of selected attributes
     * @return array of product collection
     */
    public function getProducts($categories, $attributes)
    {
        $registerCat = '';
        $currentCat = $this->_coreRegistry->registry('current_category');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if (empty($currentCat) && empty($categories)) {
            $catId = $this->_multifilterSession->getTopCategory();
            $currentCat = $this->_categoryRepository->get($catId);
        }
        if (!empty($categories)) {
            $registerCat = array_unique($categories);
        }
        $collection = $this->_productModel->getCollection();
        $collection->joinField(
            'category_id',
            'catalog_category_product',
            'category_id',
            'product_id = entity_id',
            null
        );
        if (!empty($registerCat)) {
            $collection->addFieldToFilter(
                'category_id',
                array('in' => $registerCat)
            );
        } else {
            $childCategories = $currentCat->getChildrenCategories();
            foreach ($childCategories as $category) {
                $childCategoryValue[] = $category->getId();
            }
            if (!empty($childCategoryValue) && is_array($childCategoryValue)) {
                $collection->addFieldToFilter(
                    'category_id',
                    array('in' => $childCategoryValue)
                );
            } else {
                $collection->addFieldToFilter(
                    'category_id',
                    $currentCat->getId()
                );
            }
        }
       
        if (!empty($attributes)) {
            $finalArr = array();
            $vals = array();
            foreach ($attributes as $data) {
                $vals[$data['name']][] = $data['value'];
                $frontendType = $this->checkAttribute($data['name']);
                if ($frontendType == 'multiselect') {
                    $filterFindSet[] = array('finset' => $data['value']);
                }
            }
            if (!empty($filterFindSet)) {
                $collection->addFieldToFilter($data['name'], $filterFindSet);
            }
            foreach ($vals as $k => $v) {
                if ($k == 'price') {
                    $price = '';
                    foreach ($v as $part => $partTwo) {
                        $price = explode('-', $partTwo);
                        if ($price[1] != '') {
                            $filters[] = array('from' => $price[0], 'to' => $price[1]);
                        } else {
                            $filters[] = array('gteq' => $price[0]);
                        }
                    }
                    $collection->addAttributeToFilter($k, array($filters));
                } else {
                    $frontendType = $this->checkAttribute($k);
                    if ($frontendType != 'multiselect') {
                        $collection->addAttributeToFilter($k, array('in' => $v));
                    }
                }
            }
        }
        $parentsId = '';
        $productId = array();
        $filterData = array();

        foreach ($collection as $data) {
            $filterData[] = explode(',', $data->getStyleGeneral());
            $parentsId = $objectManager->get('\Magento\ConfigurableProduct\Model\Product\Type\Configurable')->getParentIdsByChild($data->getId());
            if (!empty($parentsId)) {
                $finalArr[] = $parentsId[0];
            } else {
                $productId[] = $data->getId();
            }
        }

        if (!empty($filterData)) {
            $flat = call_user_func_array('array_merge_recursive', $filterData);
            $uniq = array_values(array_unique($flat));
        }
       
        if (!empty($finalArr)) {
            $implodedArr = array_unique($finalArr);
            return $implodedArr;
        } else {
            return array_unique($productId);
        }
    }

    /**
     * Function to fetch parent product collection based on child ids
     *
     * @param $implodedArr: array of childs from getProducts() function
     * @return array of parent product collection
     */
    public function getParentCollection($implodedArr, $activeLimit, $activeSortOpt)
    {
		$productCollection = $this->_productModel->getCollection();
		
		if(empty($implodedArr))
		{
			$implodedArr = array(0);
		}
		
        if (!empty($implodedArr))
		{            
            $productCollection->addFieldToFilter('entity_id', array($implodedArr));
            $productCollection->addAttributeToSelect('*');
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $objectManager->get('\Magento\Framework\Session\SessionManager')->settotalCount(count($productCollection->getData()));
            $page = $objectManager->get('\Magento\Framework\Session\SessionManager')->getCurrentPage();
            
			if ($page > 1 && !empty($page))
			{
                $lastLimit = $activeLimit * $page + 1;
                $firstLimit = $activeLimit + 1;
                $productCollection->setCurPage($page);
                $productCollection->setPageSize($activeLimit);
            } else {
                if ($activeLimit) {
                    $productCollection->setPageSize($activeLimit);
                } else {
                    $productCollection->setPageSize(9);
                }
            }

            if ($activeSortOpt)
			{
                $productCollection->addAttributeToSort("{$activeSortOpt}", 'DESC');
            }

			return $productCollection;
        } else 
		{
			return $implodedArr;
        }
    }

    /**
     * Function to check attribute frontend type
     *
     * @param $attributeCode: attribute code
     * @return attribute frontend type
     */
    public function checkAttribute($attributeCode)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $attributeColl = $objectManager->get('\Magento\Catalog\Model\Product\Attribute\Repository')->get($attributeCode);
        $frontendType = $attributeColl->getFrontendInput();
        return $frontendType;
    }
}
