<?php

namespace App\Filament\Widgets;

use Filament\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class ExtensionsListWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(trans('admin/extensions.marketplace_heading'))
            ->records(fn (int|string $page, ?string $sortColumn, ?string $sortDirection): LengthAwarePaginator => $this->getExtensionsPaginator(
                page: max(1, (int) $page),
                perPage: 6,
                sortColumn: $sortColumn,
                sortDirection: $sortDirection,
            ))
            ->columns([
                ImageColumn::make('image')
                    ->label(trans('admin/extensions.columns.icon'))
                    ->imageSize(40),

                TextColumn::make('title')
                    ->label(trans('admin/extensions.columns.name'))
                    ->description(fn (array $record): string => html_entity_decode((string) ($record['description'] ?? '')))
                    ->url(fn (array $record): string => $this->getResourceUrl($record), true)
                    ->sortable(),

                TextColumn::make('author_name')
                    ->label(trans('admin/extensions.columns.author'))
                    ->sortable(),

                TextColumn::make('version')
                    ->label(trans('admin/extensions.columns.version')),

                TextColumn::make('updated_date')
                    ->label(trans('admin/extensions.columns.last_updated'))
                    ->dateTime(),

                TextColumn::make('downloads')
                    ->label(trans('admin/extensions.columns.downloads'))
                    ->numeric(),
            ])
            ->actions([
                Action::make('view')
                    ->label(trans('admin/extensions.actions.view'))
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (array $record): string => $this->getResourceUrl($record), true),
            ])
            ->defaultPaginationPageOption(6)
            ->paginationPageOptions([6])
            ->paginationMode(PaginationMode::Default);
    }

    /**
     * @return LengthAwarePaginator<array{id: int, title: string, image: string, description: string, link: string, author_name: string, version: string, updated_date: string, downloads: int}>
     */
    private function getExtensionsPaginator(int $page, int $perPage, ?string $sortColumn, ?string $sortDirection): LengthAwarePaginator
    {
        $extensions = $this->getExtensions();

        if (in_array($sortColumn, ['title', 'author_name'], true)) {
            $extensions = $extensions
                ->sortBy($sortColumn, SORT_NATURAL | SORT_FLAG_CASE, $sortDirection === 'desc')
                ->values();
        }

        return new LengthAwarePaginator(
            items: $extensions->forPage($page, $perPage)->values(),
            total: $extensions->count(),
            perPage: $perPage,
            currentPage: $page,
        );
    }

    /**
     * @return Collection<int, array{id: int, title: string, image: string, description: string, link: string, author_name: string, version: string, updated_date: string, downloads: int}>
     */
    private function getExtensions(): Collection
    {
        try {
            $resources = Cache::remember('extensions_list', now()->addHour(), function (): array {
                $response = Http::acceptJson()
                    ->timeout(10)
                    ->get('https://rextstore.app/api/v2/resources/1');

                if (! $response->successful()) {
                    return [];
                }

                return Arr::wrap($response->json('resources'));
            });
        } catch (Throwable) {
            return collect();
        }

        return collect($resources)
            ->filter(fn (mixed $resource): bool => is_array($resource))
            ->map(fn (array $resource): array => [
                'id' => (int) ($resource['id'] ?? 0),
                'title' => (string) ($resource['title'] ?? ''),
                'image' => (string) ($resource['image'] ?? ''),
                'description' => (string) ($resource['description'] ?? ''),
                'link' => (string) ($resource['link'] ?? ''),
                'author_name' => (string) data_get($resource, 'author.name', ''),
                'version' => (string) ($resource['version'] ?? ''),
                'updated_date' => (string) ($resource['updated_date'] ?? ''),
                'downloads' => (int) ($resource['downloads'] ?? 0),
            ])
            ->values();
    }

    /**
     * @param  array<string, mixed>  $resource
     */
    private function getResourceUrl(array $resource): string
    {
        $link = (string) ($resource['link'] ?? '');

        if (str_starts_with($link, 'https://')) {
            return $link;
        }

        return 'https://rextstore.app/'.ltrim($link, '/');
    }
}
