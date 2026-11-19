<?php

namespace App\Filament\Resources\Nests\Eggs\Pages;

use App\Filament\Resources\Nests\EggResource;
use App\Filament\Resources\Nests\NestResource;
use App\Models\Egg;
use App\Models\Nest;
use App\Services\Eggs\Catalog\CatalogAccess;
use App\Services\Eggs\Catalog\CatalogException;
use App\Services\Eggs\Catalog\CatalogImportService;
use App\Services\Eggs\Catalog\EggCatalogEntry;
use App\Services\Eggs\Catalog\EggCatalogService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EggCatalog extends Page
{
    protected static string $resource = EggResource::class;

    protected string $view = 'filament.pages.egg-catalog';

    #[Url]
    public string $search = '';

    #[Url]
    public string $source = '';

    #[Url]
    public string $category = '';

    #[Url]
    public int $page = 1;

    #[Url(as: 'per_page')]
    public int $perPage = 24;

    #[Url(as: 'nest_id')]
    public ?int $nestContext = null;

    #[Locked]
    public bool $loaded = false;

    #[Locked]
    public array $providerNotices = [];

    #[Locked]
    public ?string $reviewToken = null;

    #[Locked]
    public ?string $selectedProvider = null;

    #[Locked]
    public ?string $selectedId = null;

    #[Locked]
    public ?string $reviewError = null;

    #[Locked]
    public ?string $resumeProvider = null;

    #[Locked]
    public ?string $resumeId = null;

    public function mount(): void
    {
        app(CatalogAccess::class)->authorize();
        $provider = request()->query('review_provider');
        $id = request()->query('review_id');
        $this->resumeProvider = is_string($provider) ? $provider : null;
        $this->resumeId = is_string($id) ? $id : null;
        if ($this->nestContext !== null) {
            $nest = Nest::query()->find($this->nestContext);
            abort_unless($nest && NestResource::canViewAny() && NestResource::canView($nest), 403);
        }
    }

    public function getTitle(): string
    {
        return trans('admin/catalog.title');
    }

    public function getSubheading(): string
    {
        return trans('admin/catalog.description');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')->label(trans('admin/catalog.back'))->color('gray')
                ->icon('heroicon-o-arrow-left')->url(fn (): string => $this->backUrl()),
            Action::make('refresh')->label(trans('admin/catalog.refresh'))->color('gray')
                ->icon('heroicon-o-arrow-path')->action(fn () => $this->refreshCatalog()),
        ];
    }

    public function refreshCatalog(): void
    {
        app(CatalogAccess::class)->authorize();
        $successful = true;
        foreach (EggCatalogService::PROVIDERS as $provider) {
            $status = app(EggCatalogService::class)->provider($provider, true, manualRefresh: true);
            $this->providerNotices[$provider] = $status['error'];
            $successful = $successful && $status['error'] === null;
        }
        $this->loaded = true;
        if ($successful) {
            Notification::make()->title(trans('admin/catalog.refresh_success'))->success()->send();
        }
    }

    public function loadCatalog(): void
    {
        app(CatalogAccess::class)->authorize();
        foreach (EggCatalogService::PROVIDERS as $provider) {
            $status = app(EggCatalogService::class)->provider($provider);
            $this->providerNotices[$provider] = in_array($status['error'], ['cooldown', 'refreshing'], true) ? $status['error'] : null;
        }
        $this->loaded = true;
        if ($this->resumeProvider && $this->resumeId) {
            $this->mountAction('review', ['provider' => $this->resumeProvider, 'id' => $this->resumeId]);
            $this->resumeProvider = null;
            $this->resumeId = null;
        }
    }

    public function updated(string $property): void
    {
        app(CatalogAccess::class)->authorize();
        if (in_array($property, ['search', 'source', 'category', 'perPage'], true)) {
            $this->page = 1;
            if ($property === 'source') {
                $this->category = '';
            }
        }
    }

    public function gotoPage(int $page, string $pageName = 'page'): void
    {
        app(CatalogAccess::class)->authorize();
        $this->page = max(1, $page);
    }

    public function nextPage(string $pageName = 'page'): void
    {
        $this->gotoPage($this->page + 1, $pageName);
    }

    public function previousPage(string $pageName = 'page'): void
    {
        $this->gotoPage($this->page - 1, $pageName);
    }

    public function resumeUrl(): string
    {
        return EggResource::getUrl('catalog', [
            'nest_id' => $this->nestContext,
            'search' => $this->search, 'source' => $this->source, 'category' => $this->category,
            'page' => $this->page, 'per_page' => $this->perPage, 'review_provider' => $this->selectedProvider, 'review_id' => $this->selectedId,
        ]);
    }

    public function backUrl(): string
    {
        $nest = $this->nestContext ? Nest::query()->find($this->nestContext) : null;

        return $nest && NestResource::canEdit($nest) ? NestResource::getUrl('edit', ['record' => $nest])
            : (NestResource::canViewAny() ? NestResource::getUrl() : url('/admin'));
    }

    public function reviewAction(): Action
    {
        return Action::make('review')
            ->authorize(fn (): bool => EggResource::canViewAny())
            ->modalHeading(trans('admin/catalog.review'))
            ->modalWidth('3xl')
            ->mountUsing(function (array $arguments, Schema $form): void {
                $this->openReview($arguments);
                $form->fill(['nest_id' => $this->nestContext]);
            })
            ->schema([
                Select::make('nest_id')
                    ->label(trans('admin/catalog.destination'))
                    ->options(fn () => app(CatalogAccess::class)->nests()->pluck('name', 'id'))
                    ->required()->searchable()->live(),
            ])
            ->modalContent(fn (): View => view('filament.pages.egg-catalog-review', $this->reviewData()))
            ->modalSubmitActionLabel(trans('admin/catalog.import'))
            ->modalSubmitAction(function (Action $action): Action {
                $disabled = $this->reviewToken === null || ! EggResource::canCreate() || $this->reviewData()['existing'] !== null;

                return $action->disabled($disabled)->extraAttributes(['x-bind:disabled' => $disabled ? 'true' : 'isProcessing']);
            })
            ->extraModalFooterActions([
                Action::make('openEgg')->label(trans('admin/catalog.open'))
                    ->visible(fn (): bool => ($egg = $this->reviewData()['existing']) && EggResource::canEdit($egg))
                    ->url(fn (): string => EggResource::getUrl('edit', ['record' => $this->reviewData()['existing']])),
            ])
            ->action(fn (array $data) => $this->importSelected($data));
    }

    protected function openReview(array $arguments): void
    {
        app(CatalogAccess::class)->authorize();
        $this->selectedProvider = is_string($arguments['provider'] ?? null) ? $arguments['provider'] : '';
        $this->selectedId = is_string($arguments['id'] ?? null) ? $arguments['id'] : '';
        $this->reviewToken = null;
        $this->reviewError = null;
        $this->reloadReview();
    }

    public function reloadReview(): void
    {
        app(CatalogAccess::class)->authorize();
        $this->reviewToken = null;
        $this->reviewError = null;
        try {
            $this->reviewToken = app(CatalogImportService::class)->stage($this->selectedProvider ?? '', $this->selectedId ?? '');
        } catch (CatalogException $exception) {
            $this->reviewError = $exception->reason;
        }
    }

    protected function importSelected(array $data): void
    {
        app(CatalogAccess::class)->authorize(true);
        try {
            $nestId = filter_var($data['nest_id'] ?? null, FILTER_VALIDATE_INT);
            if (! $nestId) {
                throw new CatalogException('nest');
            }
            $nest = app(CatalogAccess::class)->nest($nestId);
            $egg = app(CatalogImportService::class)->import($this->reviewToken ?? '', $nestId);
            Notification::make()
                ->title($egg->wasRecentlyCreated
                    ? trans('admin/catalog.imported', ['egg' => $egg->name, 'nest' => $nest->name])
                    : trans('admin/catalog.existing_in', ['nest' => $nest->name]))
                ->status($egg->wasRecentlyCreated ? 'success' : 'info')->send();
            $this->redirect(EggResource::canEdit($egg) ? EggResource::getUrl('edit', ['record' => $egg]) : $this->backUrl());
        } catch (CatalogException $exception) {
            $this->reviewError = $exception->reason;
            if ($exception->reason === 'expired') {
                $this->reloadReview();
                $this->reviewError = $this->reviewToken ? 'expired' : $this->reviewError;
            }
            Notification::make()->title($exception->getMessage())->danger()->send();
            $this->halt();
        } catch (AuthorizationException|HttpException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
            $this->reviewError = 'import';
            Notification::make()->title(trans('admin/catalog.errors.import'))->danger()->send();
            $this->halt();
        }
    }

    protected function reviewData(): array
    {
        app(CatalogAccess::class)->authorize();
        $entry = null;
        $parsed = null;
        $error = $this->reviewError;
        if ($this->selectedProvider && $this->selectedId) {
            try {
                if ($this->reviewToken) {
                    $stage = app(CatalogImportService::class)->review($this->reviewToken);
                    $entry = $stage['entry'];
                    $parsed = $stage['parsed'];
                } else {
                    $entry = app(EggCatalogService::class)->resolve($this->selectedProvider, $this->selectedId);
                }
            } catch (CatalogException $exception) {
                $error = $exception->reason;
                try {
                    $entry = app(EggCatalogService::class)->resolve($this->selectedProvider, $this->selectedId);
                } catch (CatalogException $exception) {
                    $entry = null;
                }
            }
        }
        $nestId = filter_var($this->mountedActions[0]['data']['nest_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
        $nest = $nestId ? app(CatalogAccess::class)->nests()->firstWhere('id', $nestId) : null;
        $existing = $nest && $entry ? Egg::query()->where('nest_id', $nest->id)->where('catalog_key', $entry->key())->first() : null;
        $sameName = $nest && $entry && ! $existing ? Egg::query()->where('nest_id', $nest->id)->where('name', $parsed['name'] ?? $entry->name)->exists() : false;

        return compact('entry', 'parsed', 'error', 'nest', 'existing', 'sameName') + ['nests' => app(CatalogAccess::class)->nests(), 'resumeUrl' => $this->resumeUrl()];
    }

    protected function getViewData(): array
    {
        app(CatalogAccess::class)->authorize();
        $statuses = [];
        $entries = [];
        foreach (EggCatalogService::PROVIDERS as $provider) {
            $statuses[$provider] = app(EggCatalogService::class)->cached($provider);
            $statuses[$provider]['error'] ??= $this->providerNotices[$provider] ?? null;
            $entries = array_merge($entries, $statuses[$provider]['entries']);
        }
        $categories = [];
        foreach ($entries as $entry) {
            if ($entry->category !== '' && ($this->source === '' || $entry->provider === $this->source)) {
                $categories[$entry->provider.'|'.$entry->category] = $entry->provider.' · '.$this->categoryLabel($entry);
            }
        }
        asort($categories);
        $needle = mb_strtolower(trim($this->search));
        $terms = preg_split('/\s+/u', $needle, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $matches = array_values(array_filter($entries, function (EggCatalogEntry $entry) use ($terms): bool {
            if (($this->source !== '' && $entry->provider !== $this->source)
                || ($this->category !== '' && $entry->provider.'|'.$entry->category !== $this->category)) {
                return false;
            }
            $text = mb_strtolower($entry->name.' '.$entry->description);
            foreach ($terms as $term) {
                if (! str_contains($text, $term)) {
                    return false;
                }
            }

            return true;
        }));
        usort($matches, fn ($a, $b) => strnatcasecmp($a->name, $b->name) ?: strcmp($a->provider.$a->id, $b->provider.$b->id));
        $total = count($matches);
        $this->perPage = in_array($this->perPage, [24, 48, 96], true) ? $this->perPage : 24;
        $pages = max(1, (int) ceil($total / $this->perPage));
        $this->page = min(max(1, $this->page), $pages);
        $visible = array_slice($matches, ($this->page - 1) * $this->perPage, $this->perPage);
        $context = $this->nestContext ? Nest::query()->find($this->nestContext) : null;
        if ($context !== null) {
            abort_unless(NestResource::canViewAny() && NestResource::canView($context), 403);
        }
        $imports = $context ? Egg::query()->where('nest_id', $context->id)->whereNotNull('catalog_key')->pluck('id', 'catalog_key')->all() : [];

        $relevant = $this->source !== '' && isset($statuses[$this->source]) ? [$statuses[$this->source]] : $statuses;
        $hasCatalog = collect($relevant)->contains(fn ($status) => $status['refreshed_at'] !== null);
        $emptyReason = ! $hasCatalog ? 'errors.unavailable' : ($this->search !== '' || $this->category !== '' ? 'no_matches' : 'empty');

        $paginator = (new LengthAwarePaginator($visible, $total, $this->perPage, $this->page, ['path' => EggResource::getUrl('catalog')]))->onEachSide(0);

        return compact('statuses', 'categories', 'total', 'pages', 'visible', 'imports', 'emptyReason', 'paginator');
    }

    public function categoryLabel(EggCatalogEntry $entry): string
    {
        if ($entry->provider === 'Pterodactyl') {
            return trans('admin/catalog.categories.'.strtolower($entry->category));
        }

        return match (strtolower($entry->category)) {
            'minecraft' => 'Minecraft',
            'rust' => 'Rust',
            'source-engine' => 'Source Engine',
            'voice-servers' => trans('admin/catalog.categories.voice_servers'),
            default => Str::headline($entry->category),
        };
    }
}
