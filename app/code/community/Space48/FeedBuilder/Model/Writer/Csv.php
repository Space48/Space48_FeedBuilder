<?php

class Space48_FeedBuilder_Model_Writer_Csv
    extends Space48_FeedBuilder_Model_Writer_Abstract
{
    public function writeHeader()
    {
        echo implode(',', $this->_feedData->getFields()).PHP_EOL;
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
        foreach($this->_feedData->getFeedAttributes() as $fieldName => $feedAttribute) {
            $itemData[] = $feedAttribute->getValue($item);
        }
        echo implode(',', $itemData).PHP_EOL;
    }

    public function writeFooter()
    {
        return $this;
    }
}