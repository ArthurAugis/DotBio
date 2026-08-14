<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSeoRequest;
use App\Models\Profile;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SeoController extends Controller
{
    public function edit(): View
    {
        return view('admin.seo', ['profile' => $this->profile()]);
    }

    public function update(UpdateSeoRequest $request): RedirectResponse
    {
        $profile = $this->profile();
        $validated = $request->validated();

        if ($faviconUrl = $this->storePublicFile($request, 'favicon_file', 'uploads/favicons')) {
            $this->deleteOldPublicFile($profile->favicon_url);
            $validated['favicon_url'] = $faviconUrl;
        }

        if ($metaImageUrl = $this->storePublicFile($request, 'meta_image_file', 'uploads/meta_images')) {
            $this->deleteOldPublicFile($profile->meta_image);
            $validated['meta_image'] = $metaImageUrl;
        }

        $profile->update($validated);

        return back()->with('success', 'SEO settings updated successfully.');
    }

    private function profile(): Profile
    {
        return Profile::firstOrCreate(['user_id' => auth()->id()]);
    }
}
