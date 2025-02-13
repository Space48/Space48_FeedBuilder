<?php

class Space48_FeedBuilder_Model_Data_Attribute_ProductFinalPrice extends Space48_FeedBuilder_Model_Data_Attribute_Abstract
{
    public function addCollectionAttribute(Varien_Data_Collection $collection)
    {
        return $collection->addAttributeToSelect(array('price', 'special_price'));
    }

    protected function _getValue(Mage_Core_Model_Abstract $model)
    {
        return sprintf("%.02f", $model->getFinalPrice());
    }
}