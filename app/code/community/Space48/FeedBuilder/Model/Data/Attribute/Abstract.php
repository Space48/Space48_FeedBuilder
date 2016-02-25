<?php

abstract class Space48_FeedBuilder_Model_Data_Attribute_Abstract extends Mage_Core_Model_Abstract
{
    protected $_dataField = 's48_abstract';

    public function addCollectionJoin(Varien_Data_Collection $collection)
    {
        return $collection;
    }

    public function addCollectionAttribute(Varien_Data_Collection $collection)
    {
        return $collection;
    }

    public function getDataField()
    {
        return $this->_dataField;
    }

    final public function getValue(Mage_Core_Model_Abstract $model)
    {
        $value = $this->_getValue($model);

        if (! $value) {
            return $value;
        }

        if ( $forcedCase = $this->getForceCase() ) {
            $value = $this->_forceCase($value, $forcedCase);
        }

        if ($this->getStripLineBreaks()) {
            $value = $this->_stripLineBreaks($value);
        }

        if ($this->getStripHtmlTags()) {
            $value = $this->_stripHtmlTags($value);
        }

        return $value;
    }

    protected function _getValue(Mage_Core_Model_Abstract $model)
    {
        $model->getData($this->_dataField);
    }

    protected function _forceCase($value, $forcedCase )
    {
        $forcedCase = strtolower($forcedCase);
        switch($forcedCase) {
            case self::FORCE_CASE_LOWER :
                $value = mb_convert_case($value, MB_CASE_LOWER, 'UTF-8');
                break;
            case self::FORCE_CASE_UPPER :
                $value = mb_convert_case($value, MB_CASE_UPPER, 'UTF-8');
                break;
            case self::FORCE_CASE_TITLE :
                $value = mb_convert_case($value, MB_CASE_LOWER, 'UTF-8');
                $value = mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
                break;
            default:
                break;
        }

        return $value;
    }

    protected function _stripLineBreaks($value) {
        return preg_replace("\r|\n", '', $value);
    }

    protected function _stripHtmlTags($value) {
        return strip_tags($value);
    }
}