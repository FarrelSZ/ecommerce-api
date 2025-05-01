<?php

namespace App\Http\Controllers;

use App\Models\Address\City;
use App\Models\Address\Province;
use Illuminate\Http\Request;
use App\ResponseFormatter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\Log;

class AddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function getProvince()
    {
        $provinces = Province::get(['uuid', 'name']);

        return ResponseFormatter::success($provinces);
    }

    public function getCity()
    {
        $query = City::query();
        if (request()->province_uuid) {
            $query = $query->where('province_id', function ($subQuery) {
                $subQuery->from('provinces')->where('uuid', request()->province_uuid)->select('id');
            });
        }

        if (request()->search) {
            $query = $query->where('name', 'like', '%' . request()->search . '%');
        }

        $cities = $query->get();

        return ResponseFormatter::success($cities->pluck('api_response'));
    }

    public function index()
    {
        $addresses = Auth::user()->addresses;

        Log::info('All addresses:', $addresses->toArray());

        return ResponseFormatter::success($addresses->pluck('api_response'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make(request()->all(), $this->getValidation());

        if ($validator->fails()) {
            return ResponseFormatter::error(400, $validator->errors());
        }

        $address = Auth::user()->addresses()->create($this->prepareData());
        $address->refresh();

        return $this->show($address->uuid);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid)
    {

        $address = Auth::user()->addresses()->where('uuid', $uuid)->firstOrfail();

        return ResponseFormatter::success($address->api_response);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $uuid)
    {
        $validator = \Illuminate\Support\Facades\Validator::make(request()->all(), $this->getValidation());

        if ($validator->fails()) {
            return ResponseFormatter::error(400, $validator->errors());
        }

        $address = Auth::user()->addresses()->where('uuid', $uuid)->firstOrFail();
        $address->update($this->prepareData());
        $address->refresh();



        return $this->show($address->uuid);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid)
    {
        $address = Auth::user()->addresses()->where('uuid', $uuid)->firstOrFail();
        if ($address) {
            $address->delete();

            return ResponseFormatter::success([
                'is_deleted' => true
            ]);
        } else {
            // Return a 404 response or a custom error message
            return ResponseFormatter::error([
                'message' => 'Address not found',
            ]);
        }
    }

    protected function getValidation()
    {

        return [
            'is_default' => 'required|in:1,0',
            'receiver_name' => 'required|min:5|max:40',
            'receiver_phone' => 'required|min:5|max:20',
            'city_uuid' => 'required|exists:cities,uuid',
            'district' => 'required|min:3|max:50',
            'postal_code' => 'required|numeric',
            'detail_address' => 'nullable|max:300',
            'address_note' => 'nullable|max:300',
            'type' => 'required|in:office,home',
        ];
    }

    protected function prepareData()
    {

        $payload = request()->only([
            'is_default',
            'receiver_name',
            'receiver_phone',
            'city_uuid',
            'district',
            'postal_code',
            'detail_address',
            'address_note',
            'type',
        ]);
        $payload['city_id'] = City::where('uuid', $payload['city_uuid'])->firstOrFail()->id;


        if ($payload['is_default'] == 1) {
            Auth::user()->addresses()->update([
                'is_default' => false
            ]);
        }

        return $payload;
    }

    public function setDefault(string $uuid)
    {
        $address = Auth::user()->addresses()->where('uuid', $uuid)->firstOrFail();
        $address->update([
            'is_default' => true
        ]);

        Auth::user()->addresses()->where('id', '!=', $address->id)->update([
            'is_default' => false
        ]);



        return ResponseFormatter::success([
            'is_success' => true
        ]);
    }
}
