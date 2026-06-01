<?php

namespace App\Http\Controllers;

use App\Services\Mpesa\DarajaCallbackHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MpesaCallbackController extends Controller
{
    public function __construct(protected DarajaCallbackHandler $handler) {}

    /**
     * Handle STK Push payment confirmation from Daraja.
     */
    public function stkCallback(Request $request)
    {
        Log::info('MPESA STK Callback received', $request->all());

        $this->handler->handleStkCallback($request->all());

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    /**
     * Handle B2C disbursement result from Daraja.
     */
    public function b2cCallback(Request $request)
    {
        Log::info('MPESA B2C Result received', $request->all());

        $this->handler->handleB2cCallback($request->all());

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    /**
     * Handle B2C timeout notification from Daraja.
     */
    public function b2cTimeout(Request $request)
    {
        Log::warning('MPESA B2C Timeout received', $request->all());

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }
}
