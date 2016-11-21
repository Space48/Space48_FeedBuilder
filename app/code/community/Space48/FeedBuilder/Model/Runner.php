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
                $feedConfig = $this->_extendInheritedConfig($feedConfig['inherit'], $feedReference);
            }

            $this->_allFeeds[$feedReference] = Mage::getModel('space48_feedbuilder/feed', $feedConfig);
        }
    }

    /**
     * Allows a feed to inherit and overwrite another by merging their config XML nodes
     *
     * @param string $inheritFeedId
     * @param string $extendFeedId
     * @return array
     */
    protected function _extendInheritedConfig($inheritFeedId, $extendFeedId)
    {
        $inheritedConfig = Mage::getConfig()->getNode(self::FEEDS_CONFIG_PATH . '/' . $inheritFeedId);
        $extendConfig    = Mage::getConfig()->getNode(self::FEEDS_CONFIG_PATH . '/' . $extendFeedId);

        if (!$inheritedConfig) {
            return $extendConfig->asArray();
        }

        return $inheritedConfig->extend($extendConfig, true)->asArray();
    }

    protected function _getValidFeeds(array $requestedFeeds)
    {
        return array_intersect($requestedFeeds, $this->getAllFeeds());
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
