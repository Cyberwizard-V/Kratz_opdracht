<?php

declare(strict_types=1);

namespace Kratz\Project;

use RuntimeException;

final class ProjectNotFoundException extends RuntimeException
{
    public static function withId(int $projectId): self
    {
        return new self("Project met id $projectId bestaat niet.");
    }
}