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
        if(!($feedDataModel = $this->getFeedDataModel('class'))) {
            Mage::throwException('Feed data model not defined');
        } elseif(!class_exists($feedDataModel)) {
            Mage::throwException('Feed data model does not exist :: '.$feedDataModel);
        } else {
            $this->_feedDataModel = new $feedDataModel();
        }
    }

    protected function _initialiseFeedWriterModel()
    {
        if(!($feedWriterModel = $this->getFeedWriterModel('class'))) {
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
            $feedAttributeModel = new $feedAttributeModel();
        } else {
            Mage::throwException('Unable to handle feed attribute :: '.print_r($feedAttribute, true));
        }

        return $feedAttributeModel;
    }

    protected function _addDataFields()
    {
        foreach($this->getFeedFields() as $feedField => $feedAttribute) {
            $feedAttributeModel = $this->_getFeedAttributeModel($feedAttribute);
            $this->_feedDataModel->addFeedAttribute($feedField, $feedAttributeModel);
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
        $this->_writeFeed();
    }
}