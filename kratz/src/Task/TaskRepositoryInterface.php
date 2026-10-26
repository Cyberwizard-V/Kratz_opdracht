<?php

declare(strict_types=1);

namespace Kratz\Task;

use Kratz\Project\ProjectNotFoundException;

interface TaskRepositoryInterface
{
    /**
     * Alle taken van een project als platte lijst, ook deprecated taken.
     *
     * @return Task[]
     * @throws ProjectNotFoundException als het project niet bestaat
     */
    public function findByProject(int $projectId): array;
}