<?php

declare(strict_types=1);

namespace App\Models\Assignment;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentUser;
use MinVWS\Codable\EncodingContainer;

/**
 * @extends AssignmentOption<UserAssignment>
 */
class UserOption extends AssignmentOption
{
    public function __construct(UserAssignment $assignment)
    {
        parent::__construct($assignment);
    }

    public function getUser(): EloquentUser
    {
        return $this->getAssignment()->getUser();
    }

    public function getLabel(): string
    {
        return $this->getUser()->name;
    }

    public function encode(EncodingContainer $container): void
    {
        parent::encode($container);

        $container->assignmentType = 'user';
        $container->assignment->assignedUserUuid = $this->isSelected() ? null : $this->getUser()->uuid;
    }

    protected function isSelectedForCase(EloquentCase $case): bool
    {
        return $case->assigned_user_uuid === $this->getUser()->uuid;
    }
}
