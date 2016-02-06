<?php

class Space48_FeedBuilder_Model_Writer_Csv
    extends Space48_FeedBuilder_Model_Writer_Abstract
{

    public function getSections()
    {
        return array(self::SECTION_HEADER, self::SECTION_ITEMS);
    }

    protected function _writeToHandle($fields)
    {
        fputcsv($this->_fileHandle, $fields);
    }

    public function writeSection($section)
    {
        switch ($section) {
            case self::SECTION_HEADER:
                $this->_writeToHandle($this->_dataModel->getFeedFields());
                break;
            default:
                Mage::throwException('No function available to write section : '. $section);
        }
    }

    public function writeItem(Varien_Object $item)
    {
        $itemData = array();
        /**
         * @var  $fieldName string
         * @var  $feedAttribute Space48_FeedBuilder_Model_Data_Attribute_Abstract
         */
        foreach ($this->_dataModel->getFeedAttributes() as $fieldName => $feedAttribute) {
            $itemData[] = $feedAttribute->getValue($item);
        }

        $this->_writeToHandle($itemData);
    }

    public function writeFooter()
    {
        return $this;
    }
}