<?php

class Space48_FeedBuilder_Model_Data_Filter_Store extends Space48_FeedBuilder_Model_Data_Filter_Abstract
{
    protected function getArgStoreId()
    {
        if ($this->getStoreId()) {
            $storeId = $this->getStoreId();
        } elseif ($this->getStoreCode()) {
            $storeCode = $this->getStoreCode();
            $storeId =  Mage::app()->getStore($storeCode)->getId();
        } else {
            Mage::throwException('one of store id or store code must be supplied');
        }

        $this->setDefaultStore($storeId);

        return $storeId;
    }

    protected function setDefaultStore($storeId)
    {
        Mage::app()->setCurrentStore($storeId);
    }

    public function addFilter(Varien_Data_Collection $collection)
    {
        return $collection->addStoreFilter($this->getArgStoreId());
    }
}