<?php

declare(strict_types=1);

namespace Kratz\Task;

/**
 * Een taak uit de database, eventueel met zijn subtaken.
 * Immutable: na het aanmaken verandert er niets meer.
 */
final class Task
{
    /**
     * @param Task[] $subtasks
     */
    public function __construct(
        public readonly int $id,
        public readonly ?int $parentTaskId,
        public readonly ?string $name,
        public readonly ?string $assignedTo,
        public readonly ?string $status,
        public readonly int $estimatedHours,
        public readonly int $loggedHours,
        public readonly bool $deprecated,
        public readonly array $subtasks = [],
    ) {
    }

    /**
     * Maakt een Task van een database-rij.
     * NULL-uren worden hier één keer omgezet naar 0.
     */
    public static function fromRow(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            parentTaskId: $row['parent_task_id'] !== null ? (int) $row['parent_task_id'] : null,
            name: $row['name'],
            assignedTo: $row['assigned_to'],
            status: $row['status'],
            estimatedHours: (int) ($row['estimated_hours'] ?? 0),
            loggedHours: (int) ($row['logged_hours'] ?? 0),
            deprecated: (bool) $row['deprecated_flag'],
        );
    }

    /**
     * Geef een kopie van deze taak terug, met opgegeven subtaken.
     *
     * @param Task[] $subtasks
     */
    public function withSubtasks(array $subtasks): self
    {
        return new self(
            $this->id,
            $this->parentTaskId,
            $this->name,
            $this->assignedTo,
            $this->status,
            $this->estimatedHours,
            $this->loggedHours,
            $this->deprecated,
            $subtasks,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'assigned_to' => $this->assignedTo,
            'status' => $this->status,
            'estimated_hours' => $this->estimatedHours,
            'logged_hours' => $this->loggedHours,
            'subtasks' => array_map(
                fn(Task $subtask): array => $subtask->toArray(),
                $this->subtasks,
            ),
        ];
    }
}