<?php

declare(strict_types=1);

namespace Kratz\Project;

use InvalidArgumentException;
use Kratz\Task\Task;
use Kratz\Task\TaskRepositoryInterface;
use Kratz\Task\TaskStatisticsCalculator;
use Kratz\Task\TaskTreeBuilder;

/**
 * De twee functies uit de opdracht. Elke aanroep doet precies één query.
 */
final class ProjectTaskService
{
    public function __construct(
        private readonly TaskRepositoryInterface $repository,
        private readonly TaskTreeBuilder $treeBuilder,
        private readonly TaskStatisticsCalculator $calculator,
    ) {
    }

    /**
     *
     * @throws InvalidArgumentException bij een ongeldig project-id
     * @throws ProjectNotFoundException als het project niet bestaat
     */
    public function getTaskStatistics(int $projectId): array
    {
        return $this->calculator->calculate($this->loadTree($projectId));
    }

    /**
     *
     * @throws InvalidArgumentException bij een ongeldig project-id
     * @throws ProjectNotFoundException als het project niet bestaat
     */
    public function getTaskHierarchy(int $projectId): array
    {
        return array_map(
            fn(Task $task): array => $task->toArray(),
            $this->loadTree($projectId),
        );
    }

    /**
     * @return Task[]
     */
    private function loadTree(int $projectId): array
    {
        if ($projectId <= 0) {
            throw new InvalidArgumentException("Project-id moet positief zijn, $projectId gegeven.");
        }

        return $this->treeBuilder->build($this->repository->findByProject($projectId));
    }
}