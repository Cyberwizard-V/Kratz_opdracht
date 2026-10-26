<?php

declare(strict_types=1);

namespace Kratz\Task;

use Kratz\Project\ProjectNotFoundException;
use PDO;

final class TaskRepository implements TaskRepositoryInterface
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return Task[]
     */
    public function findByProject(int $projectId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT t.id, t.parent_task_id, t.name, t.assigned_to, t.status,
                t.estimated_hours, t.logged_hours, t.deprecated_flag
                FROM projects p
                LEFT JOIN tasks t ON t.project_id = p.id
                WHERE p.id = :project_id
                ORDER BY t.id'
        );

        $statement->execute(['project_id' => $projectId]);
        $rows = $statement->fetchAll();


        if (empty($rows)) {
            throw ProjectNotFoundException::withId($projectId);
        }


        $tasks = [];
        foreach ($rows as $row) {
            if ($row['id'] === null) {
                continue;
            }
            $tasks[] = Task::fromRow($row);
        }
        return $tasks;
    }
}