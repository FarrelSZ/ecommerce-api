<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\ResponseFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    public function index()
    {
        $query = Auth::user()->transactions()->orderBy('id', 'desc');

        if (!is_null(request()->search)) {
            $query->where('meta', 'like', '%' . request()->search . '%');
        }

        $transactions = $query->paginate(request()->per_page ?? 10);

        return ResponseFormatter::success($transactions->through(function ($transactions) {
            return [
                'uuid' => $transactions->uuid,
                'type' => $transactions->type,
                'amount' => (float) $transactions->amount,
                'description' => isset($transactions->meta['description']) ? $transactions->meta['description'] : null,
                'meta' => $transactions->meta,
                'time' => $transactions->created_at->format('Y-m-d H:i:s'),
            ];
        }));
    }

    public function getListBank()
    {
        $banks = config('bank.list');

        return ResponseFormatter::success($banks);
    }

    public function createWithdraw()
    {
        $validator = Validator::make(request()->all(), [
            'amount' => 'required|numeric|min:1000',
            'description' => 'required|min:1|max:255',
            'bank_code' => 'required',
            'bank_account_number' => 'required|string|min:5|max:20',
            'bank_account_holder' => 'required|string|min:5|max:30',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(400, $validator->errors());
        }

        if (request()->amount > Auth::user()->balance) {
            return ResponseFormatter::error(400, null, [
                'Balance not enough'
            ]);
        }

        $banks = collect(config('bank.list'))->where('code', request()->bank_code)->first();
        if (is_null($banks)) {
            return ResponseFormatter::error(400, null, [
                'Bank not found'
            ]);
        }

        Auth::user()->withdraw(request()->amount, [
            'description' => 'Penarikan ke Bank ' . $banks['name'] . ' ' . request()->bank_account_number . ' ' . request()->description,
            'bank_code' => $banks['code'],
            'bank_name' => $banks['name'],
            'bank_account_number' => request()->bank_account_number,
            'bank_account_holder' => request()->bank_account_holder

        ]);

        return $this->index();
    }
}
