<?php

class Space48_FeedBuilder_Model_Data_Filter_Store extends Space48_FeedBuilder_Model_Data_Filter_Abstract
{
    public function addFilter(Mage_Eav_Model_Entity_Collection_Abstract $collection)
    {
        $storeId = $this->getArgStoreId();
        Mage::app()->setCurrentStore($storeId);

        return $collection
            ->addStoreFilter($storeId)
            ->setStoreId($storeId);
    }

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

        return $storeId;
    }
}