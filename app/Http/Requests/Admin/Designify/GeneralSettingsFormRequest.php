<?php

namespace App\Http\Requests\Admin\Designify;

use App\Http\Requests\Admin\AdminFormRequest;

class GeneralSettingsFormRequest extends AdminFormRequest
{
    /**
     * Return all the rules to apply to this request's data.
     */
    public function rules(): array
    {
        return [
            'designify:customCopyright' => 'required|in:true,false',
            'designify:copyright' => 'required|string',
            'designify:isUnderMaintenance' => 'required|in:true,false',
            'designify:maintenance' => 'required|string',
            'designify:statusCardLink' => 'nullable|string|max:255',
            'designify:supportCardLink' => 'nullable|string|max:255',
            'designify:billingCardLink' => 'nullable|string|max:255',
            'designify:alwaysShowKillButton' => 'required|in:true,false',
        ];
    }

    public function attributes(): array
    {
        return [
            'designify:customCopyright' => 'Custom Copyright',
            'designify:copyright' => 'Copyright Text',
            'designify:isUnderMaintenance' => 'Maintenance',
            'designify:maintenance' => 'Maintenance Message',
        ];
    }
}
