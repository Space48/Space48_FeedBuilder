<?php

require_once('../../../../../../../shell/abstract.php');

class Space48_FeedBuilder_Shell_FeedBuilder extends Mage_Shell_Abstract
{
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f feedBuilder.php -- [options]

  -h / help               :: This help
  --feed=<feed_reference> :: run a single feed by reference name (or feeds comma separated)
  --scheduled-feeds       :: run all scheduled feeds.
  --all-feeds             :: run all feeds.

USAGE;
    }

    public function run()
    {
        echo 'STARTING FEED GENERATION...'.PHP_EOL;

        /** @var $feedRunner Space48_FeedBuilder_Model_Runner */
        $feedRunner = Mage::getModel('space48_feedbuilder/runner');
        $feedReference = null;
        if ($this->getArg('all-feeds')) {
            $feedReference = $feedRunner::REFERENCE_ALL_FEEDS;
        } elseif ($this->getArg('scheduled-feeds')) {
            $feedReference = $feedRunner::REFERENCE_SCHEDULED_FEEDS;
        } elseif (($feed = $this->getArg('feed'))) {
            $feedReference = explode(',', $feed);
        } else {
            Mage::throwException($this->usageHelp());
        }

        $feedRunner->run($feedReference);
        echo 'FINISHED FEED GENERATION.'.PHP_EOL;
    }
}

$feedBuilderShell = new Space48_FeedBuilder_Shell_FeedBuilder();
try {
    $feedBuilderShell->run();
} catch (Exception $e) {
    echo $e->getMessage().PHP_EOL;
}