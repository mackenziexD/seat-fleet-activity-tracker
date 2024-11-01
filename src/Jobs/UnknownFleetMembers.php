<?php

namespace Helious\SeatFAT\Jobs;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Universe\UniverseName;
use Helious\SeatFAT\Models\FATS;

/**
 * Class UnknownFleetMembers.
 *
 * @package Helious\SeatFAT\Jobs
 */
class UnknownFleetMembers extends EsiBase
{
    /**
     * The maximum number of entity ids we can request resolution for.
     */
    protected $items_id_limit = 1000;

    /**
     * @var string
     */
    protected $method = 'post';

    /**
     * @var string
     */
    protected $endpoint = '/universe/names/';

    /**
     * @var string
     */
    protected $version = 'v3';

    /**
     * @var array
     */
    protected $tags = ['fleets', 'universe'];

    /**
     * @var array
     */
    protected $character_ids;

    /**
     * UnknownFleetMembers constructor.
     *
     * @param array $character_ids
     */
    public function __construct(array $character_ids)
    {
        parent::__construct();
        $this->character_ids = $character_ids;
    }

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {
        \Log::error("[FATs][Unknown Character] Getting character ID: " . $this->character_ids);
        // Get existing entity IDs once
        $existing_entity_ids = UniverseName::select('entity_id')
            ->distinct()
            ->pluck('entity_id');

        // Prepare the entity IDs from the passed character IDs
        $entity_ids = collect($this->character_ids)
            ->diff($existing_entity_ids)
            ->values();

        // Chunk the entity IDs for processing
        $entity_ids->chunk($this->items_id_limit)->each(function ($chunk) {
            $this->request_body = $chunk->unique()->values()->all();

            // Make the request to ESI
            $response = $this->retrieve();

            $resolutions = $response->getBody();

            // Save or update the names in the database
            collect($resolutions)->each(function ($resolution) {
                UniverseName::firstOrNew(['entity_id' => $resolution->id])
                    ->fill([
                        'name' => $resolution->name,
                        'category' => $resolution->category,
                    ])->save();
            });
        });
    }
}
