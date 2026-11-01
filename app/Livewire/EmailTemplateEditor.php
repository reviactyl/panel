<?php

namespace App\Livewire;

use App\Filament\Pages\Designify;
use App\Services\Mail\EmailTemplateService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class EmailTemplateEditor extends Component
{
    public function mount(): void
    {
        $this->authorizeEditor();
    }

    #[Renderless]
    public function editorPreference(): string
    {
        $this->authorizeEditor();

        return auth()->user()->fresh()->editor ?? config('app.fileEditor', 'cm');
    }

    #[Renderless]
    public function loadTemplate(string $type): array
    {
        $this->authorizeEditor();

        return app(EmailTemplateService::class)->load($type);
    }

    #[Renderless]
    public function previewTemplate(string $type, array $template): string
    {
        $this->authorizeEditor();
        $service = app(EmailTemplateService::class);
        $template = $service->validateTemplate($type, $template);

        return $service->render($template, $service->sampleData($type))['html'];
    }

    #[Renderless]
    public function saveTemplate(string $type, array $template): array
    {
        $this->authorizeEditor();
        abort_if(config('panel.load_environment_only'), 403);

        return app(EmailTemplateService::class)->save($type, $template);
    }

    #[Renderless]
    public function resetTemplate(string $type): array
    {
        $this->authorizeEditor();
        abort_if(config('panel.load_environment_only'), 403);
        $service = app(EmailTemplateService::class);
        $service->reset($type);

        return $service->defaults($type);
    }

    private function authorizeEditor(): void
    {
        abort_unless(Designify::canAccess(), 403);
    }

    public function render(): View
    {
        return view('livewire.email-template-editor', ['types' => EmailTemplateService::TYPES, 'previewValues' => collect(EmailTemplateService::TYPES)->mapWithKeys(fn ($label, $type) => [$type => app(EmailTemplateService::class)->sampleData($type)])->all()]);
    }
}
