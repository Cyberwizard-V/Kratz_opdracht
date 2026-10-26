<?php

declare(strict_types=1);

namespace Kratz\Task;

/**
 * Bouwt van een platte lijst taken een geneste tree.
 *
 * - Deprecated taken tellen niet mee, hun subtaken ook niet.
 * - Een taak waarvan de ouder niet bestaat (wees) wordt een hoofdtaak.
 * - Taken in een cyclus zijn onbereikbaar en worden overgeslagen;
 *   $visited voorkomt oneindige recursie.
 */
final class TaskTreeBuilder
{
    /**
     * @param Task[] $tasks
     * @return Task[] de hoofdtaken, met hun subtaken genest
     */
    public function build(array $tasks): array
    {
        $tasksById = [];
        $childrenByParentId = [];

        foreach ($tasks as $task) {
            $tasksById[$task->id] = $task;

            if ($task->parentTaskId !== null) {
                $childrenByParentId[$task->parentTaskId][] = $task;
            }
        }

        $visited = [];
        $roots = [];
        foreach ($tasks as $task) {
            $isRoot = $task->parentTaskId === null || !isset($tasksById[$task->parentTaskId]);

            if ($isRoot && !$task->deprecated) {
                $roots[] = $this->buildNode($task, $childrenByParentId, $visited);
            }
        }

        return $roots;
    }

    /**
     * Geeft $task terug met al zijn subtaken, moet recursief zijn.
     *
     * @param array<int, Task[]> $childrenByParentId
     * @param array<int, bool>   $visited
     */
    private function buildNode(Task $task, array $childrenByParentId, array &$visited): Task
    {
        $visited[$task->id] = true;

        $subtasks = [];
        foreach ($childrenByParentId[$task->id] ?? [] as $child) {
            if ($child->deprecated || isset($visited[$child->id])) {
                continue;
            }

            $subtasks[] = $this->buildNode($child, $childrenByParentId, $visited);
        }

        return $task->withSubtasks($subtasks);
    }
}