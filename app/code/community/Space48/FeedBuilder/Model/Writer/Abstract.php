<?php

abstract class Space48_FeedBuilder_Model_Writer_Abstract extends Mage_Core_Model_Abstract
{
    const SECTION_HEADER = 'header';
    const SECTION_ITEMS   = 'items';
    const SECTION_FOOTER = 'footer';

    protected $_fileName;
    /** @var Space48_FeedBuilder_Model_Data_Abstract  */
    protected $_feedData;
    protected $_feedType;
    protected $_fileHandle;

    public function __construct($fileName)
    {
        $this->_fileName = Mage::getBaseDir() . '/' .$fileName;
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

    abstract public function getSections();

    protected function _openFileHandle()
    {
        Mage::getConfig()->createDirIfNotExists(dirname($this->_fileName));
        return fopen($this->_fileName, 'w');
    }

    protected function _closeFileHandle()
    {
        fclose($this->_fileHandle);
    }

    abstract public function writeItem(Varien_Object $item);

    protected function _writeToHandle($string)
    {
        fwrite($this->_fileHandle, $string . PHP_EOL);
    }

}