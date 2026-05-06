<?php

declare(strict_types=1);

namespace App\Http\Controllers\Stripe;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class CheckoutSuccessController extends Controller
{
    public function __invoke(Request $request, int $jobId): Response
    {
        return Inertia::render('Jobs/PaymentSuccess', [
            'job_id' => $jobId,
        ]);
    }
}
