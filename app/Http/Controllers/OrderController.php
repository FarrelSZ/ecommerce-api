<?php

namespace App\Http\Controllers;

use App\Models\Order\OrderItem;
use App\ResponseFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function index()
    {
        $query = Auth::user()->orders()->with([
            'seller',
            'address',
            'items',
            'lastStatus'
        ]);

        if (request()->last_status) {
            $query->whereHas('lastStatus', function ($subQuery) {
                $subQuery->where('status', request()->last_status);
            });
        }

        if (request()->search) {
            $query->whereHas('seller', function ($subQuery) {
                $subQuery->where('store_name', 'LIKE', '%' . request()->search . '%');
            })->orWhere('invoice_number', 'LIKE', '%' . request()->search . '%');

            $productIds = \App\Models\Product\Product::where('name', 'LIKE', '%' . request()->search . '%')->pluck('id');
            $query->orWhereHas('items', function ($subQuery) use ($productIds) {
                $subQuery->whereIn('product_id', $productIds);
            });
        }

        $orders = $query->paginate(request()->per_page ?? 10);

        return ResponseFormatter::success($orders->through(function ($order) {
            return $order->api_response;
        }));
    }

    public function show(string $uuid)
    {
        $order = Auth::user()->orders()->where('uuid', $uuid)->with([
            'seller',
            'address',
            'items',
            'lastStatus'
        ])->where('uuid', $uuid)->firstOrFail();

        return ResponseFormatter::success($order->first()->api_response_detail);
    }

    public function markAsDone(string $uuid)
    {
        $order = Auth::user()->orders()->with([
            'lastStatus'
        ])->where('uuid', $uuid)->firstOrFail();

        if ($order->lastStatus->status != 'on_delivery') {
            return ResponseFormatter::error(400, null, [
                'Order status is not on delivery'
            ]);
        }

        $order->markAsDone();
        $order->refresh();

        return ResponseFormatter::success($order->api_response_detail);
    }

    public function addReview()
    {
        $validator = Validator::make(request()->all(), [
            'order_item_uuid' => 'required|exists:order_items,uuid',
            'star_seller' => 'required|numeric|min:1|max:5',
            'star_courier' => 'required|numeric|min:1|max:5',
            'description' => 'nullable|max:300',
            'attachments' => 'array',
            'attachments.*' => 'file|mimes:jpg,png,jpeg,mp4,mp3,mov,mkv,ogg,webm,wmv,flv,3gp|max:10000',
            'show_username' => 'required|in:1,0',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(400, $validator->errors());
        }

        $orderItem = OrderItem::where('uuid', request()->order_item_uuid)->firstOrFail();
        $order = $orderItem->order;
        if ($order->user_id != Auth::user()->id) {
            return ResponseFormatter::error(403, null, [
                'Not Yours'
            ]);
        }

        if ($order->lastStatus->status != 'done') {
            return ResponseFormatter::error(400, null, [
                'Order status is not done'
            ]);
        }

        if (!is_null($orderItem->review)) {
            return ResponseFormatter::error(400, null, [
                'You already reviewed this order'
            ]);
        }

        $attachments = [];
        if (is_array(request()->attachments) && count(request()->attachments) > 0) {
            foreach (request()->attachments as $attachment) {
                $attachments[] = $attachment->store('attachments', 'public');
            }
        }

        $review = DB::transaction(function () use ($order, $orderItem, $attachments) {
            $review = $orderItem->review()->create([
                'product_id' => $orderItem->product_id,
                'user_id' => $order->user_id,
                'star_seller' => request()->star_seller,
                'star_courier' => request()->star_courier,
                'variations' => collect($orderItem->variations)->map(function ($variation) {
                    return $variation['label'] . ': ' . $variation['value'];
                })->implode(', '),
                'description' => request()->description,
                'attachments' => $attachments,
                'show_username' => request()->show_username
            ]);


            // add coin
            $coin = 25000;
            Auth::user()->deposit($coin, [
                'description' => 'Review Produk ' . $orderItem->product->name
            ]);

            return $review;
        });

        return ResponseFormatter::success($review->api_response);
    }
}
