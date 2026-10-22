<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReorderLinkBlocksRequest;
use App\Http\Requests\SaveLinkBlockRequest;
use App\Http\Requests\UpdateLinkPageSettingsRequest;
use App\Models\LinkBlock;
use App\Models\Profile;
use App\Support\UploadLimit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class LinkPageController extends Controller
{
    public function index(): View
    {
        return view('admin.link-page', [
            'profile' => Profile::with('linkBlocks')->firstOrFail(),
            'maxUploadSizeMB' => UploadLimit::maxSizeInMegabytes(),
        ]);
    }

    public function settings(UpdateLinkPageSettingsRequest $request): RedirectResponse
    {
        Profile::firstOrFail()->update($request->validated());

        return back()->with('success', 'Link page settings saved.');
    }

    public function store(SaveLinkBlockRequest $request): RedirectResponse
    {
        $profile = Profile::firstOrFail();

        $block = new LinkBlock([
            'profile_id' => $profile->id,
            'sort_order' => (int) $profile->linkBlocks()->max('sort_order') + 1,
        ]);

        $this->fillBlock($block, $request)->save();

        return back()->with('success', 'Block added successfully.');
    }

    public function update(SaveLinkBlockRequest $request, LinkBlock $block): RedirectResponse
    {
        $this->fillBlock($block, $request)->save();

        return back()->with('success', 'Block updated successfully.');
    }

    public function toggle(LinkBlock $block): RedirectResponse
    {
        $block->update(['is_visible' => ! $block->is_visible]);

        return back();
    }

    public function destroy(LinkBlock $block): RedirectResponse
    {
        $this->deleteBlockFile($block->image_url);
        $this->deleteBlockFile($block->media_url);
        $block->delete();

        return back()->with('success', 'Block deleted successfully.');
    }

    public function reorder(ReorderLinkBlocksRequest $request): JsonResponse
    {
        foreach ($request->validated('order') as $index => $blockId) {
            LinkBlock::whereKey($blockId)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }

    private function fillBlock(LinkBlock $block, SaveLinkBlockRequest $request): LinkBlock
    {
        $block->fill($request->safe()->only(['type', 'title', 'subtitle', 'url', 'media_url']));

        if ($request->boolean('remove_image') || $request->hasFile('image_file')) {
            $block->image_url = $this->storePublicFile($request, 'image_file', 'uploads/link_blocks');
        }

        if ($mediaUrl = $this->storePublicFile($request, 'media_file', 'uploads/link_videos')) {
            $block->media_url = $mediaUrl;
        }

        foreach (['image_url', 'media_url'] as $column) {
            if ($block->exists && $block->isDirty($column)) {
                $this->deleteBlockFile($block->getOriginal($column));
            }
        }

        return $block;
    }

    private function deleteBlockFile(?string $url): void
    {
        if (str_contains((string) $url, '/storage/uploads/link_')) {
            $this->deleteOldPublicFile($url);
        }
    }
}
