<?php

class Space48_FeedBuilder_Model_Data_Filter_SaleableProducts extends Space48_FeedBuilder_Model_Data_Filter_Abstract
{
    public function addFilter(Mage_Catalog_Model_Resource_Product_Collection $collection)
    {
        return $collection->addAttributeToFilter(
            'status',
            array('eq' => Mage_Catalog_Model_Product_Status::getVisibleStatusIds())
        )->addAttributeToFilter(
            'visibility', array('in' => Mage_Catalog_Model_Product_Visibility::getVisibleInCatalogIds())
        );
    }
}