<?php

abstract class Space48_FeedBuilder_Model_Data_Filter_Abstract extends Mage_Core_Model_Abstract
{
    public function addFilter(Mage_Eav_Model_Entity_Collection_Abstract $collection)
    {
        return $collection;
    }
}