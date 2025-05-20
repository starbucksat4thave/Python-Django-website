<?php

namespace App\Filament\Resources\PermissionResource\Pages;

use App\Filament\Resources\PermissionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ListPermissions extends ListRecords
{
    protected static string $resource = PermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('All Permissions')
                ->modifyQueryUsing(fn (Builder $query) => $query),
        ];

        $categories = DB::table('permissions')->select('category')->distinct()->get();

        foreach ($categories as $category) {
            $slug = Str::slug($category->category);
            $tabs[$slug] = Tab::make(ucwords(str_replace('-', ' ', $slug)))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('category', $category->category));
        }

        return $tabs;
    }

}
