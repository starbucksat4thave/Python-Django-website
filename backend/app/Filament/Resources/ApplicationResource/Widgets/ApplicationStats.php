<?php

namespace App\Filament\Resources\ApplicationResource\Widgets;

use App\Models\Application;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ApplicationStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Pending (Unassigned)', Application::where('status', 'pending')->whereNull('authorized_by')->count())
                ->description('Pending applications with no teacher assigned')
                ->color('warning'),

            Stat::make('Approved', Application::where('status', 'approved')->count())
                ->color('success'),

            Stat::make('Rejected', Application::where('status', 'rejected')->count())
                ->color('danger'),
        ];
    }
}
