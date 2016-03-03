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
    /** @var  Space48_FeedBuilder_Model_Data_ProductExtensible */
    protected $_dataModel;
    /** @var  Varien_Data_Collection */
    protected $_childCollection;
    protected $_currentChildItemPosition;
    protected $_childItemReferences = array();

    public function __construct(Space48_FeedBuilder_Model_Data_ProductExtensible $dataModel)
    {
        parent::__construct($dataModel);
    }

    protected function _setChildItemReferenceArray()
    {
        $this->_childItemReferences = array();
        foreach ($this->_childCollection as &$item) {
            $this->_childItemReferences[] = $item;
        }
    }

    protected function _getChildItem(Mage_Catalog_Model_Product $parentProduct)
    {
        // loading a new collection of products
        if (! $this->_childCollection) {
            $this->_currentChildItemPosition = 0;
            $this->_childCollection = $this->_dataModel->getChildItems($parentProduct);
            $this->_setChildItemReferenceArray();
        }

        $this->_currentChildItemPosition++;
        if (!$this->_childCollection) {
            return false;
        }

        if($this->_currentChildItemPosition < $this->_childCollection->count()) {
            $this->_currentItemPosition--;
        }

        $item = $this->_childItemReferences[ $this->_currentChildItemPosition - 1];
        return $item;
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