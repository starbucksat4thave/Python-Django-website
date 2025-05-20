<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Filament\Resources\PublicationResource;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class PublicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'publications';

    public function form(Form $form): Form
    {
        return PublicationResource::form($form);
    }

    public function table(Table $table): Table
    {
        return PublicationResource::table($table);
    }
}
