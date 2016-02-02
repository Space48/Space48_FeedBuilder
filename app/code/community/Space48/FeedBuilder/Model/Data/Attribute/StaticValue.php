<?php

class Space48_FeedBuilder_Model_Data_Attribute_StaticValue extends Space48_FeedBuilder_Model_Data_Attribute_Abstract
{
    public function getValue()
    {
        return $this->getStaticValue();
    }
}