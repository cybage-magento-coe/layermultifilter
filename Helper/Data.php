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
    private $multifilterSession;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry = null;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $productModel;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Catalog\Model\CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Repository
     */
    private $repository;

    /**
     * @var \Magento\Framework\Session\SessionManager
     */
    private $sessionManager;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    private $configurable;

    /**
     *
     * @param \Magento\Framework\Session\Generic $multifilterSession
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Catalog\Model\Product $productModel
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\CategoryRepository $categoryRepository
     * @param \Magento\Catalog\Model\Product\Attribute\Repository $repository
     */
    public function __construct(
        \Magento\Framework\Session\Generic $multifilterSession,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Catalog\Model\Product\Attribute\Repository $repository,
        \Magento\Framework\Session\SessionManager $sessionManager,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable
    ) {
        $this->multifilterSession = $multifilterSession;
        $this->coreRegistry = $coreRegistry;
        $this->productModel = $productModel;
        $this->scopeConfig = $scopeConfig;
        $this->categoryRepository = $categoryRepository;
        $this->repository = $repository;
        $this->sessionManager = $sessionManager;
        $this->configurable = $configurable;
    }

    /**
     * Functionality to get configuration values of plugin
     *
     * @param $configPath System xml config path
     * @return value of requested configuration
     */
    public function getConfig($configPath)
    {
        return $this->scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Function to fetch product collection based on selected filters
     *
     * @param $categories array of selected category
     * @param $attributes array of selected attributes
     * @return array of product collection
     */
    public function getProducts(
        $categories,
        $attributes
    ) {
        $current = $this->getCurrentCategory($categories);
        $currentCat = $current['currentcat'];
        $registerCat = $current['registercat'];
        $collection  = $this->getCollection($attributes, $currentCat, $registerCat);
        $parentsId = '';
        $productId = [];
        $filterData = [];

        foreach ($collection as $data) {
            $filterData[] = explode(',', $data->getStyleGeneral());
            $parentsId = $this->configurable->getParentIdsByChild($data->getId());
            if (!empty($parentsId)) {
                $finalArr[] = $parentsId[0];
            } else {
                $productId[] = $data->getId();
            }
        }

        if (!empty($finalArr)) {
            $implodedArr = array_unique($finalArr);
            return $implodedArr;
        } else {
            return array_unique($productId);
        }
    }
    
    /**
     * Function to get current category 
     *
     * @param type $categories
     * @return array of current category and registory category
     */
    public function getCurrentCategory($categories)
    {
        $registerCat = '';
        $currentCat = $this->coreRegistry->registry('current_category');
        if (empty($currentCat) && empty($categories)) {
            $catId = $this->multifilterSession->getTopCategory();
            $currentCat = $this->categoryRepository->get($catId);
        }
        if (!empty($categories)) {
            $registerCat = array_unique($categories);
        }
        return ['currentcat' => $currentCat, 'registercat' => $registerCat];
    }

    /**
     *
     * @param type $attributes
     * @param type $currentCat
     * @param type $registerCat
     * @return type
     */
    public function getCollection($attributes, $currentCat, $registerCat)
    {
        $collection = $this->productModel->getCollection();
        $collection->joinField(
            'category_id',
            'catalog_category_product',
            'category_id',
            'product_id = entity_id',
            null
        );
        if (!empty($registerCat)) {
            $collection->addFieldToFilter('category_id', ['in' => $registerCat]);
        } else {
            $childCategories = $currentCat->getChildrenCategories();
            foreach ($childCategories as $category) {
                $childCategoryValue[] = $category->getId();
            }
            if (!empty($childCategoryValue) && is_array($childCategoryValue)) {
                $collection->addFieldToFilter('category_id', ['in' => $childCategoryValue]);
            } else {
                $collection->addFieldToFilter('category_id', $currentCat->getId());
            }
        }
        if (!empty($attributes)) {
            $vals = [];
            foreach ($attributes as $data) {
                $vals[$data['name']][] = $data['value'];
                $frontendType = $this->checkAttribute($data['name']);
                if ($frontendType == 'multiselect') {
                    $filterFindSet[] = ['finset' => $data['value']];
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
                            $filters[] = ['from' => $price[0], 'to' => $price[1]];
                        } else {
                            $filters[] = ['gteq' => $price[0]];
                        }
                    }
                    $collection->addAttributeToFilter($k, [$filters]);
                } else {
                    $frontendType = $this->checkAttribute($k);
                    if ($frontendType != 'multiselect') {
                        $collection->addAttributeToFilter($k, ['in' => $v]);
                    }
                }
            }
        }
        return $collection;
    }

    /**
     * Function to fetch parent product collection based on child ids
     *
     * @param $implodedArr: array of childs from getProducts() function
     * @return array of parent product collection
     */
    public function getParentCollection(
        $implodedArr,
        $activeLimit,
        $activeSortOpt
    ) {
        $productCollection = $this->productModel->getCollection();

        if (empty($implodedArr)) {
            $implodedArr = [];
        }

        if (!empty($implodedArr)) {
            $productCollection->addFieldToFilter('entity_id', [$implodedArr]);
            $productCollection->addAttributeToSelect('*');
            $this->sessionManager->settotalCount(count($productCollection->getData()));
            $page = $this->sessionManager->getCurrentPage();
            if ($page > 1 && !empty($page)) {
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
            if ($activeSortOpt) {
                $productCollection->addAttributeToSort("{$activeSortOpt}", 'DESC');
            }
            return $productCollection;
        } else {
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
        $attributeColl = $this->repository->get($attributeCode);
        $frontendType = $attributeColl->getFrontendInput();
        return $frontendType;
    }
}
