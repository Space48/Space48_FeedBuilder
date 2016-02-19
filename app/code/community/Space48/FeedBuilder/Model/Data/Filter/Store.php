<?php

class Space48_FeedBuilder_Model_Data_Filter_Store extends Space48_FeedBuilder_Model_Data_Filter_Abstract
{
    public function addFilter(Varien_Data_Collection $collection)
    {
        $storeId = $this->getArgStoreId();
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

        $this->setRegistryStoreId($storeId);

        return $storeId;
    }

    private function setRegistryStoreId($storeId)
    {
        $currentRegistryStoreId = Mage::registry('feed_store_id');
        if (isset($currentRegistryStoreId)) {
            Mage::unregister('feed_store_id');
        }

        Mage::register('feed_store_id', $storeId);
    }
}