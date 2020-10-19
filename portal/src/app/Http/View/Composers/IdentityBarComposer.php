<?php

namespace App\Http\View\Composers;

use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class IdentityBarComposer
{
    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $user = Session::get('user');
        $view->with('userName', $user->name);
    }
}
