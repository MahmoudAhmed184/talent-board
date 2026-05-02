<?php

namespace App\Enums;

enum UserRole: string
{
    case Candidate = 'candidate';
    case Employer = 'employer';
    case Admin = 'admin';

    public function canSelfRegister(): bool
    {
        return $this !== self::Admin;
    }

    /**
     * @return array<int, string>
     */
    public function abilities(): array
    {
        return match ($this) {
            self::Candidate => [
                'jobs:view',
                'applications:create',
                'candidate:manage-profile',
            ],
            self::Employer => [
                'jobs:view',
                'employer:manage-jobs',
                'employer:review-applications',
            ],
            self::Admin => [
                'admin:moderate-jobs',
                'admin:view-activity',
            ],
        };
    }

    /**
     * @return array<int, string>
     */
    public static function selfRegisterableValues(): array
    {
        return array_values(array_map(
            fn (self $role): string => $role->value,
            array_filter(self::cases(), fn (self $role): bool => $role->canSelfRegister()),
        ));
    }
}
