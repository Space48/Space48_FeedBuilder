<?php

class Space48_FeedBuilder_Model_Data_Attribute_ProductUrl extends Space48_FeedBuilder_Model_Data_Attribute_Abstract
{
    public function addCollectionJoin(Varien_Data_Collection $collection)
    {
        return $collection->addUrlRewrite();
    }

    public function getValue(Mage_Core_Model_Abstract $model)
    {
        /* @TODO : find core way of find url without manually removing script name */
        return str_replace($_SERVER[SCRIPT_NAME] . '/', '', $model->getProductUrl());
    }
}