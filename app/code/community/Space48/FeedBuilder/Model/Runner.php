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
            $feed = Mage::getModel('space48_feedbuilder/feed', $feedConfig);
            $this->_allFeeds[$feedReference] = $feed;
        }
    }

    protected function _getValidFeeds(array $requestedFeeds)
    {
        return array_intersect($requestedFeeds, $this->getAllFeeds());
    }

    protected function _getScheduledFeeds()
    {
        /* @TODO : MAKE THIS ONLY RETURN SCHEDULED FEEDS */
        return $this->getAllFeeds();
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

    public function getAllFeeds()
    {
        if (!$this->_allFeeds) {
            $feedConfigs = Mage::getConfig()->getNode(self::FEEDS_CONFIG_PATH)->asArray();
            $this->_registerFeeds($feedConfigs);
        }

        return $this->_allFeeds;
    }

    public function run($feedReference = self::REFERENCE_SCHEDULED_FEEDS)
    {
        /**
         * @var  $feedReference string
         * @var  $feedModel Space48_FeedBuilder_Model_Feed
         */
        foreach ($this->_getFeedsToRun($feedReference) as $feedReference => $feedModel) {
            $feedModel->createFeed();
        }

    }
}