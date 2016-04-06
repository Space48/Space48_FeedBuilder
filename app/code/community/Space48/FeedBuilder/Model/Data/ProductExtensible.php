<?php

class Space48_FeedBuilder_Model_Data_ProductExtensible
    extends Space48_FeedBuilder_Model_Data_Abstract
{
    protected $_parentSpecificFilters = array(
        'Space48_FeedBuilder_Model_Data_Filter_ProductsSimpleOrParent',
        'Space48_FeedBuilder_Model_Data_Filter_VisibleProducts'
        );

    public function _construct()
    {
        $this->setCollection($this->_getBasicProductCollection());
    }

    protected function _getBasicProductCollection()
    {
        return Mage::getModel('space48_feedbuilder/resource_catalog_product_collection');
    }

    protected function _getChildCollection(array $parentIds)
    {
        $collection = Mage::getModel('catalog/product')
            ->getCollection();
        $collection
            ->getSelect()
            ->joinInner(
                array('link' => 'catalog_product_super_link'),
                $collection->getConnection()->quoteInto("e.entity_id=link.product_id AND link.parent_id IN(?)", $parentIds),
                array('parent_id')
            )
            ->group('entity_id');

        return $collection;

    }

    protected function _removeParentSpecificFilters($feedConfig)
    {
        if (!isset($feedConfig['filters'])) {
            return $feedConfig;
        }

        $filters = array();
        foreach ($feedConfig['filters'] as $filterName => $filterConfig) {
            if (!isset($filterConfig['class'])
                || ! in_array($filterConfig['class'], $this->_parentSpecificFilters)) {
                $filters[$filterName] = $filterConfig;
            }
        }
        $feedConfig['filters'] = $filters;
        return $feedConfig;
    }

    /**
     * @return self
     */
    protected function _getChildDataModel()
    {
        $feedConfig = $this->getData();
        $feedConfig = $this->_removeParentSpecificFilters($feedConfig);
        $dataModel = get_class($this);
        return new $dataModel($feedConfig);
    }

    /**
     * @param Mage_Catalog_Model_Product $parentProduct
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getChildItems(array $parentProductIds)
    {
        if (!$parentProductIds) {
            return array();
        }
        $childDataModel = $this->_getChildDataModel();
        $childDataModel->setCollection($this->_getChildCollection($parentProductIds));
        $childDataModel->setItemsPerIteration(null);
        return $childDataModel->getIterationOfCollection();
    }

    /**
     * @return Varien_Data_Collection
     */
    public function getIterationOfCollection()
    {
        $mergedChildrenAndSimpleWithNoParent = new Varien_Data_Collection();

        /** @var Mage_Catalog_Model_Product $parentOrSimpleWithNoParent */
        $parentsAndSimplesWithNoParent = parent::getIterationOfCollection();
        try {
            $children = $this->getChildItems($this->getParentIdsFromCollection($parentsAndSimplesWithNoParent));
        } catch (Exception $e) {
            echo 'ERROR (getting child products) : '.$e->getMessage().PHP_EOL;
        }


        foreach ($parentsAndSimplesWithNoParent as $parentOrSimpleWithNoParent) {
            if ($this->isNonSimple($parentOrSimpleWithNoParent)) {
                foreach ($this->getMergedChildrenProducts($parentOrSimpleWithNoParent, $children) as $child) {
                    try {
                        $mergedChildrenAndSimpleWithNoParent->addItem($child);
                    } catch (Exception $e) {
                        echo 'ERROR (adding merged product) : '.$e->getMessage().PHP_EOL;
                    }
                }
            } else {
                try {
                    $mergedChildrenAndSimpleWithNoParent->addItem($parentOrSimpleWithNoParent);
                } catch (Exception $e) {
                    echo 'ERROR (adding simple product) : '.$e->getMessage().PHP_EOL;
                }
            }
        }

        return $mergedChildrenAndSimpleWithNoParent;
    }

    /**
     * @param Mage_Catalog_Model_Product $parentOrSimpleWithNoParent
     * @return bool
     */
    private function isNonSimple(Mage_Catalog_Model_Product $parentOrSimpleWithNoParent)
    {
        return $parentOrSimpleWithNoParent->getTypeId() !== Mage_Catalog_Model_Product_Type::TYPE_SIMPLE;
    }

    /**
     * @param Mage_Catalog_Model_Resource_Product_Collection $parentsAndSimplesWithNoParent
     * @return int[]
     */
    private function getParentIdsFromCollection(Mage_Catalog_Model_Resource_Product_Collection $parentsAndSimplesWithNoParent)
    {
        $parentIds = array();
        foreach ($parentsAndSimplesWithNoParent as $product) {
            if ($this->isNonSimple($product)) {
                $parentIds[] = $product->getId();
            }
        }
        return array_unique($parentIds);
    }

    /**
     * @param Mage_Catalog_Model_Product $parent
     * @param Mage_Catalog_Model_Product[] $children
     * @return Mage_Catalog_Model_Product[]
     */
    private function getMergedChildrenProducts(Mage_Catalog_Model_Product $parent, $children)
    {
        $childrenWithMergedData = array();
        if (!$parent || !$children) {
            return $childrenWithMergedData;
        }
        $thisParentsChildren = $children->getItemsByColumnValue('parent_id', $parent->getId());

        /** @var Mage_Catalog_Model_Product $child */
        foreach ($thisParentsChildren as $child) {
            $childData = $child->getData();
            foreach ($childData as $key => $value) {
                if ( null === $childData[$key]){
                    unset($childData[$key]);
;                }
            }
            $child->setData(array_merge($parent->getData(), $childData));
            $childrenWithMergedData[] = $child;
        }
        return $childrenWithMergedData;
    }


}