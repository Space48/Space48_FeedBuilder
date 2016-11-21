<?php

/**
 * Class Space48_FeedBuilder_Model_Feed
 * @method getDataIteratorModel($nodeIdentifier) Space48_FeedBuilder_Model_DataIterator_Abstract
 * @method getWriterModel($nodeIdentifier) Space48_FeedBuilder_Model_Writer_Abstract
 * @method getDataModel($nodeIdentifier) Space48_FeedBuilder_Model_Data_Abstract
 * @method getStatus() string
 * @method getFileName() string
 * @method getName() string
 */

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

    protected function _initialiseDataModel($feedConfig)
    {
        if (!($dataModel = $this->getDataModel('class'))) {
            Mage::throwException('Feed data model not defined');
        } elseif (!class_exists($dataModel)) {
            Mage::throwException('Feed data model does not exist :: ' . $dataModel);
        } else {
            $this->_dataModel = new $dataModel($feedConfig);
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

        $feedConfig = $this->getData();
        $this->_initialiseDataModel($feedConfig);
        $this->_initialiseWriterModel();
        $this->_initialiseDataIteratorModel();

        $this->_writeFeed();
    }

    public function sendFeed()
    {
        if ($this->getSenderModel() && ($senderModel=$this->getSenderModel('class'))) {
            if (!class_exists($senderModel)) {
                Mage::throwException('Sender Model Doesn\'t Exist ;' . $senderModel);
            }

            $config = $this->getSenderModel();
            $config['local_filename'] = $this->getFileName();
            $senderModel = new $senderModel($config);
            $senderModel->send();
        }
    }
}