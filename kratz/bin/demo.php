<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Kratz\Database\ConnectionFactory;
use Kratz\Project\ProjectNotFoundException;
use Kratz\Project\ProjectTaskService;
use Kratz\Task\TaskRepository;
use Kratz\Task\TaskStatisticsCalculator;
use Kratz\Task\TaskTreeBuilder;

$service = new ProjectTaskService(
    new TaskRepository(ConnectionFactory::create()),
    new TaskTreeBuilder(),
    new TaskStatisticsCalculator(),
);

$projectId = (int) ($argv[1] ?? 0);

try {
    echo '--- Statistieken ---' . PHP_EOL;
    echo json_encode($service->getTaskStatistics($projectId), JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION) . PHP_EOL;

    echo '--- Hiërarchie ---' . PHP_EOL;
    echo json_encode($service->getTaskHierarchy($projectId), JSON_PRETTY_PRINT) . PHP_EOL;
} catch (ProjectNotFoundException | InvalidArgumentException $e) {
    echo 'Fout: ' . $e->getMessage() . PHP_EOL;
}