<?php

declare(strict_types=1);

namespace Kratz\Tests\Fake;

use Kratz\Project\ProjectNotFoundException;
use Kratz\Task\Task;
use Kratz\Task\TaskRepositoryInterface;

/**
 * Repository zonder database, voor tests.
 */
final class InMemoryTaskRepository implements TaskRepositoryInterface
{
    public int $queryCount = 0;

    /**
     * @param array<int, Task[]> $tasksByProjectId
     */
    public function __construct(private readonly array $tasksByProjectId)
    {
    }

    public function findByProject(int $projectId): array
    {
        $this->queryCount++;

        if (!array_key_exists($projectId, $this->tasksByProjectId)) {
            throw ProjectNotFoundException::withId($projectId);
        }

        return $this->tasksByProjectId[$projectId];
    }
}