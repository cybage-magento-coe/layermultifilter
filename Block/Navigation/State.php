<?php

/**
 * Cybage Multifilter Layered Navigation Plugin
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

class State extends \Magento\LayeredNavigation\Block\Navigation\State
{
    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    protected $_catalogLayer;

    /**
     *
     * @var \Magento\Framework\Session\Generic
     */
    protected $_multifilterSession;

    /**
     * @var \Magento\Catalog\Model\CategoryRepository
     */
    protected $_categoryRepository;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute
     */
    protected $_entityAttribute;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection
     */
    protected $_attributeOptionCollection;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Framework\Session\Generic $multifilterSession,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Eav\Model\Entity\Attribute $entityAttribute,
        \Magento\Catalog\Model\Product\Attribute\Repository $productAttributeRepository,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection $attributeOptionCollection,
        array $data = []
    ) {
        $this->_catalogLayer = $layerResolver->get();
        $this->_multifilterSession = $multifilterSession;
        $this->_categoryRepository = $categoryRepository;
        $this->_entityAttribute = $entityAttribute;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->_attributeOptionCollection = $attributeOptionCollection;
        parent::__construct($context, $layerResolver, $data);
    }
    
    /**
     * Function to get categories stored in session
     */
    public function getSessionCategories()
    {
        return $this->_multifilterSession->getCategories();
    }
    
    /**
     * Function to get attributes stored in session
     */
    public function getSessionAttributes()
    {
        return $this->_multifilterSession->getAtrributes();
    }
    
    /**
     * Function to get active filters values in session
     */
    public function getActiveFilters()
    {
        if (!empty($this->_multifilterSession->getTopCategory())) {
            $filters = $this->_multifilterSession->getAtrributes();
            return $filters;
        } elseif (!empty($this->_multifilterSession->getCategories()) || !empty($this->_multifilterSession->getAtrributes())) {
            $filters = array_merge($this->_multifilterSession->getCategories(), $this->_multifilterSession->getAtrributes());
            return $filters;
        }
    }
    
    /**
     * Function to get filter label by filter
     */
    public function getFilterLabel($filter)
    {
        if (is_array($filter)) {
            $attributeCode = $filter['name'];
            $entityType = 'catalog_product';
            $filterLabels = explode(' ', $this->getAttributeInfo($entityType, $attributeCode)->getFrontendLabel());
            return $filterLabels[0];
        } else {
            $name = 'Category';
            return $name;
        }
    }
    
    /**
     * Function to get filter label by filter
     */
    public function getFilterValueName($filter)
    {
        if (is_array($filter)) {
            if ($filter['name'] == 'price') return $filter['value'];
            $attributeCode = $filter['name'];
            
            $formOptions = $this->productAttributeRepository->get($attributeCode)->getOptions();
            foreach ($formOptions as $formOption) {
                if ($formOption->getValue() == $filter['value']) {
                    return $formOption->getLabel();
                }
            }
        } else {
            $categoryObj = $this->_categoryRepository->get($filter);
            return $categoryObj->getName();
        }
    }

    /**
     * Load attribute data by code
     *
     * @param   mixed $entityType    Can be integer, string, or instance of class Mage\Eav\Model\Entity\Type
     * @param   string $attributeCode
     * @return  \Magento\Eav\Model\Entity\Attribute
     */
    public function getAttributeInfo($entityType, $attributeCode)
    {
        return $this->_entityAttribute->loadByCode($entityType, $attributeCode);
    }

    /**
     * Get filter id values
     */
    public function getFilterId($filter)
    {
        if (is_array($filter)) {
            return $filter['value'];
        } else {
            return $filter;
        }
    }
}
