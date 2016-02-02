<?php

class Space48_FeedBuilder_Model_Data_Attribute_ProductImage
    extends Space48_FeedBuilder_Model_Data_Attribute_Abstract
{
    protected $_dataField = 'small_image';

    public function addCollectionAttribute(Mage_Catalog_Model_Resource_Product_Collection $collection)
    {
        return $collection->addAttributeToSelect('small_image');
    }

    public function getValue(Mage_Core_Model_Abstract $model)
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product' . $model->getData($this->_dataField);
    }
}