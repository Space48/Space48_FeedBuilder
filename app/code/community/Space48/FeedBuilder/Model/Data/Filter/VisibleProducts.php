<?php

class Space48_FeedBuilder_Model_Data_Filter_VisibleProducts extends Space48_FeedBuilder_Model_Data_Filter_Abstract
{
    public function addFilter(Mage_Catalog_Model_Resource_Product_Collection $collection)
    {
        return $collection->addAttributeToFilter(
            'visibility', array('in' => Mage_Catalog_Model_Product_Visibility::getVisibleInCatalogIds())
        );
    }
}