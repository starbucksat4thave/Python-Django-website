<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers\PublicationsRelationManager;
use App\Models\Department;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Webbingbrasil\FilamentAdvancedFilter\Filters\NumberFilter;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getSchema(): array
    {
        return [
            TextInput::make('name')
                ->required()
                ->maxLength(255),
            FileUpload::make('image')
                ->image()
                ->disk('public')
                ->directory('user-images')
                ->visibility('public')
                ->required(),
            TextInput::make('university_id')
                ->required()
                ->numeric(),
            //make a select using the department relation names
            Forms\Components\Select::make('department_id')
                ->label('Department')
                ->options(
                    Department::all()->pluck('name', 'id')
                )
                ->required(),
            //Make in descending order
            Select::make('session')
                ->options(function () {
                    $currentYear = Carbon::now()->year;
                    $years = range($currentYear + 5, 2000);
                    return array_combine($years, $years);
                })
                ->required()
                ->searchable(),
            TextInput::make('year')
                ->numeric()
                ->default(null),
            TextInput::make('semester')
                ->numeric()
                ->default(null),
            DatePicker::make('dob')
                ->required(),
            TextInput::make('phone')
                ->tel()
                ->required()
                ->maxLength(255),
            Textarea::make('address')
                ->required()
                ->columnSpanFull(),
            TextInput::make('city')
                ->required()
                ->maxLength(255),
            TextInput::make('designation')
                ->required()
                ->maxLength(255),
            TextInput::make('publication_count')
                ->required()
                ->numeric()
                ->default(0),
            TextInput::make('status')
                ->required(),
            TextInput::make('email')
                ->email()
                ->required()
                ->maxLength(255),
            TextInput::make('password')
                ->password()
                ->maxLength(255)
                ->dehydrateStateUsing(fn($state) => Hash::make($state))
                ->dehydrated(fn($state) => filled($state))
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema(static::getSchema());
    }

    public static function table(Table $table): Table
    {

        return $table
            ->columns([
                TextColumn::make('id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable(),
                ImageColumn::make('image'),
                TextColumn::make('university_id')
                    ->sortable(),
                TextColumn::make('department.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('session'),
                TextColumn::make('year')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('semester')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('dob')
                    ->date()
                    ->sortable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('city')
                    ->searchable(),
                TextColumn::make('designation')
                    ->searchable(),
                TextColumn::make('publication_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status'),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Academic Filters Group
                SelectFilter::make('department_id')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Department')
                    ->columnSpan(1),
                SelectFilter::make('session')
                    ->options(function () {
                        $currentYear = Carbon::now()->year;
                        $years = range(2000, $currentYear + 1);
                        return array_combine($years, $years);
                    })
                    ->searchable()
                    ->preload()
                    ->label('Session')
                    ->columnSpan(1),
                SelectFilter::make('year')
                    ->options([
                        '1' => 'Year 1',
                        '2' => 'Year 2',
                        '3' => 'Year 3',
                        '4' => 'Year 4',
                    ])
                    ->label('Academic Year')
                    ->columnSpan(1),

                SelectFilter::make('semester')
                    ->options([
                        '1' => 'Semester 1',
                        '2' => 'Semester 2',
                    ])
                    ->label('Semester')
                    ->columnSpan(1),
                DateRangeFilter::make('dob'),
                // Account Status Group
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ])
                    ->label('User Status')
                    ->columnSpan(1),


                DateRangeFilter::make('created_at')
                    ->label('Registration Date')
                    ->placeholder('Select Date Range')
                    ->format('Y-m-d')
                    ->columnSpan(1),
                NumberFilter::make('publication_count'),
                Filter::make('email_verified')
                    ->form([
                        Forms\Components\Toggle::make('email_verified')
                            ->label('Verified Emails Only')
                            ->onColor('success')
                            ->offColor('danger')
                            ->default(true)
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when(
                            isset($data['email_verified']),
                            fn($q) => $data['email_verified']
                                ? $q->whereNotNull('email_verified_at')
                                : $q->whereNull('email_verified_at')
                        );
                    })
                    ->label('Email Verification Status')
                    ->default(false)
                    ->columnSpan(1),
            ])
            ->filtersFormColumns(2)
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
            PublicationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
