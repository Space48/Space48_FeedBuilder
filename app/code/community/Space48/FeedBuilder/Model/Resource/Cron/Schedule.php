<?php

class Space48_FeedBuilder_Model_Resource_Cron_Schedule extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Primery key auto increment flag
     *
     * @var bool
     */
    protected $_isPkAutoIncrement    = false;

    public function _construct()
    {
        $this->_init('space48_feedbuilder/cron_schedule', 'feed_reference');
    }
}
