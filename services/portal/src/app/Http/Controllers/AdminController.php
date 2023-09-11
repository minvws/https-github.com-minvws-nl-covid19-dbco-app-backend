<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

use function view;

class AdminController extends Controller
{
    public function index(): View
    {
        return view('admin');
    }
}
