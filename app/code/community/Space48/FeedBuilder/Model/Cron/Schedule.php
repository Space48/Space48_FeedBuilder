<?php

class Space48_FeedBuilder_Model_Cron_Schedule extends Mage_Core_Model_Abstract
{
    protected $_executionTime;

    protected function _construct()
    {
        $this->_init('space48_feedbuilder/cron_schedule');
        $this->_executionTime = $this->_getTimestamp();
    }

    protected function _getTimestamp()
    {
        $locale = Mage::app()->getLocale()->getLocaleCode();
        $executeTime = new Zend_Date(time(), Zend_Date::TIMESTAMP, $locale);
        return $executeTime->get('YYYY-MM-dd HH:mm:ss');
    }

    protected function _hasScheduleCompleted($schedule)
    {
        if (!$schedule->getFinishedAt() || ! $schedule->getScheduledAt()) {
            $scheduleHasCompleted = false;
        } else {
            $scheduleHasCompleted =
                strtotime($schedule->getFinishedAt()) >= strtotime($schedule->getScheduledAt());
        }
        return $scheduleHasCompleted;
    }

    protected function _doesFeedNeedScheduling($feedReference)
    {
        $schedule = Mage::getModel('space48_feedbuilder/cron_schedule')
            ->load($feedReference);
        if (! $schedule->getScheduledAt() ) {
            $needsScheduling = true;
        } elseif ($this->_hasScheduleCompleted($schedule)) {
        } else {
            $needsScheduling = false;
        }

        return $needsScheduling;
    }

    /*
     * Based on code from Mage_Cron_Model_Observer
     */
    public function updateSchedule()
    {
        $scheduleAheadFor = Mage::getStoreConfig(Mage_Cron_Model_Observer::XML_PATH_SCHEDULE_AHEAD_FOR)*60;
        $allFeeds = Mage::getModel('space48_feedbuilder/runner')->getAllFeeds();

        foreach ($allFeeds as $feedReference => $feed) {
            // Feed doesn't have a cron schedule
            if (!($cronExpr = $feed->getSchedule('cron_expr'))) {
                continue;
            }

            // Feed is already scheduled
            if (!$this->_doesFeedNeedScheduling($feedReference)) {
                continue;
            }

            // Set schedule
            $scheduledAt = $this->_getNextScheduledTime($cronExpr, $scheduleAheadFor);
            if ($scheduledAt) {
                Mage::getModel('space48_feedbuilder/cron_schedule')
                    ->load($feedReference)
                    ->setScheduledAt($scheduledAt)
                    ->save();
            }
        }
    }

    /*
     * Based on code from Mage_Cron_Model_Observer
     */
    protected function _getNextScheduledTime($cronExpression, $scheduleAheadFor)
    {
        $schedule = Mage::getModel('cron/schedule');

        $now = time();
        $timeAhead = $now + $scheduleAheadFor;

        $nextScheduledTime = null;
        for ($time = $now; $time < $timeAhead; $time += 60) {
            $ts = strftime('%Y-%m-%d %H:%M:00', $time);
            if (!$schedule->trySchedule($time)) {
                // time does not match cron expression
                continue;
            }
            $nextScheduledTime = $time;
            break;
        }

        return $nextScheduledTime;
    }

    /*
     * Uses code from Magento class Mage_Cron_Model_Schedule
     */
    public function trySchedule($cronExpr, $time)
    {
        $cronExpr = preg_split('#\s+#', $cronExpr, null, PREG_SPLIT_NO_EMPTY);
        if (!$cronExpr || !$time) {
            return false;
        }
        if (!is_numeric($time)) {
            $time = strtotime($time);
        }

        $d = getdate(Mage::getSingleton('core/date')->timestamp($time));

        $match = $this->matchCronExpression($cronExpr[0], $d['minutes'])
            && $this->matchCronExpression($cronExpr[1], $d['hours'])
            && $this->matchCronExpression($cronExpr[2], $d['mday'])
            && $this->matchCronExpression($cronExpr[3], $d['mon'])
            && $this->matchCronExpression($cronExpr[4], $d['wday']);

        return $match ? strftime('%Y-%m-%d %H:%M', $time) : false;
    }

    public function getScheduledFeeds()
    {
        $this->updateSchedule();
        return $this->getCollection()
            ->addFieldToFilter('scheduled_at', array('lte'=> $this->_executionTime ));
    }
}