<?php

abstract class Space48_FeedBuilder_Test_Util_FeedTestAbstract extends Space48_Test_Integration_TestCase
{
    protected $inTransaction = false;

    protected function getResource()
    {
        /** @var  $resource Mage_Core_Model_Resource */
        $resource = Mage::getSingleton('core/resource');
        return $resource;
    }

    protected function getConnection()
    {
        $resource = $this->getResource();
        return $resource->getConnection('default_write');
    }

    protected function beginTransaction()
    {
        $this->getConnection()->beginTransaction();
        $this->inTransaction = true;
    }

    /** @after */
    protected function rollbackTransaction()
    {
        if ($this->inTransaction) {
            $this->getConnection()->rollBack();
        }
        $this->inTransaction = false;
    }

    protected function deleteAllProducts()
    {
        if (! $this->inTransaction) {
            throw new LogicException('You have to call beginTransaction() before deleteAllProducts()');
        }
        $this->getConnection()->delete($this->getResource()->getTableName('catalog/product'));
    }
}
