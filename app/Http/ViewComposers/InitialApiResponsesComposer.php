<?php

namespace App\Http\ViewComposers;

use App\Services\Http\InitialApiResponses;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InitialApiResponsesComposer
{
    public function __construct(private Request $request, private InitialApiResponses $responses) {}

    public function compose(View $view): void
    {
        $view->with('initialApiResponses', $this->request->method() === 'GET'
            ? $this->responses->forPage($this->request, $this->request->path(), (int) $this->request->query('page', 1), websocket: true)
            : []);
    }
}
