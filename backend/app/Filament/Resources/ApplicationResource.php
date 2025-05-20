<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicationResource\Pages;
use App\Models\Application;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('authorized_by')
                    ->label('Authorized Teacher')
                    ->options(function () {
                        return User::role('teacher')->pluck('name', 'id');
                    })
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search) =>
                    User::role('teacher')
                        ->where('name', 'like', "%{$search}%")
                        ->pluck('name', 'id')
                    )
                    ->getOptionLabelUsing(fn ($value) =>
                    User::find($value)?->name
                    )
                    ->preload(),


                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->disabled()
                    ->required(),

                Textarea::make('body')
                    ->rows(10)
                    ->disabled()
                    ->label('Application Content'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('user.name')->label('Student'),
                TextColumn::make('applicationTemplate.title')->label('Template'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')->label('Submitted')->since(),
                TextColumn::make('authorizedBy.name')
                    ->label('Authorized Teacher')
                    ->searchable(),

            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),

                SelectFilter::make('authorized_status')
                    ->label('Authorization')
                    ->options([
                        'unassigned' => 'Not Assigned',
                        'assigned' => 'Assigned',
                    ])->default(null)
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        return match ($value) {
                            'unassigned' => $query->whereNull('authorized_by'),
                            'assigned' => $query->whereNotNull('authorized_by'),
                            default => $query,
                        };
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApplications::route('/'),
            'create' => Pages\CreateApplication::route('/create'),
            'edit' => Pages\EditApplication::route('/{record}/edit'),
        ];
    }

}
