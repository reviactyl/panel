<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;

class DesignifyComposer
{
    private array $reviactylDefaults;

    public function __construct()
    {
        $this->reviactylDefaults = [
            'customCopyright' => config('designify.customCopyright', true),
            'copyright' => config('designify.copyright') ?? 'Powered by [Reviactyl](https://reviactyl.app/)',
            'isUnderMaintenance' => config('designify.isUnderMaintenance', false),
            'maintenance' => config('designify.maintenance') ?? 'We are currently under maintenance. Kindly check back later!',
            'colorPrimary' => config('designify.colorPrimary') ?? '#3b82f6',
            'colorSuccess' => config('designify.colorSuccess') ?? '#3D8F1F',
            'colorDanger' => config('designify.colorDanger') ?? '#8F1F20',
            'colorSecondary' => config('designify.colorSecondary') ?? '#2B2B40',
            'colorDiscord' => config('designify.colorDiscord') ?? '#5865F2',
            'color50' => config('designify.color50') ?? '#fafafa',
            'color100' => config('designify.color100') ?? '#f4f4f5',
            'color200' => config('designify.color200') ?? '#e4e4e7',
            'color300' => config('designify.color300') ?? '#d4d4d8',
            'color400' => config('designify.color400') ?? '#9f9fa9',
            'color500' => config('designify.color500') ?? '#71717b',
            'color600' => config('designify.color600') ?? '#52525c',
            'color700' => config('designify.color700') ?? '#3f3f46',
            'color800' => config('designify.color800') ?? '#27272a',
            'color900' => config('designify.color900') ?? '#18181b',
            'color950' => config('designify.color950') ?? '#09090b',
            'color50L' => config('designify.color50L') ?? '#09090b',
            'color100L' => config('designify.color100L') ?? '#18181b',
            'color200L' => config('designify.color200L') ?? '#27272a',
            'color300L' => config('designify.color300L') ?? '#3f3f46',
            'color400L' => config('designify.color400L') ?? '#52525c',
            'color500L' => config('designify.color500L') ?? '#71717b',
            'color600L' => config('designify.color600L') ?? '#9f9fa9',
            'color700L' => config('designify.color700L') ?? '#d4d4d8',
            'color800L' => config('designify.color800L') ?? '#e4e4e7',
            'color900L' => config('designify.color900L') ?? '#f4f4f5',
            'color950L' => config('designify.color950L') ?? '#fafafa',
            'sidebarLogout' => config('designify.sidebarLogout', false),
            'sidebarButtons' => $this->getSidebarButtonsConfig(),
            'background' => config('designify.background') ?? 'none',
            'radius' => config('designify.radius') ?? '15px',
            'allocationBlur' => config('designify.allocationBlur', true),
            'fontFamily' => config('designify.fontFamily') ?? 'Poppins',
            'alertType' => config('designify.alertType') ?? 'info',
            'alertMessage' => config('designify.alertMessage') ?? '**Welcome to Reviactyl!** You can modify Theme Look & Feel using [Designify](/admin/designify) at the administration area.',
            'alerts' => $this->getAlertsConfig(),
            'site_color' => config('designify.site_color') ?? '#3b82f6',
            'site_title' => config('designify.site_title') ?? 'Reviactyl',
            'site_description' => config('designify.site_description') ?? 'Our official control panel made better with Reviactyl.',
            'site_image' => config('designify.site_image') ?? '/reviactyl/logo.png',
            'site_favicon' => config('designify.site_favicon') ?? '/reviactyl/icon.png',
            'errors' => [
                '403' => [
                    'title' => config('designify.errors.403.title') ?? 'Access Forbidden',
                    'message' => config('designify.errors.403.message') ?? 'You do not have permission to access this resource. Please contact the administrator if you believe this is an error.',
                    'button' => config('designify.errors.403.button') ?? 'Back to Dashboard',
                    'image' => config('designify.errors.403.image') ?? '',
                    'color' => config('designify.errors.403.color') ?? '#f59e0b',
                ],
                '404' => [
                    'title' => config('designify.errors.404.title') ?? 'Page Not Found',
                    'message' => config('designify.errors.404.message') ?? 'The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.',
                    'button' => config('designify.errors.404.button') ?? 'Back to Dashboard',
                    'image' => config('designify.errors.404.image') ?? '',
                    'color' => config('designify.errors.404.color') ?? '#3b82f6',
                ],
                '500' => [
                    'title' => config('designify.errors.500.title') ?? 'Internal Server Error',
                    'message' => config('designify.errors.500.message') ?? 'We encountered an error while processing your request. Please try again later or contact support if the problem persists.',
                    'button' => config('designify.errors.500.button') ?? 'Try Again',
                    'image' => config('designify.errors.500.image') ?? '',
                    'color' => config('designify.errors.500.color') ?? '#ef4444',
                ],
            ],
            'statusCardLink' => config('designify.statusCardLink') ?? '',
            'supportCardLink' => config('designify.supportCardLink') ?? '',
            'billingCardLink' => config('designify.billingCardLink') ?? '',
            'alwaysShowKillButton' => config('designify.alwaysShowKillButton', false),
            'cardType' => config('designify.cardType') ?? 'grid',
            'layoutType' => config('designify.layoutType') ?? 'modern',
        ];

        $this->reviactylDefaults['colorMutedText'] = $this->getAccessibleMutedText($this->reviactylDefaults);
    }

    private function getAccessibleMutedText(array $palette): string
    {
        $backgrounds = [$palette['color800'], $palette['color900'], $palette['color950']];
        $candidates = [
            $palette['color400'],
            $palette['color300'],
            $palette['color200'],
            $palette['color100'],
            $palette['color50'],
            '#ffffff',
            '#000000',
        ];

        $bestCandidate = $candidates[0];
        $bestMinimumContrast = 0.0;

        foreach ($candidates as $candidate) {
            $minimumContrast = min(array_map(
                fn (string $background): float => $this->contrastRatio($candidate, $background),
                $backgrounds,
            ));

            if ($minimumContrast >= 4.5) {
                return $candidate;
            }

            if ($minimumContrast > $bestMinimumContrast) {
                $bestCandidate = $candidate;
                $bestMinimumContrast = $minimumContrast;
            }
        }

        return $bestCandidate;
    }

    private function contrastRatio(string $foreground, string $background): float
    {
        $foregroundLuminance = $this->relativeLuminance($foreground);
        $backgroundLuminance = $this->relativeLuminance($background);

        return (max($foregroundLuminance, $backgroundLuminance) + 0.05)
            / (min($foregroundLuminance, $backgroundLuminance) + 0.05);
    }

    private function relativeLuminance(string $hex): float
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = implode('', array_map(fn (string $character): string => $character.$character, str_split($hex)));
        }

        $channels = array_map(function (string $channel): float {
            $value = hexdec($channel) / 255;

            return $value <= 0.04045 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
        }, str_split($hex, 2));

        return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
    }

    private function getAlertsConfig(): array
    {
        $fallback = [[
            'type' => config('designify.alertType') ?? 'info',
            'message' => config('designify.alertMessage') ?? '**Welcome to Reviactyl!** You can modify Theme Look & Feel using [Designify](/admin/designify) at the administration area.',
        ]];

        $alerts = config('designify.alerts');

        if (is_string($alerts)) {
            $decoded = json_decode($alerts, true);
            $alerts = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($alerts) || empty($alerts)) {
            return $fallback;
        }

        $normalized = [];
        foreach ($alerts as $alert) {
            if (! is_array($alert)) {
                continue;
            }

            $type = (string) ($alert['type'] ?? 'info');
            $message = (string) ($alert['message'] ?? '');

            $normalized[] = [
                'type' => $type,
                'message' => $message,
            ];
        }

        return empty($normalized) ? $fallback : $normalized;
    }

    private function getSidebarButtonsConfig(): array
    {
        $buttons = config('designify.sidebarButtons');

        if (is_string($buttons)) {
            $decoded = json_decode($buttons, true);
            $buttons = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($buttons)) {
            return [];
        }

        $normalized = [];
        foreach ($buttons as $button) {
            if (! is_array($button)) {
                continue;
            }

            $label = trim((string) ($button['label'] ?? ''));
            $url = trim((string) ($button['url'] ?? ''));
            $newTab = filter_var($button['newTab'] ?? false, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;

            if ($label === '' || $url === '') {
                continue;
            }

            $normalized[] = [
                'label' => $label,
                'url' => $url,
                'newTab' => $newTab,
            ];
        }

        return $normalized;
    }

    public function compose(View $view): void
    {
        $view->with('panelConfiguration', $this->reviactylDefaults);
    }
}
