<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseMaterialResource\Pages;
use App\Models\CourseResource;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CourseMaterialResource extends Resource
{
    protected static ?string $model = CourseResource::class;
    protected static ?string $label = 'Course Resources';
    protected static ?string $navigationGroup = 'Course Management';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            //
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id')
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('title')
                ->searchable(),

            Tables\Columns\TextColumn::make('courseSession.course.name')
                ->label('Course')
                ->searchable(),

            Tables\Columns\TextColumn::make('uploadedBy.name')
                ->label('Uploaded By')
                ->searchable(),

            Tables\Columns\TextColumn::make('file_name')
                ->label('File Name')
                ->searchable(),

            Tables\Columns\TextColumn::make('file_type')
                ->label('File Type')
                ->searchable(),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Uploaded At')
                ->dateTime()
                ->sortable(),
        ])
            ->filters([
                SelectFilter::make('course')
                    ->relationship('courseSession.course', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Course'),

                SelectFilter::make('uploadedBy')
                    ->relationship('uploadedBy', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Uploaded By'),

                SelectFilter::make('file_type')
                    ->options(
                        CourseResource::query()
                            ->distinct()
                            ->pluck('file_type', 'file_type')
                            ->toArray()
                    )
                    ->multiple()
                    ->searchable()
                    ->label('File Type'),

                Filter::make('created_at')
                    ->label('Upload Date')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('From Date'),
                        DatePicker::make('created_until')
                            ->label('To Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourseMaterials::route('/'),
        ];
    }
}
