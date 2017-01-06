<?php

class Space48_FeedBuilder_Model_Data_Abstract
    extends Mage_Core_Model_Abstract
{
    /** @var  Varien_Data_Collection */
    protected $_collection;
    protected $_itemsPerIteration = 50;
    protected $_currentIteration = 0;
    protected $_maxIterations;
    protected $_lastPage;
    protected $_attributes = array();
    protected $_filters = array();

    protected function _applyConfig()
    {
        $this->_addAttributes();
        $this->_addFilters();
        $this->_applyCollectionJoins();
        $this->_applyCollectionAttributes();
        $this->_applyCollectionFilters();
    }

    protected function _getAttributeModel($attribute)
    {
        if (is_string($attribute)) {
            $attributeModel = Mage::getModel('space48_feedbuilder/data_attribute_basic');
            $attributeModel->setDataField($attribute);
        } elseif (is_array($attribute) && isset($attribute['class'])) {
            $attributeModel = $attribute['class'];
            if (!class_exists($attributeModel)) {
                Mage::throwException('Attribute model does not exist :: ' . $attributeModel);
            }

            $args = isset($attribute['args']) ? $attribute['args'] : array();
            $attributeModel = new $attributeModel($args);
        } else {
            Mage::throwException('Unable to handle feed attribute :: ' . print_r($attribute, true));
        }

        return $attributeModel;
    }

    protected function _getFilterModel($filter)
    {
        if (is_array($filter) && isset($filter['class'])) {
            $filterModel = $filter['class'];
            if (!class_exists($filterModel)) {
                Mage::throwException('Attribute model does not exist :: ' . $filterModel);
            }

            $args = isset($filter['args']) ? $filter['args'] : array();
            $filterModel = new $filterModel($args);
        } else {
            Mage::throwException('Unable to handle feed filter :: ' . print_r($filter, true));
        }

        return $filterModel;
    }

    protected function _addAttributes()
    {
        foreach ($this->getFields() as $feedField => $feedAttribute) {
            $this->_attributes[$feedField] = $this->_getAttributeModel($feedAttribute);
        }
    }

    protected function _addFilters()
    {
        foreach ($this->getFilters() as $feedFilterName => $feedFilter) {
            $this->_filters[$feedFilterName] = $this->_getFilterModel($feedFilter);
        }
    }

    protected function _applyCollectionJoins()
    {
        /**
         * @var  $feedFieldName string
         * @var  $attributeModel Space48_FeedBuilder_Model_Data_Attribute_Abstract
         */
        foreach ($this->getFeedAttributes() as $feedFieldName => $attributeModel) {
            $this->_collection = $attributeModel->addCollectionJoin($this->_collection);
        }
    }

    protected function _applyCollectionAttributes()
    {
        /**
         * @var  $feedFieldName string
         * @var  $attributeModel Space48_FeedBuilder_Model_Data_Attribute_Abstract
         */
        foreach ($this->getFeedAttributes() as $feedFieldName => $attributeModel) {
            $this->_collection = $attributeModel->addCollectionAttribute($this->_collection);
        }
    }

    protected function _applyCollectionFilters()
    {
        /**
         * @var  $feedFilterName string
         * @var  $filterModel Space48_FeedBuilder_Model_Data_Filter_Abstract
         */
        foreach ($this->_getFilters() as $feedFilterName => $filterModel) {
            $this->_collection = $filterModel->addFilter($this->_collection);
        }
    }

    public function getFeedAttributes()
    {
        return $this->_attributes;
    }

    protected function _getFilters()
    {
        return $this->_filters;
    }

    public function getFeedFields()
    {
        return array_keys($this->getFeedAttributes());
    }

    public function isCollectionProcessingComplete()
    {
        return $this->_currentIteration >= $this->_lastPage ||
            ($this->_maxIterations && $this->_currentIteration > $this->_maxIterations);
    }

    protected function setCollection(Varien_Data_Collection $collection)
    {
        $this->_collection = $collection;
    }

    public function setMaxIterations($maxIterations)
    {
        $this->_maxIterations = (int) $maxIterations;
    }

    public function setItemsPerIteration($itemsPerIteration)
    {
        $this->_itemsPerIteration = $itemsPerIteration;
    }

    public function setCurrentIteration($currentIteration)
    {
        // Value will be incremented on collection iteration request
        $this->_currentIteration = (int) $currentIteration - 1;
    }

    public function getIterationOfCollection()
    {
        if (is_null($this->_collection)) {
            Mage::throwException('Collection not set for Feedbuilder');
        } elseif($this->_collection === false) {
            return false;
        } elseif (is_null($this->_lastPage)) {
            $this->_applyConfig();
        }

        $this->_currentIteration++;

        // Reset collection for next load.
        $this->_collection->clear();
        $this->_collection
            ->setPageSize($this->_itemsPerIteration)
            ->setCurPage($this->_currentIteration);

        $this->_lastPage = $this->_collection->getLastPageNumber();

        if ($this->isCollectionProcessingComplete()) {
            $this->_collection = false;
        }

        return $this->_collection;
    }

}