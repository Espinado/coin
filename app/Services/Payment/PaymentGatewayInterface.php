<?php

namespace App\Services\Payment;

use App\Models\Deposit;
use App\Models\Withdrawal;
use App\Services\Payment\Dtos\DepositIntentDto;
use App\Services\Payment\Dtos\PayoutRequestDto;
use App\Services\Payment\Dtos\PayoutStatusDto;
use App\Services\Payment\Dtos\VerifiedIpnEvent;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    public function createDepositIntent(Deposit $deposit): DepositIntentDto;

    public function sendPayout(Withdrawal $withdrawal): PayoutRequestDto;

    public function getPayoutStatus(Withdrawal $withdrawal): PayoutStatusDto;

    public function verifyIpn(Request $request): VerifiedIpnEvent;
}
