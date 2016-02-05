<?php

class Space48_FeedBuilder_Model_DataIterator_Basic
    extends Space48_FeedBuilder_Model_DataIterator_Abstract
{
    public function getCollectionItem()
    {
        if ($this->_currentItemPosition >= $this->_collection->count()) {
            $this->_getIterationOfCollection();
        }

        $this->_currentItemPosition++;
        return $this->_collection ?
            $this->_collection[ $this->_currentItemPosition - 1] :
            false;
    }
}