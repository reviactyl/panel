<?php

namespace App\Http\Controllers\Admin;

use Illuminate\View\View;
use App\Http\Controllers\Controller;
use App\Services\Helpers\SoftwareVersionService;

class BaseController extends Controller
{
    /**
     * BaseController constructor.
     */
    public function __construct(private SoftwareVersionService $version)
    {
    }

    /**
     * Return the admin index view.
     */
    public function index(): View
    {
        $logs = \App\Models\ActivityLog::with('actor')
            ->orderBy('timestamp', 'desc')
            ->take(10)
            ->get();

        return view('admin.index', [
            'version' => $this->version,
            'logs' => $logs
        ]);
    }
}
