<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Filament\Resources\RoleResource\RelationManagers\UsersRelationManager;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $schema = [];
        $categories = Permission::select('category')
            ->distinct()
            ->pluck('category');
            foreach ($categories as $category) {
                $permissions = Permission::where('category', $category)
                    ->pluck('name', 'id')
                    ->toArray();
                $schema[] = Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\CheckboxList::make('permissions')
                            ->label($category)
                            ->relationship('permissions', 'name') // Use relationship for many-to-many
                            ->options($permissions)
                            ->columns(3) // Optional: Show in 2 columns
                            ->required()
                            ->bulkToggleable(),
                    ]);
            }
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->unique('roles', 'name', ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\Select::make('guard_name')
                    ->label('Guard Name')
                    ->options([
                        'web' => 'Web',
                        'api' => 'API',
                    ])
                    ->default('web'),
            Forms\Components\Section::make('Permissions')
                ->schema($schema),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('guard_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('permissions.name')
                    ->formatStateUsing(function ( $record) {
                        $permissions = $record->permissions->pluck('name')->toArray();

                        if (count($permissions) > 2) {
                            return implode(', ', array_slice($permissions, 0, 2)) . ' and ' . (count($permissions) - 2) . ' more';
                        }

                        return implode(', ', $permissions);
                    })
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            UsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
            'view' => Pages\ViewRole::route('/{record}'),
        ];
    }
}
