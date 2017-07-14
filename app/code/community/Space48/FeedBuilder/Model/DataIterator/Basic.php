<?php

class Space48_FeedBuilder_Model_DataIterator_Basic
    extends Space48_FeedBuilder_Model_DataIterator_Abstract
{
    public function getCollectionItem()
    {
        if ($this->_currentItemPosition >= $this->_collection->count()) {
            $this->_initNextIterationOfCollection();
        }

        $this->_currentItemPosition++;
        // The reason for the inner check is to avoid the following error when executing the script
        // Notice: Undefined offset: 0 in Space48/FeedBuilder/Model/DataIterator/Basic.php on line 14
        return $this->_collection ? (array_key_exists($this->_currentItemPosition - 1, $this->_itemReferences) ?
            $this->_itemReferences[ $this->_currentItemPosition - 1] :
            false) : false;
    }
}
