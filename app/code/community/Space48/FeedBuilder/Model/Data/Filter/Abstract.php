<?php

abstract class Space48_FeedBuilder_Model_Data_Filter_Abstract extends Mage_Core_Model_Abstract
{
    // if this is meant to deal with orders as well as products we need the collection to be able to be an eav one
    // as well as a non-eav one
    public function addFilter(Varien_Data_Collection_Db $collection)
    {
        return $collection;
    }
}
