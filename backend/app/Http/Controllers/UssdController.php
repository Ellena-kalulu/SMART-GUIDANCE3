<?php

namespace App\Http\Controllers;

use App\Services\UssdService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class UssdController extends Controller
{
    public function __construct(
        protected UssdService $ussd,
    ) {}

    /**
     * USSD gateway callback (Africa's Talking, TNM, Airtel, etc.)
     *
     * POST /api/ussd/callback
     * Params: sessionId, phoneNumber, text (optional: serviceCode)
     */
    public function callback(Request $request): Response
    {
        $sessionId   = $request->input('sessionId', $request->input('SESSION_ID', 'test-' . uniqid()));
        $phoneNumber = $request->input('phoneNumber', $request->input('MSISDN', '0990000000'));
        $text        = (string) $request->input('text', $request->input('USERDATA', ''));

        $response = $this->ussd->handle($sessionId, $phoneNumber, $text);

        return response($response, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    /** Web-based USSD simulator for local testing */
    public function simulator(): View
    {
        return view('ussd.simulator');
    }

    /** Simulator AJAX endpoint */
    public function simulate(Request $request): Response
    {
        $validated = $request->validate([
            'session_id'   => ['required', 'string', 'max:100'],
            'phone_number' => ['required', 'string', 'max:20'],
            'text'         => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $response = $this->ussd->handle(
                $validated['session_id'],
                $validated['phone_number'],
                $validated['text'] ?? '',
            );
        } catch (\Throwable $e) {
            report($e);
            $response = "END Service temporarily unavailable.\nPlease try again later.";
        }

        return response($response, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}

