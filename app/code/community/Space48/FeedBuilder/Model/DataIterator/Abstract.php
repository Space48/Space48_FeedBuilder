<?php

abstract class Space48_FeedBuilder_Model_DataIterator_Abstract
{
    /** @var Space48_FeedBuilder_Model_Data_Abstract */
    protected $_dataModel;
    /** @var  Varien_Data_Collection */
    protected $_collection;
    protected $_currentItemPosition;
    protected $_itemReferences = array();

    public function __construct(Space48_FeedBuilder_Model_Data_Abstract $dataModel)
    {
        $this->_dataModel = $dataModel;
        $this->_initNextIterationOfCollection();
    }

    protected function _setItemReferenceArray()
    {
        $this->_itemReferences = array();
        if($this->_collection || !empty($this->_collection)) {
            foreach ($this->_collection as &$item) {
                $this->_itemReferences[] = $item;
            }
        }
    }

    /**
     *
     */
    protected function _initNextIterationOfCollection()
    {
        $this->_currentItemPosition = 0;
        $this->_collection = $this->_dataModel->getIterationOfCollection();
        $this->_setItemReferenceArray();

        if (!$this->_collection || !$this->_collection->count()
            && !$this->_dataModel->isCollectionProcessingComplete() ) {
            $this->_initNextIterationOfCollection();
        }
    }

    /**
     * @return Varien_Object
     */
    abstract public function getCollectionItem();
}