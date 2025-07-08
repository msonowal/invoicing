<?php

namespace App\Actions\Jetstream;

use App\Models\Organization;
use Laravel\Jetstream\Contracts\DeletesTeams;

class DeleteTeam implements DeletesTeams
{
    /**
     * Delete the given team.
     */
    public function delete(Organization $team): void
    {
        $team->purge();
    }
}
