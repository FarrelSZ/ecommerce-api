@extends('mails.layout')

@section('content')
    Hai {{ $order->seller->name }},<br>
    Ada order baru nih dari {{ $order->user->name }}, silahkan cek aplikasi untuk melihat detail.
    <br>
    <br>
    @foreach ($order->items as $item)
        {{ $item->product->name }} x {{ $item->qty }} = {{ $item->qty * $item->product->price }}<br>
    @endforeach
@endsection
