<?php

class Space48_FeedBuilder_Model_Runner
{
    const REFERENCE_ALL_FEEDS       = 'all_feeds';
    const REFERENCE_SCHEDULED_FEEDS = 'scheduled_feeds';
    const FEEDS_CONFIG_PATH         = 'space48_feedbuilder/feeds';

    protected $_allFeeds = array();

    protected function _isRequestForAllFeeds(array $requestedFeeds)
    {
        return in_array(self::REFERENCE_ALL_FEEDS, $requestedFeeds);
    }

    protected function _isRequestForScheduledFeeds(array $requestedFeeds)
    {
        return in_array(self::REFERENCE_SCHEDULED_FEEDS, $requestedFeeds);
    }

    protected function _registerFeeds(array $feedConfigs)
    {
        foreach ($feedConfigs as $feedReference => $feedConfig) {
            if (isset($feedConfig['inherit'])) {
                $inheritedConfig = $this->_getSpecifiedFeedConfig($feedConfig['inherit'], $feedConfigs);
                $inheritedConfig = array_merge($inheritedConfig, $feedConfig);
                $feed = Mage::getModel('space48_feedbuilder/feed', $inheritedConfig);
            } else {
                $feed = Mage::getModel('space48_feedbuilder/feed', $feedConfig);
            }

            $this->_allFeeds[$feedReference] = $feed;
        }
    }

    protected function _getSpecifiedFeedConfig($feedIdentifier, array $feedConfigs)
    {
        return isset($feedConfigs[$feedIdentifier]) ? $feedConfigs[$feedIdentifier] : array();
    }

    protected function _getValidFeeds(array $requestedFeeds)
    {
        $allFeeds = $this->getAllFeeds();

        foreach ($this->getAllFeeds() as $name => $feedData) {
            if (!in_array($name, $requestedFeeds)) {
                unset($allFeeds[$name]);
            }
        }

        return $allFeeds;
    }

    protected function _getScheduledFeeds()
    {
	    return Mage::getModel('space48_feedbuilder/cron_schedule')
                            ->getScheduledFeeds();
    }

    protected function _getFeedsToRun($feedReference)
    {
        if (!is_array($feedReference)) {
            $feedsRequested = array($feedReference);
        } else {
            $feedsRequested = $feedReference;
        }

        $allFeeds = $this->getAllFeeds();

        if ($this->_isRequestForAllFeeds($feedsRequested)) {
            $feedsToRun = $allFeeds;
        } elseif ($this->_isRequestForScheduledFeeds($feedsRequested)) {
            $feedsToRun = $this->_getScheduledFeeds();
        } else {
            $feedsToRun = $this->_getValidFeeds($feedsRequested);
        }

        return $feedsToRun;
    }

    protected function _isFeedScheduled($feedReferenceType)
    {
        if ($feedReferenceType == self::REFERENCE_SCHEDULED_FEEDS) {
            return Mage::getModel('space48_feedbuilder/cron_schedule');
        }
    }

    protected function _setFeedStartedAtIfScheduled($feedReference, $feedReferenceType)
    {
        if ($cronSchedule = $this->_isFeedScheduled($feedReferenceType)) {
            $cronSchedule->setFeedStartedAt($feedReference);
        }
    }

    protected function _setFeedFinishedAtIfScheduled($feedReference, $feedReferenceType)
    {
        if ($cronSchedule = $this->_isFeedScheduled($feedReferenceType)) {
            $cronSchedule->setFeedFinishedAt($feedReference);
        }
    }

    protected function _setFeedReferenceTypeWhenRunAsCron($feedReferenceType)
    {
        if (is_object($feedReferenceType) && $feedReferenceType instanceof Mage_Cron_Model_Schedule ) {
            return self::REFERENCE_SCHEDULED_FEEDS;
        }

        return $feedReferenceType;
    }

    public function getAllFeeds()
    {
        if (!$this->_allFeeds) {
            $feedConfigs = Mage::getConfig()->getNode(self::FEEDS_CONFIG_PATH)->asArray();
            $this->_registerFeeds($feedConfigs);
        }

        return $this->_allFeeds;
    }

    public function run($feedReferenceType = self::REFERENCE_SCHEDULED_FEEDS)
    {
        $feedReferenceType = $this->_setFeedReferenceTypeWhenRunAsCron($feedReferenceType);

        /**
         * @var  $feedReference string
         * @var  $feedModel Space48_FeedBuilder_Model_Feed
         */
        foreach ($this->_getFeedsToRun($feedReferenceType) as $feedReference => $feedModel) {

            /* @Todo refactor _setFeedStartedAtIfScheduled and _setFeedFinishedAtIfScheduled */

            $this->_setFeedStartedAtIfScheduled($feedReference, $feedReferenceType);
            $feedModel->createFeed();
            $feedModel->sendFeed();
            $this->_setFeedFinishedAtIfScheduled($feedReference, $feedReferenceType);
        }
    }
}
