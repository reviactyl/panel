<?php

namespace App\Http\Controllers\Base;

use Illuminate\View\View;
use App\Http\Controllers\Controller;
use Illuminate\View\Factory as ViewFactory;
use App\Contracts\Repository\ServerRepositoryInterface;

class IndexController extends Controller
{
    /**
     * IndexController constructor.
     */
    public function __construct(
        protected ServerRepositoryInterface $repository,
        protected ViewFactory $view,
    ) {
    }

    /**
     * Returns listing of user's servers.
     */
    public function index(): View
    {
        return view('templates/base.core');
    }
}
