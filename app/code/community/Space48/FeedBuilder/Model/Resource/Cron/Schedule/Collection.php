<?php

class Space48_FeedBuilder_Model_Resource_Cron_Schedule_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('space48_feedbuilder/cron_schedule');
    }
}
