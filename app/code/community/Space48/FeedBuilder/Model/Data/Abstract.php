<?php

class Space48_FeedBuilder_Model_Data_Abstract
{
    /** @var  Varien_Data_Collection */
    protected $_collection;
    protected $_itemsPerIteration = 50;
    protected $_currentIteration = 0;
    protected $_maxIterations;
    protected $_lastPage;
    protected $_feedAttributes = array();

    public function addFeedAttribute($feedFieldName, Space48_FeedBuilder_Model_Data_Attribute_Abstract $attributeModel) {
        $this->_feedAttributes[$feedFieldName] = $attributeModel;
    }

    protected function _addCollectionJoins()
    {
        /**
         * @var  $feedFieldName string
         * @var  $attributeModel Space48_FeedBuilder_Model_Data_Attribute_Abstract
         */
        foreach ($this->getFeedAttributes() as $feedFieldName => $attributeModel) {
            $this->_collection = $attributeModel->addCollectionJoin($this->_collection);
        }
    }

    protected function _addCollectionAttributes()
    {
        /**
         * @var  $feedFieldName string
         * @var  $attributeModel Space48_FeedBuilder_Model_Data_Attribute_Abstract
         */
        foreach ($this->getFeedAttributes() as $feedFieldName => $attributeModel) {
            $this->_collection = $attributeModel->addCollectionAttribute($this->_collection);
        }
    }

    protected function _addCalculatedFields($item)
    {
        /**
         * @var  $feedFieldName string
         * @var  $attributeModel Space48_FeedBuilder_Model_Data_Attribute_Abstract
         */
        foreach ($this->getFeedAttributes() as $feedFieldName => $attributeModel) {
            $item = $attributeModel->addCalculatedField($item);
        }

        return $item;
    }

    public function getFeedAttributes()
    {
        return $this->_feedAttributes;
    }

    public function getFields()
    {
        return array_keys($this->getFeedAttributes());
    }

    protected function _isCollectionProcessingComplete()
    {
        return $this->_currentIteration > $this->_lastPage ||
            ($this->_maxIterations && $this->_currentIteration > $this->_maxIterations);
    }

    public function setCollection(Varien_Data_Collection $collection)
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
        if (!$this->_collection) {
            Mage::throwException('Collection not set for Feedbuilder');
        } elseif (is_null($this->_lastPage)) {
            $this->_addCollectionJoins();
            $this->_addCollectionAttributes();
        }

        $this->_currentIteration++;

        // Reset collection for next load.
        $this->_collection->clear();
        $this->_collection
            ->setPageSize($this->_itemsPerIteration)
            ->setCurPage($this->_currentIteration);

        $this->_lastPage = $this->_collection->getLastPageNumber();

        if($this->_isCollectionProcessingComplete()) {
            $this->_collection = false;
        }

        return $this->_collection;
    }

}