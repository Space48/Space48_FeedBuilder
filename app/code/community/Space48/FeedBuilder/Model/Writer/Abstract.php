<?php

abstract class Space48_FeedBuilder_Model_Writer_Abstract extends Mage_Core_Model_Abstract
    implements Space48_FeedBuilder_Model_Writer_Interface
{
    const SECTION_HEADER = 'header';
    const SECTION_BODY   = 'body';
    const SECTION_FOOTER = 'footer';

    protected $_fileName;
    /** @var Space48_FeedBuilder_Model_Data_Abstract  */
    protected $_feedData;
    protected $_feedType;
    protected $_fileHandle;

    public function __construct($fileName, Space48_FeedBuilder_Model_Data_Abstract $feedData)
    {
        $this->_fileName = Mage::getBaseDir() . '/' .$fileName;
        $this->_feedData = $feedData;
        $this->_fileHandle = $this->_openFileHandle();
    }

    public function __destruct()
    {
        $this->_closeFileHandle();
    }

    protected function _getHelper()
    {
        return Mage::helper('space48_feedbuilder');
    }

    protected function _getSections()
    {
        return array(self::SECTION_HEADER, self::SECTION_BODY, self::SECTION_FOOTER);
    }

    protected function _getIterationOfCollection()
    {
        return $this->_feedData->getIterationOfCollection();
    }

    protected function _openFileHandle()
    {
        Mage::getConfig()->createDirIfNotExists(dirname($this->_fileName));
        return fopen($this->_fileName, 'w');
    }

    protected function _closeFileHandle()
    {
        fclose($this->_fileHandle);
    }

    protected function _writeSection($feedSection)
    {
        $feedSection = ucfirst(strtolower($feedSection));
        $writeFunctionName = 'write'.$feedSection;
        $this->{$writeFunctionName}();
    }

    protected function _writeItems(Varien_Data_Collection $collection)
    {
        foreach ($collection as $item) {
            $this->writeItem($item);
        }
    }

    protected function _writeToHandle($string)
    {
        fwrite($this->_fileHandle, $string . PHP_EOL);
    }

    protected function writeBody()
    {
        while ($collectionIteration = $this->_getIterationOfCollection()) {
            $this->_writeItems($collectionIteration);
        }
    }

    public function writeFeed()
    {
        foreach ($this->_getSections() as $section) {
            $this->_writeSection($section);
        }
    }

}