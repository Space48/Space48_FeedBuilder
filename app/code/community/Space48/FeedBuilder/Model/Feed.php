<?php

class Space48_FeedBuilder_Model_Feed extends Mage_Core_Model_Abstract
{
    const STATUS_DISABLED = 'disabled';
    /** @var  Space48_FeedBuilder_Model_Writer_Abstract */
    protected $_feedWriterModel;
    /** @var  Space48_FeedBuilder_Model_Data_Abstract */
    protected $_feedDataModel;

    protected function _isDisabled()
    {
        return $this->getStatus() == self::STATUS_DISABLED;
    }

    protected function _initialiseFeedDataModel()
    {
        if(!($feedDataModel = $this->getDataModel('class'))) {
            Mage::throwException('Feed data model not defined');
        } elseif(!class_exists($feedDataModel)) {
            Mage::throwException('Feed data model does not exist :: '.$feedDataModel);
        } else {
            $this->_feedDataModel = new $feedDataModel();
        }
    }

    protected function _initialiseFeedWriterModel()
    {
        if(!($feedWriterModel = $this->getWriterModel('class'))) {
            Mage::throwException('Feed writer model not defined');
        } elseif(!class_exists($feedWriterModel)) {
            Mage::throwException('Feed writer model does not exist :: '.$feedWriterModel);
        } else {
            $this->_feedWriterModel = new $feedWriterModel($this->getFileName(), $this->_feedDataModel);
        }
    }

    protected function _getFeedAttributeModel($feedAttribute)
    {
        if (is_string($feedAttribute)) {
            $feedAttributeModel =  Mage::getModel('space48_feedbuilder/data_attribute_basic');
            $feedAttributeModel->setDataField($feedAttribute);
        } elseif (is_array($feedAttribute) && isset($feedAttribute['class'])) {
            $feedAttributeModel = $feedAttribute['class'];
            if (!class_exists($feedAttributeModel)){
                Mage::throwException('Attribute model does not exist :: '.$feedAttributeModel);
            }

            $args = isset($feedAttribute['args']) ? $feedAttribute['args'] : array();
            $feedAttributeModel = new $feedAttributeModel($args);
        } else {
            Mage::throwException('Unable to handle feed attribute :: '.print_r($feedAttribute, true));
        }

        return $feedAttributeModel;
    }

    protected function _getFeedFilterModel($feedFilter)
    {
        if (is_string($feedFilter)) {
            $feedFilterModel =  Mage::getModel('space48_feedbuilder/data_attribute_basic');
            $feedFilterModel->setDataField($feedFilter);
        } elseif (is_array($feedFilter) && isset($feedFilter['class'])) {
            $feedFilterModel = $feedFilter['class'];
            if (!class_exists($feedFilterModel)){
                Mage::throwException('Attribute model does not exist :: '.$feedFilterModel);
            }

            $args = isset($feedFilter['args']) ? $feedFilter['args'] : array();
            $feedFilterModel = new $feedFilterModel($args);
        } else {
            Mage::throwException('Unable to handle feed attribute :: '.print_r($feedFilter, true));
        }

        return $feedFilterModel;
    }

    protected function _addDataFields()
    {
        foreach($this->getFields() as $feedField => $feedAttribute) {
            $feedAttributeModel = $this->_getFeedAttributeModel($feedAttribute);
            $this->_feedDataModel->addFeedAttribute($feedField, $feedAttributeModel);
        }
    }

    protected function _addDataFilters()
    {
        foreach($this->getFilters() as $feedFilterName => $feedFilter) {
            $feedFilterModel = $this->_getFeedFilterModel($feedFilter);
            $this->_feedDataModel->addFeedFilter($feedFilterName, $feedFilterModel);
        }
    }

    protected function _writeFeed()
    {
        $this->_feedWriterModel->writeFeed();
    }

    public function createFeed()
    {
        if ($this->_isDisabled()) {
            echo $this->getName() .' :: disabled'.PHP_EOL;
            return;
        }
        echo $this->getName() .' :: creating'.PHP_EOL;

        $this->_initialiseFeedDataModel();
        $this->_initialiseFeedWriterModel();

        $this->_addDataFields();
        $this->_addDataFilters();
        $this->_writeFeed();
    }
}