<?php

interface Space48_FeedBuilder_Model_Writer_Interface
{
    public function writeHeader();

    public function writeItem(Mage_Core_Model_Abstract $item);

    public function writeFooter();
}