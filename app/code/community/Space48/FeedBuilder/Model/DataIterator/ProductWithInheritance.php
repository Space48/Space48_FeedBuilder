<?php

/** for use with data filter as below:
 * <product_type>
 *  <class>Space48_FeedBuilder_Model_Data_Filter_ProductsSimpleOrParent</class>
 * </product_type>
 *
 * This will mean the collection being iterated contains parent products or simples with no parent.
 */
class Space48_FeedBuilder_Model_DataIterator_ProductWithInheritance
    extends Space48_FeedBuilder_Model_DataIterator_Abstract
{
    protected $_productTypeFilters = array('Space48_FeedBuilder_Model_Data_Filter_ProductsSimpleOrParent');
    protected $_childCollection;
    protected $_currentChildItemPosition;
    protected $_childItemReferences = array();

    protected function _removeProductTypeFilter($feedConfig)
    {
        if (!isset($feedConfig['filters'])) {
            return $feedConfig;
        }

        $filters = array();
        foreach ($feedConfig['filters'] as $filterName => $filterConfig) {
            if (!isset($filterConfig['class'])
                || ! in_array($filterConfig['class'], $this->_productTypeFilters)) {
                $filters[$filterName] = $filterConfig;
            }
        }
        $feedConfig['filters'] = $filters;
        return $feedConfig;
    }

    protected function _getChildDataModel()
    {
        if (!($dataModel = $this->_dataModel->getDataModel('class'))) {
            Mage::throwException('Feed data model for child collection not defined');
        } elseif (!class_exists($dataModel)) {
            Mage::throwException('Feed data model for child collection does not exist :: ' . $dataModel);
        } else {
            $feedConfig = $this->_dataModel->getData();
            $feedConfig = $this->_removeProductTypeFilter($feedConfig);
            return new $dataModel($feedConfig);
        }
    }

    protected function _getChildItem(Mage_Catalog_Model_Product $product)
    {
        // loading a new collection of products
        if (! $this->_childCollection) {
            $this->_currentChildItemPosition = 0;
            $childDataModel = $this->_getChildDataModel();
            $childrenIds = $product->getTypeInstance()->getChildrenIds($product->getId());
            if(!$childrenIds) {
                return false;
            }
            $childCollection =
                Mage::getModel('catalog/product')
                    ->getCollection()
                    ->addIdFilter($childrenIds[0]);
            $childDataModel->setCollection($childCollection);
            $childDataModel->setItemsPerIteration(10000);
            $this->_childCollection = $childDataModel->getIterationOfCollection();
            $this->_setChildItemReferenceArray();
        }

        $this->_currentChildItemPosition++;
        if (!$this->_childCollection) {
            $item = false;
        } else {
            // Make the next loop call this function again.
            if($this->_currentChildItemPosition < $this->_childCollection->count()) {
                $this->_currentItemPosition--;
            }

            $item = $this->_childItemReferences[ $this->_currentChildItemPosition - 1];
        }

        return $item;
    }

    protected function _setChildItemReferenceArray()
    {
        $this->_childItemReferences = array();
        foreach ($this->_childCollection as &$item) {
            $this->_childItemReferences[] = $item;
        }
    }

    public function getCollectionItem()
    {
        if ($this->_currentItemPosition >= $this->_collection->count()) {
            $this->_getIterationOfCollection();
        }

        $this->_currentItemPosition++;
        // no more items.
        if (!$this->_collection) {
            return false;
        }
        /** @var  $item Mage_Catalog_Model_Product */
        $item = $this->_itemReferences[ $this->_currentItemPosition - 1];
        if ($item->getTypeId() !== Mage_Catalog_Model_Product_Type::TYPE_SIMPLE
            && ($childItem = $this->_getChildItem($item)) ) {
            $mergedItemData = array_merge($item->getData(), $childItem->getData());
            $item = Mage::getModel('catalog/product', $mergedItemData);
        }

        return $item;
    }
}