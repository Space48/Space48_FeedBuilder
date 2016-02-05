<?php

class Space48_FeedBuilder_Model_Feed extends Mage_Core_Model_Abstract
{
    const STATUS_DISABLED = 'disabled';
    const DEFAULT_DATA_ITERATOR = 'Space48_FeedBuilder_Model_DataIterator_Basic';

    /** @var  Space48_FeedBuilder_Model_Data_Abstract */
    protected $_dataModel;
    /** @var  Space48_FeedBuilder_Model_DataIterator_Abstract */
    protected $_dataIteratorModel;
    /** @var  Space48_FeedBuilder_Model_Writer_Abstract */
    protected $_writerModel;

    protected function _isDisabled()
    {
        return $this->getStatus() == self::STATUS_DISABLED;
    }

    protected function _initialiseDataModel()
    {
        if (!($dataModel = $this->getDataModel('class'))) {
            Mage::throwException('Feed data model not defined');
        } elseif (!class_exists($dataModel)) {
            Mage::throwException('Feed data model does not exist :: ' . $dataModel);
        } else {
            $this->_dataModel = new $dataModel();
        }
    }

    protected function _initialiseDataIteratorModel()
    {
        if (!($dataIteratorModel = $this->getDataIteratorModel('class'))) {
            $dataIteratorModel = self::DEFAULT_DATA_ITERATOR;
        }
        if (!class_exists($dataIteratorModel)) {
            Mage::throwException('Feed data iterator model does not exist :: ' . $dataIteratorModel);
        } else {
            $this->_dataIteratorModel = new $dataIteratorModel($this->_dataModel);
        }
    }

    protected function _initialiseWriterModel()
    {
        if (!($writerModel = $this->getWriterModel('class'))) {
            Mage::throwException('Feed writer model not defined');
        } elseif (!class_exists($writerModel)) {
            Mage::throwException('Feed writer model does not exist :: ' . $writerModel);
        } else {
            $this->_writerModel = new $writerModel($this->getFileName(), $this->_dataModel);
        }
    }

    protected function _getAttributeModel($feedAttribute)
    {
        if (is_string($feedAttribute)) {
            $feedAttributeModel = Mage::getModel('space48_feedbuilder/data_attribute_basic');
            $feedAttributeModel->setDataField($feedAttribute);
        } elseif (is_array($feedAttribute) && isset($feedAttribute['class'])) {
            $feedAttributeModel = $feedAttribute['class'];
            if (!class_exists($feedAttributeModel)) {
                Mage::throwException('Attribute model does not exist :: ' . $feedAttributeModel);
            }

            $args = isset($feedAttribute['args']) ? $feedAttribute['args'] : array();
            $feedAttributeModel = new $feedAttributeModel($args);
        } else {
            Mage::throwException('Unable to handle feed attribute :: ' . print_r($feedAttribute, true));
        }

        return $feedAttributeModel;
    }

    protected function _getFilterModel($feedFilter)
    {
        if (is_array($feedFilter) && isset($feedFilter['class'])) {
            $filterModel = $feedFilter['class'];
            if (!class_exists($filterModel)) {
                Mage::throwException('Attribute model does not exist :: ' . $filterModel);
            }

            $args = isset($feedFilter['args']) ? $feedFilter['args'] : array();
            $filterModel = new $filterModel($args);
        } else {
            Mage::throwException('Unable to handle feed filter :: ' . print_r($feedFilter, true));
        }

        return $filterModel;
    }

    protected function _addDataFields()
    {
        foreach ($this->getFields() as $feedField => $feedAttribute) {
            $attributeModel = $this->_getAttributeModel($feedAttribute);
            $this->_dataModel->addFeedAttribute($feedField, $attributeModel);
        }
    }

    protected function _addDataFilters()
    {
        foreach ($this->getFilters() as $feedFilterName => $feedFilter) {
            $filterModel = $this->_getFilterModel($feedFilter);
            $this->_dataModel->addFeedFilter($feedFilterName, $filterModel);
        }
    }

    protected function _writeItems()
    {
        while($item = $this->_dataIteratorModel->getCollectionItem()){
            $this->_writerModel->writeItem($item);
        }
    }

    protected function _writeFeed()
    {
        foreach ($this->_writerModel->getSections() as $section) {
            if ($section == Space48_FeedBuilder_Model_Writer_Abstract::SECTION_ITEMS) {
                $this->_writeItems();
            } else {
                $this->_writerModel->writeSection($section);
            }
        }
    }

    public function createFeed()
    {
        if ($this->_isDisabled()) {
            echo $this->getName() .' :: disabled'.PHP_EOL;
            return;
        }
        echo $this->getName() .' :: creating'.PHP_EOL;

        $this->_initialiseDataModel();
        $this->_initialiseWriterModel();

        $this->_addDataFields();
        $this->_addDataFilters();

        $this->_initialiseDataIteratorModel();

        $this->_writeFeed();
    }
}