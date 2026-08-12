<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function __construct()
    {
        // Permissions middleware could be enabled if needed, matching CategoryController
    }

    public function index(Request $request): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;
        $query = Brand::where('company_id', $companyId);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Filter by active status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $brands = $query->with('parent')->orderBy('name')->get();

        return response()->json($brands);
    }

    public function store(Request $request): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('brands', 'name')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                })
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('brands', 'slug')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                })
            ],
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10240',
            'parent_id' => 'nullable|exists:brands,id',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $brand = new Brand();
        $brand->fill($request->except(['logo']));
        $brand->company_id = $companyId;

        // Auto-generate slug
        $slug = $request->input('slug') ?: Str::slug($request->input('name'));
        $baseSlug = $slug;
        $counter = 1;
        while (Brand::where('company_id', $companyId)->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        $brand->slug = $slug;

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('brands/logos', 'public');
            $brand->logo = '/storage/' . $path;
        }

        $brand->save();

        return response()->json([
            'message' => 'Brand created successfully',
            'brand' => $brand
        ], 201);
    }

    public function show(Brand $brand): JsonResponse
    {
        $brand->loadCount('products');
        return response()->json($brand);
    }

    public function update(Request $request, Brand $brand): JsonResponse
    {
        $companyId = auth()->user()->current_company_id;

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('brands', 'name')->ignore($brand->id)->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                })
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('brands', 'slug')->ignore($brand->id)->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                })
            ],
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10240',
            'parent_id' => 'nullable|exists:brands,id',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $brand->fill($request->except(['logo']));

        // Update slug if name or slug changes
        if ($request->has('name') || $request->has('slug')) {
            $slug = $request->input('slug') ?: Str::slug($request->input('name'));
            $baseSlug = $slug;
            $counter = 1;
            while (Brand::where('company_id', $companyId)
                ->where('slug', $slug)
                ->where('id', '!=', $brand->id)
                ->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            $brand->slug = $slug;
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($brand->logo) {
                $oldPath = str_replace('/storage/', '', $brand->logo);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('logo')->store('brands/logos', 'public');
            $brand->logo = '/storage/' . $path;
        }

        $brand->save();

        return response()->json([
            'message' => 'Brand updated successfully',
            'brand' => $brand
        ]);
    }

    public function destroy(Brand $brand): JsonResponse
    {
        if ($brand->products()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete brand with associated products. Please reassign the products first.'
            ], 422);
        }

        if ($brand->logo) {
            $oldPath = str_replace('/storage/', '', $brand->logo);
            Storage::disk('public')->delete($oldPath);
        }

        $brand->delete();

        return response()->json([
            'message' => 'Brand deleted successfully'
        ]);
    }
}
