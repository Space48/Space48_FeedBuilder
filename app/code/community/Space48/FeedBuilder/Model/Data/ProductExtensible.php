<?php

class Space48_FeedBuilder_Model_Data_ProductExtensible
    extends Space48_FeedBuilder_Model_Data_Abstract
{
    protected $_parentSpecificFilters = array(
        'Space48_FeedBuilder_Model_Data_Filter_ProductsSimpleOrParent',
        'Space48_FeedBuilder_Model_Data_Filter_VisibleProducts'
        );

    public function _construct()
    {
        $this->setCollection($this->_getBasicProductCollection());
    }

    protected function _getBasicProductCollection()
    {
        return Mage::getModel('space48_feedbuilder/resource_catalog_product_collection');
    }

    protected function _getChildCollection(array $childrenIds)
    {
        return Mage::getModel('catalog/product')
                ->getCollection()
                ->addIdFilter($childrenIds);
    }

    protected function _removeParentSpecificFilters($feedConfig)
    {
        if (!isset($feedConfig['filters'])) {
            return $feedConfig;
        }

        $filters = array();
        foreach ($feedConfig['filters'] as $filterName => $filterConfig) {
            if (!isset($filterConfig['class'])
                || ! in_array($filterConfig['class'], $this->_parentSpecificFilters)) {
                $filters[$filterName] = $filterConfig;
            }
        }
        $feedConfig['filters'] = $filters;
        return $feedConfig;
    }

    protected function _getChildDataModel()
    {
        if (!($dataModel = $this->getDataModel('class'))) {
            Mage::throwException('Feed data model for child collection not defined');
        } elseif (!class_exists($dataModel)) {
            Mage::throwException('Feed data model for child collection does not exist :: ' . $dataModel);
        } else {
            $feedConfig = $this->getData();
            $feedConfig = $this->_removeParentSpecificFilters($feedConfig);
            return new $dataModel($feedConfig);
        }
    }

    public function getChildItems(Mage_Catalog_Model_Product $parentProduct)
    {
        $childDataModel = $this->_getChildDataModel();
        $groupedChildrenIds = $parentProduct->getTypeInstance()->getChildrenIds($parentProduct->getId());
        if(!$groupedChildrenIds) {
            return false;
        }
        $childDataModel->setCollection($this->_getChildCollection($groupedChildrenIds[0]));
        $childDataModel->setItemsPerIteration(null);
        return $childDataModel->getIterationOfCollection();
    }
}