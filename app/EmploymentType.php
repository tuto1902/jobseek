<?php

namespace App;

enum EmploymentType: string
{
    case FullTime = 'full-time';
    case PartTime = 'part-time';
    case Contract = 'contract';
    case Freelance = 'freelance';
    case Internship = 'internship';
    case Temporary = 'temporary';

    public function getLabel(): string
    {
        return match ($this) {
            self::FullTime => 'Full-time',
            self::PartTime => 'Part-time',
            self::Contract => 'Contract',
            self::Freelance => 'Freelance',
            self::Internship => 'Internship',
            self::Temporary => 'Temporary',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::FullTime => 'success',
            self::PartTime => 'warning',
            self::Contract => 'info',
            self::Freelance => 'purple',
            self::Internship => 'gray',
            self::Temporary => 'orange',
        };
    }
}
