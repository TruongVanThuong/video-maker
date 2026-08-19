<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Project;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VideoReady
{
    use Dispatchable, SerializesModels;

    public Project $project;

    /**
     * Create a new event instance.
     *
     * @param Project $project
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }
}
