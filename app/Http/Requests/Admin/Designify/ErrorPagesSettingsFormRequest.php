<?php

namespace App\Http\Requests\Admin\Designify;

use App\Http\Requests\Admin\AdminFormRequest;

class ErrorPagesSettingsFormRequest extends AdminFormRequest
{
    /**
     * Return all the rules to apply to this request's data.
     */
    public function rules(): array
    {
        return [
            'designify:errors:403:title' => 'present|nullable|string|max:191',
            'designify:errors:403:message' => 'present|nullable|string',
            'designify:errors:403:button' => 'present|nullable|string|max:191',
            'designify:errors:403:image' => 'present|nullable|string|max:191',
            'designify:errors:403:color' => 'present|nullable|string|max:191',
            
            'designify:errors:404:title' => 'present|nullable|string|max:191',
            'designify:errors:404:message' => 'present|nullable|string',
            'designify:errors:404:button' => 'present|nullable|string|max:191',
            'designify:errors:404:image' => 'present|nullable|string|max:191',
            'designify:errors:404:color' => 'present|nullable|string|max:191',
            
            'designify:errors:500:title' => 'present|nullable|string|max:191',
            'designify:errors:500:message' => 'present|nullable|string',
            'designify:errors:500:button' => 'present|nullable|string|max:191',
            'designify:errors:500:image' => 'present|nullable|string|max:191',
            'designify:errors:500:color' => 'present|nullable|string|max:191',
        ];
    }

    public function attributes(): array
    {
        return [
            'designify:errors:403:title' => '403 Title',
            'designify:errors:403:message' => '403 Message',
            'designify:errors:403:button' => '403 Button Text',
            'designify:errors:403:image' => '403 Image URL',
            'designify:errors:403:color' => '403 Accent Color',
            
            'designify:errors:404:title' => '404 Title',
            'designify:errors:404:message' => '404 Message',
            'designify:errors:404:button' => '404 Button Text',
            'designify:errors:404:image' => '404 Image URL',
            'designify:errors:404:color' => '404 Accent Color',
            
            'designify:errors:500:title' => '500 Title',
            'designify:errors:500:message' => '500 Message',
            'designify:errors:500:button' => '500 Button Text',
            'designify:errors:500:image' => '500 Image URL',
            'designify:errors:500:color' => '500 Accent Color',
        ];
    }
}
