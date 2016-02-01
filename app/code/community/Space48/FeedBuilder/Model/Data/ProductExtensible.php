<?php

class Space48_FeedBuilder_Model_Data_ProductExtensible
    extends Space48_FeedBuilder_Model_Data_Abstract
{
    public function __construct()
    {
        $this->setCollection($this->_getProductCollection());
    }

    protected function _getProductCollection()
    {
        return Mage::getModel('catalog/product')->getCollection();
    }
}