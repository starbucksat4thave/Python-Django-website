<?php

namespace App\Helpers;

use App\Models\Course;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class SemesterTabHelper
{
    public static function makeTabs(
        string $modelClass,
        string $relationshipPath,
        string $allLabel = 'All Records'
    ): array {
        $tabs = [
            Tab::make($allLabel)
                ->badge($modelClass::count())
                ->badgeColor('gray')
        ];

        // Get ordered combinations first
        $combinations = self::getSemesterCombinations();

        foreach ($combinations as $combo) {
            $tabs[] = self::createTab($modelClass, $relationshipPath, $combo);
        }

        return $tabs;
    }

    private static function getSemesterCombinations()
    {
        return Course::select('year', 'semester')
            ->distinct()
            ->orderBy('year', 'asc')  // Ascending year
            ->orderBy('semester', 'asc')  // Ascending semester
            ->get();
    }

    private static function createTab(string $modelClass, string $relationshipPath, $combo): Tab
    {
        $count = $modelClass::query()
            ->when($relationshipPath !== 'self',
                fn($q) => $q->whereHas(
                    $relationshipPath,
                    fn($q) => $q->where([
                        'year' => $combo->year,
                        'semester' => $combo->semester
                    ])
                ),
                fn($q) => $q->where([
                    'year' => $combo->year,
                    'semester' => $combo->semester
                ])
            )->count();

        return Tab::make("Y{$combo->year}-S{$combo->semester}")
            ->modifyQueryUsing(fn(Builder $query) => $relationshipPath !== 'self'
                ? $query->whereHas(
                    $relationshipPath,
                    fn($q) => $q->where([
                        'year' => $combo->year,
                        'semester' => $combo->semester
                    ])
                )
                : $query->where([
                    'year' => $combo->year,
                    'semester' => $combo->semester
                ])
            )
            ->badge(number_format($count))
            ->badgeColor(self::getBadgeColor($count));
    }

    private static function getBadgeColor(int $count): string
    {
        return match (true) {
            $count === 0 => 'gray',
            $count < 20 => 'danger',  // Changed from 'red'
            $count < 50 => 'warning', // Changed from 'orange'
            default => 'success',     // Changed from 'green'
        };
    }
}
