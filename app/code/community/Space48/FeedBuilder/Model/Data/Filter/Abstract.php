<?php

abstract class Space48_FeedBuilder_Model_Data_Filter_Abstract extends Mage_Core_Model_Abstract
{
    public function addFilter(Varien_Data_Collection $collection)
    {
        return $collection;
    }
}