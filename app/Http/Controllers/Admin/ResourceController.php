<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResourceController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = $request->get('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 20, 50, 100]) ? $perPage : 10;

        $resources = Resource::withCount('cityResources')->latest()->paginate($perPage);
        return view('admin.resources.index', compact('resources'));
    }

    public function edit(Resource $resource): View
    {
        return view('admin.resources.edit', [
            'resource' => $resource->load(['cityResources.city']),
        ]);
    }

    public function update(Request $request, Resource $resource): RedirectResponse
    {
        $request->validate([
            'base_price' => 'required|integer|min:0',
        ]);

        $resource->update(['base_price' => $request->base_price]);

        return redirect()->route('admin.resources.index')
            ->with('success', __('admin.resource_updated'));
    }
}
