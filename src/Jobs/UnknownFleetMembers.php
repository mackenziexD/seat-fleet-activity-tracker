<?php

namespace Helious\SeatFAT\Jobs;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Character\CharacterInfo;

/**
 * Class UnknownFleetMembers.
 *
 * @package Helious\SeatFAT\Jobs
 */
class UnknownFleetMembers extends EsiBase
{
    /**
     * The maximum number of entity IDs we can request in one batch.
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
        \Log::error("[UnknownFleetMembers] Processing unknown characters: " . implode(', ', $this->character_ids));

        // Get character IDs already in CharacterInfo
        $existing_character_ids = CharacterInfo::select('character_id')
            ->whereIn('character_id', $this->character_ids)
            ->pluck('character_id')
            ->toArray();

        // Filter out known characters to avoid duplicates
        $unknown_character_ids = collect($this->character_ids)
            ->diff($existing_character_ids)
            ->values();

        // Batch process remaining unknown characters
        $unknown_character_ids->chunk($this->items_id_limit)->each(function ($chunk) {
            $this->request_body = $chunk->unique()->values()->all();

            $response = $this->retrieve();

            $characters = $response->getBody();

            collect($characters)->each(function ($character) {
                CharacterInfo::updateOrCreate(
                    ['character_id' => $character->id],
                    ['name' => $character->name, 'corporation_id' => $character->corporation_id]
                );
            });
        });
    }
}
