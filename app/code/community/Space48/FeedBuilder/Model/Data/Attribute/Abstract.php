<?php

abstract class Space48_FeedBuilder_Model_Data_Attribute_Abstract extends Mage_Core_Model_Abstract
{
    protected $_dataField = 's48_abstract';

    public function addCollectionJoin(Varien_Data_Collection $collection)
    {
        return $collection;
    }

    public function addCollectionAttribute(Varien_Data_Collection $collection)
    {
        return $collection;
    }

    public function getDataField()
    {
        return $this->_dataField;
    }

    public function getValue(Mage_Core_Model_Abstract $model)
    {
        return $model->getData($this->_dataField);
    }
}