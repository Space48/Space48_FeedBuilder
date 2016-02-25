<?php

class Space48_FeedBuilder_Model_Data_Attribute_Additional extends Space48_FeedBuilder_Model_Data_Attribute_Abstract
{

    public function addCollectionAttribute(Varien_Data_Collection $collection)
    {
        return $collection->addAttributeToSelect($this->getAttributeCode());
    }

    protected function _getValue(Mage_Core_Model_Abstract $model)
    {
        $value = $model->getData($this->getAttributeCode());

        if ($this->getIsSelect()) {
            $attr = Mage::getModel('catalog/product')->getResource()->getAttribute($this->getAttributeCode());
            $value = $attr->getSource()->getOptionText($value);
        }

        return $value;
    }
}
