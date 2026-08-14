<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReorderLinksRequest;
use App\Http\Requests\StoreLinkRequest;
use App\Http\Requests\UpdateLinkRequest;
use App\Models\Link;
use App\Models\Profile;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class LinkController extends Controller
{
    private const DEFAULT_COLOR = '#8b5cf6';

    public function index(): View
    {
        $profile = Profile::with('links')->firstOrFail();

        return view('admin.links', compact('profile'));
    }

    public function store(StoreLinkRequest $request): RedirectResponse
    {
        $profile = Profile::firstOrFail();

        $profile->links()->create(array_merge($request->validated(), [
            'color' => $request->validated('color') ?? self::DEFAULT_COLOR,
            'hover_effect' => $request->validated('hover_effect') ?? 'none',
            'sort_order' => (int) $profile->links()->max('sort_order') + 1,
            'is_visible' => true,
        ]));

        return back()->with('success', 'Link added successfully.');
    }

    public function update(UpdateLinkRequest $request, Link $link): RedirectResponse
    {
        $link->update($request->validated());

        return back()->with('success', 'Link updated successfully.');
    }

    public function destroy(Link $link): RedirectResponse
    {
        $link->delete();

        return back()->with('success', 'Link deleted successfully.');
    }

    public function reorder(ReorderLinksRequest $request): JsonResponse
    {
        foreach ($request->validated('order') as $index => $linkId) {
            Link::whereKey($linkId)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }
}
