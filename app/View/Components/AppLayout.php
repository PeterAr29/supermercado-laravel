<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    /**
     * Apuntaba a 'layouts.layout', que solo tenía `@yield('content')` y nunca
     * imprimía `$slot`: el perfil y el dashboard salían vacíos (H-47).
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}
