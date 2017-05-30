<?php

class Space48_FeedBuilder_Model_Data_OrderExtensible
    extends Space48_FeedBuilder_Model_Data_Abstract
{
    public function _construct()
    {
        $this->setCollection($this->_getBasicProductCollection());
    }

    protected function _getBasicProductCollection()
    {
        return Mage::getModel('sales/order')->getCollection();
    }
}
