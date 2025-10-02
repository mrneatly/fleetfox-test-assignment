<?php

namespace App\Enums;

use Illuminate\Support\Arr;

/**
 * Backed enum of allowed icon names for TaskCategory
 */
enum TaskCategoryIcon: string
{
    case House = 'house';
    case User = 'user';
    case Settings = 'settings';
    case Bell = 'bell';
    case Star = 'star';
    case Tag = 'tag';
    case Folder = 'folder';
    case Bookmark = 'bookmark';
    case Heart = 'heart';
    case ChartLine = 'chart-line';

    /**
     * Get all enum values as an array of strings.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return Arr::map(self::cases(), fn (self $c) => $c->value);
    }
}
