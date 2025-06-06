<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\ResponseFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Auth::user()->products()->with([
            'category',
            'images',
            'variations',
        ]);

        if (request()->search) {
            $query->where('name', 'like', '%' . request()->search . '%');
        }

        if (request()->category) {
            $query->whereHas('category', function ($subQuery) {
                $subQuery->where('name', 'like', '%' . request()->category . '%');
            });
        }

        $products = $query->paginate(request()->per_page ?? 10);

        return ResponseFormatter::success($products->through(function ($product) {
            return $product->api_response_seller;
        }));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->getValidation());

        if ($validator->fails()) {
            return ResponseFormatter::error(400, $validator->errors());
        }

        $payload = $this->prepareData($validator->validated());
        $product = DB::transaction(function () use ($payload) {
            $product = Auth::user()->products()->create($payload);

            foreach ($payload['variations'] as $variation) {
                $product->variations()->create($variation);
            }

            foreach ($payload['images'] as $image) {
                $product->images()->create($image);
            }

            return $product;
        });

        $product->refresh();

        return ResponseFormatter::success($product->api_response_seller);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $uuid)
    {
        $rules = $this->getValidation();
        $rules['old_images'] = 'array';
        $rules['old_images.*'] = 'url';

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return ResponseFormatter::error(400, $validator->errors());
        }

        $payload = $this->prepareData($validator->validated());
        $product = DB::transaction(function () use ($payload, $uuid) {
            $product = Auth::user()->products()->where('uuid', $uuid)->firstOrFail();
            $product->update($payload);

            $product->variations()->delete();
            foreach ($payload['variations'] as $variation) {
                $product->variations()->create($variation);
            }

            foreach ($product->images as $image) {
                if (!in_array($image->image, $payload['old_images'])) {
                    Storage::disk('public')->delete($image->image);
                    $image->delete();
                }
            }
            foreach ($payload['images'] as $image) {
                $product->images()->create($image);
            }

            return $product;
        });

        $product->refresh();

        return ResponseFormatter::success($product->api_response_seller);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid)
    {
        $product = Auth::user()->products()->where('uuid', $uuid)->firstOrFail();
        $product->delete();

        return ResponseFormatter::success([
            'is_deleted' => true
        ]);
    }

    private function getValidation()
    {
        return [
            'name' => 'required|string|min:5|max:150',
            'price' => 'required|numeric|min:1000',
            'price_sale' => 'nullable|numeric|min:500',
            'stock' => 'required|numeric|min:0',
            'category_slug' => 'required|exists:categories,slug',
            'description' => 'required|min:20|max:500',
            'weight' => 'required|numeric|min:1',
            'length' => 'required|numeric|min:1',
            'width' => 'required|numeric|min:1',
            'height' => 'required|numeric|min:1',
            'video' => 'nullable|file|mimes:mp4,mov,avi,wmv,flv, mkv, webm, ogg, mp3|max:30720',
            'images' => 'required|array|min:1|max:9',
            'images.*' => 'required|image|max:2024',
            'variations' => 'array',
            'variations.*.name' => 'required|string|min:3|max:255',
            'variations.*.values' => 'array',
            'variations.*.values.*' => 'string|min:3|max:200',
        ];
    }

    private function prepareData(array $payload)
    {
        $payload['category_id'] = Category::where('slug', $payload['category_slug'])->firstOrFail()->id;
        unset($payload['category_slug']);

        $payload['slug'] = Str::slug($payload['name']) . '-' . Str::random(5);

        if (!is_null($payload['video'])) {
            $payload['video'] = $payload['video']->store('products/video', 'public');
        }

        $images = [];
        foreach ($payload['images'] as $image) {
            $images[] = [
                'image' => $image->store('products/images', 'public')
            ];
        }

        $payload['images'] = $images;

        if (isset($payload['old_images'])) {
            $oldImages = [];
            foreach ($payload['old_images'] as $oldImage) {
                $oldImages[] = str_replace(config('app.url') . '/storage/', '', $oldImage);
            }
            $payload['old_images'] = $oldImages;
        } else {
            $payload['old_images'] = [];
        }

        return $payload;
    }
}
