<?php

class Space48_FeedBuilder_Model_Data_Filter_ProductsSimpleOrParent
    extends Space48_FeedBuilder_Model_Data_Filter_Abstract
{
    public function addFilter(Mage_Eav_Model_Entity_Collection_Abstract $collection)
    {
        return $collection
            ->joinTable('catalog/product_relation', 'child_id=entity_id', array(
                'parent_id' => 'parent_id'
            ), null, 'left')
            ->addAttributeToFilter(array(
                array(
                    'attribute' => 'parent_id',
                    'null' => null
                )
            ));
    }
}