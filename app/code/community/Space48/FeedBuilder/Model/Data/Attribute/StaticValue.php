<?php

class Space48_FeedBuilder_Model_Data_Attribute_StaticValue extends Space48_FeedBuilder_Model_Data_Attribute_Abstract
{
    protected function _getValue(Mage_Core_Model_Abstract $model)
    {
        return $this->getStaticValue();
    }
}