<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Repositories\Dashboard\DashboardLogicRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardLogicRepository $dashboardLogicRepo
    ) {
    }

    /**
     * Atgriež galveno paneli (Dashboard)
     */
    public function index(Request $request): Response
    {
        $data = $this->dashboardLogicRepo->getDashboardData($request->user());

        return Inertia::render('Dashboard', $data);
    }
}