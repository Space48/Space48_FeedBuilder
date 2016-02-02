<?php

class Space48_FeedBuilder_Model_Writer_Csv
    extends Space48_FeedBuilder_Model_Writer_Abstract
{
    protected function _writeToHandle($fields)
    {
        fputcsv($this->_fileHandle, $fields);
    }

    public function writeHeader()
    {
        $this->_writeToHandle($this->_feedData->getFields());
    }

    public function writeItem(Mage_Core_Model_Abstract $item)
    {
        /** @TODO : Move this piece of functionality to the data model */
        /**
         * @var  $fieldName string
         * @var  $feedAttribute Space48_FeedBuilder_Model_Data_Attribute_Abstract
         */
        foreach ($this->_feedData->getFeedAttributes() as $fieldName => $feedAttribute) {
            $item = $feedAttribute->addCalculatedField($item);
        }

        $itemData = array();
        /**
         * @var  $fieldName string
         * @var  $feedAttribute Space48_FeedBuilder_Model_Data_Attribute_Abstract
         */
        foreach ($this->_feedData->getFeedAttributes() as $fieldName => $feedAttribute) {
            $itemData[] = $feedAttribute->getValue($item);
        }

        $this->_writeToHandle($itemData);
    }

    public function writeFooter()
    {
        return $this;
    }
}