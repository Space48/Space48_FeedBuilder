<?php

class Space48_FeedBuilder_Model_Data_Filter_EnabledProducts extends Space48_FeedBuilder_Model_Data_Filter_Abstract
{
    public function addFilter(Mage_Eav_Model_Entity_Collection_Abstract $collection)
    {
        return $collection->addAttributeToFilter(
            'status',
            array( 'eq' => array(Mage_Catalog_Model_Product_Status::STATUS_ENABLED) )
        );
    }
}
