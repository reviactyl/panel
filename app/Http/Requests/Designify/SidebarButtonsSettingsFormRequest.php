<?php

namespace App\Http\Requests\Designify;

use Illuminate\Support\Collection;

class SidebarButtonsSettingsFormRequest extends DesignifyFormRequest
{
    /**
     * Return all the rules to apply to this request's data.
     */
    public function rules(): array
    {
        return [
            'sidebar_buttons' => 'nullable|array|max:12',
            'sidebar_buttons.*.label' => 'nullable|string|max:60',
            'sidebar_buttons.*.url' => 'nullable|string|max:255',
            'sidebar_buttons.*.newTab' => 'nullable|boolean',
        ];
    }

    public function attributes(): array
    {
        return [
            'sidebar_buttons' => 'Sidebar Buttons',
            'sidebar_buttons.*.label' => 'Button Label',
            'sidebar_buttons.*.url' => 'Button URL',
            'sidebar_buttons.*.newTab' => 'Open in New Tab',
        ];
    }

    /**
     * Return only the settings keys expected by the sidebar button controller.
     */
    public function normalize(?array $only = null): array
    {
        $buttons = Collection::make($this->input('sidebar_buttons', []))
            ->filter(fn ($button): bool => is_array($button))
            ->map(function (array $button): array {
                $label = trim((string) data_get($button, 'label', ''));
                $url = trim((string) data_get($button, 'url', ''));
                $newTab = filter_var(data_get($button, 'newTab', false), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;

                return [
                    'label' => $label,
                    'url' => $url,
                    'newTab' => $newTab,
                ];
            })
            ->filter(fn (array $button): bool => $button['label'] !== '' && $button['url'] !== '')
            ->values()
            ->all();

        return [
            'designify:sidebarButtons' => json_encode($buttons, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '[]',
        ];
    }
}
