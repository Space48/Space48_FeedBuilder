<?php

class Space48_FeedBuilder_Model_Data_Filter_VisibleProducts extends Space48_FeedBuilder_Model_Data_Filter_Abstract
{
    public function addFilter(Mage_Eav_Model_Entity_Collection_Abstract $collection)
    {
        return $collection->addAttributeToFilter(
            'visibility', array('in' => array(
                Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG,
                Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH
            )));
    }
}