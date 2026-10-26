<?php

declare(strict_types=1);

namespace Kratz\Tests;

use InvalidArgumentException;
use Kratz\Project\ProjectNotFoundException;
use Kratz\Project\ProjectTaskService;
use Kratz\Task\Task;
use Kratz\Task\TaskStatisticsCalculator;
use Kratz\Task\TaskTreeBuilder;
use Kratz\Tests\Fake\InMemoryTaskRepository;
use PHPUnit\Framework\TestCase;

/**
 * Dezelfde data als in de database, zodat test en demo dezelfde getallen geven.
 */
final class ProjectTaskServiceTest extends TestCase
{
    private InMemoryTaskRepository $repository;
    private ProjectTaskService $service;

    protected function setUp(): void
    {
        $this->repository = new InMemoryTaskRepository([
            1 => [
                self::task(1, null, 'Design', 'Anna', 'in_progress', 4, 2),
                self::task(2, 1, 'Wireframes', 'Bram', 'done', 8, 10),
                self::task(3, 2, 'Schetsen', 'Bram', 'done', 3, 3),
                self::task(4, 3, 'Iconen', 'Chris', 'pending', 2, 0),
                self::task(5, 1, 'Print-versie', 'Anna', 'pending', 6, 0, deprecated: true),
                self::task(6, null, 'Kick-off', null, 'pending', null, null),
                self::task(7, null, 'Oude CMS', 'Bram', 'done', 20, 20, deprecated: true),
                self::task(8, 7, 'Data-export', 'Bram', 'pending', 5, 0),
            ],
            2 => [],
            3 => [
                self::task(20, null, 'Codeschrijven', 'Viktor', 'in_progress', 4, 1),
                self::task(21, 999, 'Wees', 'Bram', 'pending', 5, 2),
                self::task(22, 23, 'Cyclus A', 'Chris', 'done', 1, 1),
                self::task(23, 22, 'Cyclus B', 'Chris', 'done', 1, 1),
                self::task(24, 24, 'Zelfverwijzing', 'Dirk', 'pending', 2, 0),
            ],
            4 => [
                self::task(30, null, 'Niet geschat', 'Anna', 'done', 0, 5),
            ],
        ]);

        $this->service = new ProjectTaskService(
            $this->repository,
            new TaskTreeBuilder(),
            new TaskStatisticsCalculator(),
        );
    }

    public function testStatisticsCountSubtasksRecursivelyAndSkipDeprecated(): void
    {
        self::assertSame([
            'totalTasks' => 5,
            'tasksByStatus' => ['pending' => 2, 'in_progress' => 1, 'done' => 2],
            'totalEstimated' => 17,
            'totalLogged' => 15,
            'completionPercentage' => 88.24,
        ], $this->service->getTaskStatistics(1));
    }

    public function testHierarchyIsNestedFourLevelsDeep(): void
    {
        $tree = $this->service->getTaskHierarchy(1);

        $iconen = $tree[0]['subtasks'][0]['subtasks'][0]['subtasks'][0];

        self::assertSame('Iconen', $iconen['name']);
        self::assertSame('Chris', $iconen['assigned_to']);
        self::assertSame('pending', $iconen['status']);
        self::assertSame(2, $iconen['estimated_hours']);
        self::assertSame(0, $iconen['logged_hours']);
        self::assertSame([], $iconen['subtasks']);
    }

    public function testDeprecatedTasksAndTheirChildrenAreExcluded(): void
    {
        $tree = $this->service->getTaskHierarchy(1);

        self::assertSame(['Design', 'Kick-off'], array_column($tree, 'name'));
        self::assertSame(['Wireframes'], array_column($tree[0]['subtasks'], 'name'));
    }

    public function testNullHoursBecomeZero(): void
    {
        $kickOff = $this->service->getTaskHierarchy(1)[1];

        self::assertNull($kickOff['assigned_to']);
        self::assertSame(0, $kickOff['estimated_hours']);
        self::assertSame(0, $kickOff['logged_hours']);
    }

    public function testEmptyProjectReturnsZeroes(): void
    {
        self::assertSame([
            'totalTasks' => 0,
            'tasksByStatus' => ['pending' => 0, 'in_progress' => 0, 'done' => 0],
            'totalEstimated' => 0,
            'totalLogged' => 0,
            'completionPercentage' => 0.0,
        ], $this->service->getTaskStatistics(2));

        self::assertSame([], $this->service->getTaskHierarchy(2));
    }

    public function testZeroEstimateDoesNotDivideByZero(): void
    {
        self::assertSame(0.0, $this->service->getTaskStatistics(4)['completionPercentage']);
    }

    public function testOrphanBecomesRootAndCyclesAreSkipped(): void
    {
        $tree = $this->service->getTaskHierarchy(3);
        $stats = $this->service->getTaskStatistics(3);

        self::assertSame(['Codeschrijven', 'Wees'], array_column($tree, 'name'));
        self::assertSame(2, $stats['totalTasks']);
        self::assertSame(33.33, $stats['completionPercentage']);
    }

    public function testUnknownProjectThrows(): void
    {
        $this->expectException(ProjectNotFoundException::class);

        $this->service->getTaskStatistics(99);
    }

    public function testInvalidProjectIdThrowsWithoutQuery(): void
    {
        try {
            $this->service->getTaskHierarchy(0);
            self::fail('Er had een InvalidArgumentException moeten komen.');
        } catch (InvalidArgumentException) {
            self::assertSame(0, $this->repository->queryCount);
        }
    }

    public function testEachCallUsesExactlyOneQuery(): void
    {
        $this->service->getTaskStatistics(1);

        self::assertSame(1, $this->repository->queryCount);
    }

    private static function task(
        int $id,
        ?int $parentId,
        string $name,
        ?string $assignedTo,
        ?string $status,
        ?int $estimated,
        ?int $logged,
        bool $deprecated = false,
    ): Task {
        return Task::fromRow([
            'id' => $id,
            'parent_task_id' => $parentId,
            'name' => $name,
            'assigned_to' => $assignedTo,
            'status' => $status,
            'estimated_hours' => $estimated,
            'logged_hours' => $logged,
            'deprecated_flag' => $deprecated ? 1 : 0,
        ]);
    }
}