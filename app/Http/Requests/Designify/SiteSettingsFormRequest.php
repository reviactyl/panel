<?php

namespace App\Http\Requests\Designify;

use App\Http\Requests\Designify\DesignifyFormRequest;

class SiteSettingsFormRequest extends DesignifyFormRequest
{
    /**
     * Return all the rules to apply to this request's data.
     */
    public function rules(): array
    {
        return [
            'designify:site_color' => 'required|string',
            'designify:site_title' => 'required|string',
            'designify:site_description' => 'required|string',
            'designify:site_image' => 'required|string',
            'designify:site_favicon' => 'required|string',
        ];
    }

    public function attributes(): array
    {
        return [
            'designify:site_color' => 'Site Color',
            'designify:site_title' => 'Site Title',
            'designify:site_description' => 'Site Description',
            'designify:site_image' => 'Site Banner',
            'designify:site_favicon' => 'Site Favicon',
        ];
    }
}
