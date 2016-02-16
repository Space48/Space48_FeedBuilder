<?php

class Space48_FeedBuilder_Model_Data_Attribute_GoogleProductAvailability extends Space48_FeedBuilder_Model_Data_Attribute_ProductIsInStock
{
    const GOOGLE_IN_STOCK = 'in stock';
    const GOOGLE_OUT_OF_STOCK = 'out of stock';
    const GOOGLE_PRE_ORDER_STOCK = 'preorder';

    protected function _allowsBackOrders()
    {
        return Mage::getStoreConfig('cataloginventory/item/options/backorders')
        != Mage_CatalogInventory_Model_Stock::BACKORDERS_NO;
    }

    public function getValue(Mage_Core_Model_Abstract $model)
    {
        $isInStock = $model->getS48IsInStock();
        if ($isInStock) {
            $value = self::GOOGLE_IN_STOCK;
        } elseif ($this->_allowsBackOrders()) {
            return self::GOOGLE_PRE_ORDER_STOCK;
        } else {
            $value = self::GOOGLE_OUT_OF_STOCK;
        }

        return $value;
    }
}