<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PublicationResource\Pages;
use App\Models\Publication;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Http;

class PublicationResource extends Resource
{
    protected static ?string $model = Publication::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('doi')
                    ->required()
                    ->label('DOI')
                    ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'After entering a DOI, click the fetch icon to automatically retrieve and fill in publication details.')
                    ->suffixAction(
                        Action::make('fetchData')
                            ->icon('heroicon-m-cloud-arrow-down')
                            ->color('danger')
                            ->label('Fetch')
                            ->tooltip('Fetch publication data from DOI')
                            ->action(function (Forms\Set $set, Forms\Get $get) {
                                $doi = $get('doi');

                                if (!$doi) {
                                    Notification::make()
                                        ->title('DOI Required')
                                        ->body('Please enter a DOI before fetching data.')
                                        ->danger()
                                        ->send();
                                    return;
                                }

                                $response = Http::get("https://api.crossref.org/works/{$doi}");

                                if ($response->successful()) {
                                    $publicationData = $response->json()['message'];

                                    // Parse published date
                                    $dateParts = $publicationData['published-print']['date-parts'][0] ?? null;
                                    if ($dateParts) {
                                        $year = $dateParts[0] ?? 1;
                                        $month = $dateParts[1] ?? 1;
                                        $day = $dateParts[2] ?? 1;
                                        $publishedDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                    } else {
                                        $publishedDate = null;
                                    }
                                    $rawAbstract = $publicationData['abstract'] ?? null;
                                    $cleanAbstract = $rawAbstract ? preg_replace('/<\/?jats:p>/', '', $rawAbstract) : null;

                                    $set('title', $publicationData['title'][0] ?? null);
                                    $set('abstract', $cleanAbstract);
                                    $set('journal', $publicationData['container-title'][0] ?? null);
                                    $set('volume', $publicationData['volume'] ?? null);
                                    $set('issue', $publicationData['issue'] ?? null);
                                    $set('pages', $publicationData['page'] ?? null);
                                    $set('published_date', $publishedDate);
                                    $set('url', $publicationData['URL'] ?? null);
                                    $set('pdf_link', null); // Crossref may not provide a direct PDF link

                                    Notification::make()
                                        ->title('Data Fetched')
                                        ->body('Publication data has been fetched and pre-filled. Please verify before saving.')
                                        ->success()
                                        ->send();
                                } else {
                                    Notification::make()
                                        ->title('Fetch Failed')
                                        ->body('Unable to fetch publication data. Please check the DOI and try again.')
                                        ->danger()
                                        ->send();
                                }
                            })
                    ),
                TextInput::make('title')
                    ->required()
                    ->label('Title'),
                Textarea::make('abstract')
                    ->label('Abstract'),
                TextInput::make('journal')
                    ->label('Journal'),
                TextInput::make('volume')
                    ->label('Volume'),
                TextInput::make('issue')
                    ->label('Issue'),
                TextInput::make('pages')
                    ->label('Pages'),
                DatePicker::make('published_date')
                    ->label('Published Date'),
                TextInput::make('url')
                    ->label('URL'),
                TextInput::make('pdf_link')
                    ->label('PDF Link'),
                Select::make('users')
                    ->label('Authors')
                    ->multiple()
                    ->relationship('users', 'name')
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->name} [ID: {$record->university_id}]")
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('users.name')
                    ->label('Authors')
                    ->listWithLineBreaks()
                    ->limitList(1)
                    ->expandableLimitedList(),
                TextColumn::make('doi')
                    ->label('DOI')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('DOI copied')
                    ->copyMessageDuration(1500),
                TextColumn::make('journal')
                    ->label('Journal')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('published_date')
                    ->label('Published Date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListPublications::route('/'),
            'create' => Pages\CreatePublication::route('/create'),
            'edit' => Pages\EditPublication::route('/{record}/edit'),
        ];
    }
}
