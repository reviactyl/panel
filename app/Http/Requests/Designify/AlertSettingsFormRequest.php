<?php

namespace App\Http\Requests\Designify;

use Illuminate\Support\Collection;
use App\Http\Requests\Admin\AdminFormRequest;

class AlertSettingsFormRequest extends AdminFormRequest
{
    /**
     * Return all the rules to apply to this request's data.
     */
    public function rules(): array
    {
        return [
            'alerts' => 'required|array|min:1',
            'alerts.*.type' => 'required|string|in:info,announcement,success,warning,danger,disabled',
            'alerts.*.message' => 'required|string',
        ];
    }

    public function attributes(): array
    {
        return [
            'alerts' => 'Alerts',
            'alerts.*.type' => 'Alert Type',
            'alerts.*.message' => 'Alert Message',
        ];
    }

    /**
     * Return only the settings keys expected by the alert controller.
     */
    public function normalize(?array $only = null): array
    {
        $alerts = Collection::make($this->input('alerts', []))
            ->map(function ($alert): array {
                return [
                    'type' => (string) data_get($alert, 'type', 'info'),
                    'message' => (string) data_get($alert, 'message', ''),
                ];
            })
            ->values()
            ->all();

        $firstAlert = $alerts[0] ?? [
            'type' => 'info',
            'message' => '',
        ];

        return [
            'designify:alerts' => json_encode($alerts, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '[]',
            'designify:alertType' => $firstAlert['type'],
            'designify:alertMessage' => $firstAlert['message'],
        ];
    }
}
