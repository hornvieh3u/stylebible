<?php

declare(strict_types=1);

namespace RebelCode\Spotlight\Instagram\Actions;

use RebelCode\Iris\Importer;
use RebelCode\Spotlight\Instagram\Feeds\FeedManager;

class UpdateFeedsAction
{
    /** @var Importer */
    protected $importer;

    /** @var FeedManager */
    protected $feedManager;

    /** Constructor */
    public function __construct(Importer $importer, FeedManager $feedManager)
    {
        $this->importer = $importer;
        $this->feedManager = $feedManager;
    }

    public function __invoke()
    {
        $feeds = $this->feedManager->query();
        $sources = [];

        foreach ($feeds as $feed) {
            foreach ($feed->sources as $source) {
                $sources[(string) $source] = $source;
            }
        }

        $this->importer->importForSources(array_values($sources));
    }
}
