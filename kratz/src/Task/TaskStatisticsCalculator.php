<?php

declare(strict_types=1);

namespace Kratz\Task;

/**
 * Telt recursief alle taken in een boom op.
 */
final class TaskStatisticsCalculator
{
    private const STATUSES = ['pending', 'in_progress', 'done'];

    /**
     * @param Task[] $roots
     * @return array{totalTasks: int, tasksByStatus: array<string, int>, totalEstimated: int, totalLogged: int, completionPercentage: float}
     */
    public function calculate(array $roots): array
    {
        $totals = [
            'totalTasks' => 0,
            'tasksByStatus' => array_fill_keys(self::STATUSES, 0),
            'totalEstimated' => 0,
            'totalLogged' => 0,
        ];

        $this->collect($roots, $totals);

        $totals['completionPercentage'] = $this->percentage($totals['totalLogged'], $totals['totalEstimated']);

        return $totals;
    }

    /**
     * @param Task[]               $tasks
     * @param array<string, mixed> $totals
     */
    private function collect(array $tasks, array &$totals): void
    {
        foreach ($tasks as $task) {
            $totals['totalTasks']++;
            $totals['totalEstimated'] += $task->estimatedHours;
            $totals['totalLogged'] += $task->loggedHours;

            if (isset($totals['tasksByStatus'][$task->status])) {
                $totals['tasksByStatus'][$task->status]++;
            }

            $this->collect($task->subtasks, $totals);
        }
    }

    private function percentage(int $logged, int $estimated): float
    {
        if ($estimated === 0) {
            return 0.0;
        }

        return round($logged / $estimated * 100, 2);
    }
}