<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PanelService;

/**
 * La portada del panel: qué pasó hoy y qué hay que atender.
 *
 * Las cifras las calcula PanelService; aquí solo se piden y se pintan.
 */
class DashboardController extends Controller
{
    public function __construct(private readonly PanelService $panel) {}

    public function index()
    {
        return view('admin.dashboard', $this->panel->resumen());
    }
}
