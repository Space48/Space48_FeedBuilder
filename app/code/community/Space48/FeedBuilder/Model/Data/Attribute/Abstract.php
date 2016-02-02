<?php

abstract class Space48_FeedBuilder_Model_Data_Attribute_Abstract extends Mage_Core_Model_Abstract
{
    protected $_dataField = 's48_abstract';

    protected function _calculateFieldValue(Mage_Core_Model_Abstract $model)
    {
        //return $this->getValue($model);
        return $model->getData($this->_dataField);
    }

    public function addCollectionJoin(Varien_Data_Collection $collection)
    {
        return $collection;
    }

    public function addCollectionAttribute(Varien_Data_Collection $collection)
    {
        return $collection;
    }

    public function addCalculatedField(Mage_Core_Model_Abstract $model)
    {
        return $model->setData(
            $this->_dataField,
            $this->_calculateFieldValue($model)
        );
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