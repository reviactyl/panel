<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Contracts\Repository\SettingsRepositoryInterface;

class DesignifyComposer
{
    private array $reviactylDefaults;
    private array $Theme1;
    private array $Theme2;
    private array $Theme3;
    private array $Theme4;
    private array $Theme5;
    private array $Theme6;
    private array $Theme7;

    public function __construct(
        private SettingsRepositoryInterface $settings,
    ) {
        $this->Theme1 = [
            'name' => config('designify.theme1.name') ?? 'Petrascia',
            'colorPrimary' => config('designify.theme1.colorPrimary') ?? '#3b82f6',
            'color50' => config('designify.theme1.color50') ?? '#f8f9fa',
            'color100' => config('designify.theme1.color100') ?? '#e1e4e8',
            'color200' => config('designify.theme1.color200') ?? '#c5cbd3',
            'color300' => config('designify.theme1.color300') ?? '#9aa5b1',
            'color400' => config('designify.theme1.color400') ?? '#6c7885',
            'color500' => config('designify.theme1.color500') ?? '#55606d',
            'color600' => config('designify.theme1.color600') ?? '#47505c',
            'color700' => config('designify.theme1.color700') ?? '#38414d',
            'color800' => config('designify.theme1.color800') ?? '#2f3741',
            'color900' => config('designify.theme1.color900') ?? '#1d232b',
        ];

        $this->Theme2 = [
            'name' => config('designify.theme2.name') ?? 'Pink',
            'colorPrimary' => config('designify.theme2.colorPrimary') ?? '#D11EB2',
            'color50' => config('designify.theme2.color50') ?? '#f8f9fa',
            'color100' => config('designify.theme2.color100') ?? '#D7CFD6',
            'color200' => config('designify.theme2.color200') ?? '#BEAABB',
            'color300' => config('designify.theme2.color300') ?? '#A2739B',
            'color400' => config('designify.theme2.color400') ?? '#7C5978',
            'color500' => config('designify.theme2.color500') ?? '#765E78',
            'color600' => config('designify.theme2.color600') ?? '#5A4256',
            'color700' => config('designify.theme2.color700') ?? '#361F32',
            'color800' => config('designify.theme2.color800') ?? '#280D25',
            'color900' => config('designify.theme2.color900') ?? '#160613',
        ];

        $this->Theme3 = [
            'name' => config('designify.theme3.name') ?? 'Purple',
            'colorPrimary' => config('designify.theme3.colorPrimary') ?? '#8423C0',
            'color50' => config('designify.theme3.color50') ?? '#f8f9fa',
            'color100' => config('designify.theme3.color100') ?? '#D3D0D7',
            'color200' => config('designify.theme3.color200') ?? '#B4ABB8',
            'color300' => config('designify.theme3.color300') ?? '#8F7A9E',
            'color400' => config('designify.theme3.color400') ?? '#6D5A79',
            'color500' => config('designify.theme3.color500') ?? '#695C74',
            'color600' => config('designify.theme3.color600') ?? '#4D3F56',
            'color700' => config('designify.theme3.color700') ?? '#291F34',
            'color800' => config('designify.theme3.color800') ?? '#1B0E27',
            'color900' => config('designify.theme3.color900') ?? '#0E0615',
        ];

        $this->Theme4 = [
            'name' => config('designify.theme4.name') ?? 'Orange',
            'colorPrimary' => config('designify.theme4.colorPrimary') ?? '#CF721B',
            'color50' => config('designify.theme4.color50') ?? '#f8f9fa',
            'color100' => config('designify.theme4.color100') ?? '#CBC2C0',
            'color200' => config('designify.theme4.color200') ?? '#B6A3A0',
            'color300' => config('designify.theme4.color300') ?? '#9E766F',
            'color400' => config('designify.theme4.color400') ?? '#765954',
            'color500' => config('designify.theme4.color500') ?? '#77584F',
            'color600' => config('designify.theme4.color600') ?? '#553E3B',
            'color700' => config('designify.theme4.color700') ?? '#341E1A',
            'color800' => config('designify.theme4.color800') ?? '#270F0A',
            'color900' => config('designify.theme4.color900') ?? '#150704',
        ];

        $this->Theme5 = [
            'name' => config('designify.theme5.name') ?? 'Red',
            'colorPrimary' => config('designify.theme5.colorPrimary') ?? '#C81B1B',
            'color50' => config('designify.theme5.color50') ?? '#f8f9fa',
            'color100' => config('designify.theme5.color100') ?? '#C0B5B2',
            'color200' => config('designify.theme5.color200') ?? '#AD9693',
            'color300' => config('designify.theme5.color300') ?? '#966A68',
            'color400' => config('designify.theme5.color400') ?? '#71524D',
            'color500' => config('designify.theme5.color500') ?? '#6C554E',
            'color600' => config('designify.theme5.color600') ?? '#503B36',
            'color700' => config('designify.theme5.color700') ?? '#331C17',
            'color800' => config('designify.theme5.color800') ?? '#270F08',
            'color900' => config('designify.theme5.color900') ?? '#150603',
        ];

        $this->Theme6 = [
            'name' => config('designify.theme6.name') ?? 'Midnight',
            'colorPrimary' => config('designify.theme6.colorPrimary') ?? '#6366f1',
            'color50' => config('designify.theme6.color50') ?? '#f8fafc',
            'color100' => config('designify.theme6.color100') ?? '#f1f5f9',
            'color200' => config('designify.theme6.color200') ?? '#e2e8f0',
            'color300' => config('designify.theme6.color300') ?? '#cbd5e1',
            'color400' => config('designify.theme6.color400') ?? '#94a3b8',
            'color500' => config('designify.theme6.color500') ?? '#64748b',
            'color600' => config('designify.theme6.color600') ?? '#475569',
            'color700' => config('designify.theme6.color700') ?? '#334155',
            'color800' => config('designify.theme6.color800') ?? '#1e293b',
            'color900' => config('designify.theme6.color900') ?? '#0f172a',
        ];

        $this->Theme7 = [
            'name' => config('designify.theme7.name') ?? 'Monochrome',
            'colorPrimary' => config('designify.theme7.colorPrimary') ?? '#000000',
            'color50' => config('designify.theme7.color50') ?? '#ffffff',
            'color100' => config('designify.theme7.color100') ?? '#f5f5f5',
            'color200' => config('designify.theme7.color200') ?? '#e5e5e5',
            'color300' => config('designify.theme7.color300') ?? '#d4d4d4',
            'color400' => config('designify.theme7.color400') ?? '#a3a3a3',
            'color500' => config('designify.theme7.color500') ?? '#737373',
            'color600' => config('designify.theme7.color600') ?? '#525252',
            'color700' => config('designify.theme7.color700') ?? '#404040',
            'color800' => config('designify.theme7.color800') ?? '#262626',
            'color900' => config('designify.theme7.color900') ?? '#171717',
        ];

        $this->reviactylDefaults = [
            'customCopyright' => config('designify.customCopyright', true),
            'copyright' => config('designify.copyright') ?? 'Powered by [Reviactyl](https://reviactyl.dev/)',
            'isUnderMaintenance' => config('designify.isUnderMaintenance', false),
            'maintenance' => config('designify.maintenance') ?? 'We are currently under maintenance. Kindly check back later!',
            'colorPrimary' => config('designify.colorPrimary') ?? '#3b82f6',
            'colorSuccess' => config('designify.colorSuccess') ?? '#3D8F1F',
            'colorDanger' => config('designify.colorDanger') ?? '#8F1F20',
            'colorSecondary' => config('designify.colorSecondary') ?? '#2B2B40',
            'colorDiscord' => config('designify.colorDiscord') ?? '#5865F2',
            'color50' => config('designify.color50') ?? '#F4F4F5',
            'color100' => config('designify.color100') ?? '#DEDEE2',
            'color200' => config('designify.color200') ?? '#D2D2DB',
            'color300' => config('designify.color300') ?? '#8282A4',
            'color400' => config('designify.color400') ?? '#5E5E7F',
            'color500' => config('designify.color500') ?? '#42425B',
            'color600' => config('designify.color600') ?? '#1B1B21',
            'color700' => config('designify.color700') ?? '#141416',
            'color800' => config('designify.color800') ?? '#070709',
            'color900' => config('designify.color900') ?? '#07070C',
            'theme1' => $this->Theme1,
            'theme2' => $this->Theme2,
            'theme3' => $this->Theme3,
            'theme4' => $this->Theme4,
            'theme5' => $this->Theme5,
            'theme6' => $this->Theme6,
            'theme7' => $this->Theme7,
            'themeSelector' => config('designify.themeSelector', false),
            'sidebarLogout' => config('designify.sidebarLogout', false),
            'background' => config('designify.background') ?? 'none',
            'radius' => config('designify.radius') ?? '15px',
            'allocationBlur' => config('designify.allocationBlur', true),
            'fontFamily' => config('designify.fontFamily') ?? 'Poppins',
            'alertType' => config('designify.alertType') ?? 'info',
            'alertMessage' => config('designify.alertMessage') ?? '**Welcome to Reviactyl!** You can modify Theme Look & Feel using [Designify](/admin/designify) at the administration area.',
            'site_color' => config('designify.site_color') ?? '#3b82f6',
            'site_title' => config('designify.site_title') ?? 'Reviactyl',
            'site_description' => config('designify.site_description') ?? 'Our official control panel made better with Reviactyl.',
            'site_image' => config('designify.site_image') ?? '/reviactyl/logo.png',
            'site_favicon' => config('designify.site_favicon') ?? '/reviactyl/icon.png',
            'statusCardLink' => config('designify.statusCardLink') ?? '',
            'supportCardLink' => config('designify.supportCardLink') ?? '',
            'billingCardLink' => config('designify.billingCardLink') ?? '',
            'alwaysShowKillButton' => config('designify.alwaysShowKillButton', false),
        ];
    }

    public function compose(View $view): void
    {
        $view->with('reviactylConfiguration', $this->reviactylDefaults);
    }
}