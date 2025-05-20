<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NoticeResource\Pages;
use App\Models\Notice;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class NoticeResource extends Resource
{
    protected static ?string $model = Notice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required(),
                Forms\Components\Textarea::make('content')
                    ->columnSpanFull(),
                Forms\Components\Select::make('department_id')
                    ->relationship('department', 'name')
                    ->default(null),
                Forms\Components\DatePicker::make('archived_on')
                    ->label('Archived Date')
                    ->hintIconTooltip('The deadline of this notice.'),
                Forms\Components\FileUpload::make('file')
                    ->directory('notices'),
                Select::make('approvedBy')
                    ->label('Approved By')
                    ->relationship('approvedBy', 'name')
                    ->options(
                        User::whereHas('roles', function (Builder $query) {
                            $query->where('name', 'teacher');
                        })->get()->pluck('name', 'id')
                    )
                    ->multiple()
                    ->searchable()
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('publisher.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('department.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('archived_on')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('file')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('department')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Filter by Department'),

                SelectFilter::make('publisher')
                    ->relationship('publisher', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Filter by Publisher'),
                DateRangeFilter::make('published_on')
                    ->label('Published Date Range')
                    ->placeholder('Select a date range'),

                TernaryFilter::make('approved')
                    ->label('Approval Status')
                    ->placeholder('All')
                    ->trueLabel('Approved')
                    ->falseLabel('Not Approved')
                    ->queries(
                        true: fn (Builder $query) => $query->whereHas('approvedBy'),
                        false: fn (Builder $query) => $query->whereDoesntHave('approvedBy'),
                    ),

                TernaryFilter::make('archived')
                    ->label('Archived Status')
                    ->placeholder('All')
                    ->trueLabel('Archived')
                    ->falseLabel('Active')
                    ->attribute('archived_on')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('archived_on'),
                        false: fn (Builder $query) => $query->whereNull('archived_on'),
                    ),

                TernaryFilter::make('has_file')
                    ->label('File Attached')
                    ->placeholder('All')
                    ->trueLabel('Yes')
                    ->falseLabel('No')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('file'),
                        false: fn (Builder $query) => $query->whereNull('file'),
                    ),

                Filter::make('archived_date')
                    ->form([Forms\Components\DatePicker::make('archived_on')])
                    ->query(fn (Builder $query, array $data): Builder =>
                    $query->when(
                        $data['archived_on'],
                        fn ($query) => $query->whereDate('archived_on', $data['archived_on'])
                    )
                    )
                    ->label('Archived On Date'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListNotices::route('/'),
            'create' => Pages\CreateNotice::route('/create'),
            'view' => Pages\ViewNotice::route('/{record}'),
            'edit' => Pages\EditNotice::route('/{record}/edit'),
        ];
    }
}
