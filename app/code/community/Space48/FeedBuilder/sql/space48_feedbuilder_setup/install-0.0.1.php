<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Create table 'space48_feedbuilder/cron_schedule'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('space48_feedbuilder/cron_schedule'))
    ->addColumn('feed_reference', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false,
        'primary'   => true,
    ), 'Feed Reference')
    ->addColumn('scheduled_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => true,
    ), 'Scheduled At')
    ->addColumn('started_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => true,
    ), 'Started At')
    ->addColumn('finished_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => true,
    ), 'Finished At')
    ->addIndex($installer->getIdxName('space48_feedbuilder/cron_schedule', array('scheduled_at')),
        array('scheduled_at'))
    ->setComment('Space 48 FeedBuilder Cron Schedule');
$installer->getConnection()->createTable($table);

$installer->endSetup();
