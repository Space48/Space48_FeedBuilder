<?php

class Space48_FeedBuilder_Model_Data_CustomerExtensible
    extends Space48_FeedBuilder_Model_Data_Abstract
{
    public function __construct()
    {
        $this->setCollection($this->_getCustomerCollection());
    }

    protected function _getCustomerCollection()
    {
        return Mage::getModel('customer/customer')->getCollection();
    }
}