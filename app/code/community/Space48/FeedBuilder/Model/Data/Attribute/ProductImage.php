<?php

class Space48_FeedBuilder_Model_Data_Attribute_ProductImage
    extends Space48_FeedBuilder_Model_Data_Attribute_Abstract
{
    protected $_dataField = 'small_image';

    public function addCollectionAttribute(Mage_Catalog_Model_Resource_Product_Collection $collection)
    {
        return $collection->addAttributeToSelect('small_image');
    }
}