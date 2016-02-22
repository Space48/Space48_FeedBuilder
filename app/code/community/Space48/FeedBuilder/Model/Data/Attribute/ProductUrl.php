<?php

class Space48_FeedBuilder_Model_Data_Attribute_ProductUrl extends Space48_FeedBuilder_Model_Data_Attribute_Abstract
{
    public function addCollectionJoin(Varien_Data_Collection $collection)
    {
        return $collection->addUrlRewrite();
    }

    public function getValue(Mage_Core_Model_Abstract $model)
    {
        return $model->getProductUrl();
    }
}