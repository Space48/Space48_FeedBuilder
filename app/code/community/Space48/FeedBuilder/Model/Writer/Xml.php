<?php

class Space48_FeedBuilder_Model_Writer_Xml
    extends Space48_FeedBuilder_Model_Writer_Abstract
{
    /** @var Varien_Simplexml_Element */
    protected $_feedSimpleXml;
    public function getSections()
    {
        return array(self::SECTION_HEADER, self::SECTION_ITEMS);
    }


    protected function _openFileHandle()
    {
        // when dealing with XML/Simplexml we don't require the file to be opened
        Mage::getConfig()->createDirIfNotExists(dirname($this->_fileName));
    }

    public function _closeFileHandle()
    {
        if ($this->_feedSimpleXml instanceof Varien_Simplexml_Element) {
            $this->_feedSimpleXml->saveXML($this->_fileName);
        } else {
            Mage::throwException('Feed was not generated');
        }
    }

    public function writeSection($section)
    {
        switch ($section) {
            case self::SECTION_HEADER:
                $data = new Varien_Object(array('external_wrapper' => null));
                $eventName = 'space48_feedbuilder_' . $this->_dataModel->getData('feed_code') . '_xml_feed';
                Mage::dispatchEvent($eventName, array('xml_wrapper' => $data));

                if (strpos($data->getData('external_wrapper'), '<?xml version="1.0"')) {
                    // if the xml header exists assume that the full header and wrapper were provided
                    $xmlWrapper = $data->getData('external_wrapper');
                } else if ($data->getData('external_wrapper') !== null) {
                    $xmlWrapper = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .  $data->getData('external_wrapper');
                } else {
                    Mage::throwException('The ' . $this->_dataModel->getData('feed_code') . 'xml feed needs an external wrapper');
                }

                $this->_feedSimpleXml = new Varien_Simplexml_Element($xmlWrapper);
                break;
            default:
                Mage::throwException('No function available to write section : '. $section);
        }
    }

    public function writeItem(Varien_Object $item)
    {
        $itemXml = $this->_feedSimpleXml->addChild($this->_dataModel->getData('item_wrapper'));
        /**
         * @var  $fieldName string
         * @var  $feedAttribute Space48_FeedBuilder_Model_Data_Attribute_Abstract
         */
        foreach ($this->_dataModel->getFeedAttributes() as $fieldName => $feedAttribute) {
            $value = $feedAttribute->getValue($item);
            // when we need xml to be generated for a group of things eg order items, quote items, configurable variants
            // the simplexml objects for each item should be returned in an array.
            // for an example check Space48_Bazaarvoice on Charlotetilbury.
            if (is_array($value) && $this->isArrayOfXMLs($value)) {
                $fieldXML = $itemXml->addChild($fieldName);
                foreach ($value as $member) {
                    $this->xmlAdopt($fieldXML, $member);
                }
            }
            if ($value !== null) {
                // more often than not when an attribute is empty in xml we just don't add it
                // instead of adding it as an empty element
                $itemXml->addChild($fieldName, $value);
            }

        }
    }

    protected function xmlAdopt(Varien_Simplexml_Element $root, Varien_Simplexml_Element $new)
    {
        $node = $root->addChild($new->getName(), (string) $new);
        foreach($new->attributes() as $attr => $value) {
            $node->addAttribute($attr, $value);
        }
        foreach($new->children() as $ch) {
            $this->xmlAdopt($node, $ch);
        }
    }

    //this would have been so much easier in a strictly typed language
    protected function isArrayOfXMLs($array)
    {
        if (!is_array($array) || empty($array)) {
            return false;
        }

        $isArrayOfXmls = true;
        foreach ($array as $member) {
            if (!$member instanceof Varien_Simplexml_Element) {
                $isArrayOfXmls = false;
                break;
            }
        }

        return $isArrayOfXmls;
    }

    public function writeFooter()
    {
        return $this;
    }
}
