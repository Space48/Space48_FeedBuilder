<?php

class Space48_FeedBuilder_Model_Data_Attribute_ProductIsInStock extends Space48_FeedBuilder_Model_Data_Attribute_Abstract
{
    protected $_dataField = 's48_is_in_stock';

    public function addCollectionJoin(Varien_Data_Collection $collection)
    {
        return $collection->joinField($this->_dataField,
            'cataloginventory/stock_item',
            'is_in_stock',
            'product_id=entity_id',
            '{{table}}.stock_id=1',
            'left');
    }
}