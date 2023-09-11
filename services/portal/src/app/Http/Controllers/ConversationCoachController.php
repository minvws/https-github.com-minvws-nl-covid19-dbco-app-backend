<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

use function view;

class ConversationCoachController extends Controller
{
    public function index(): View
    {
        return view('conversation-coach');
    }
}
