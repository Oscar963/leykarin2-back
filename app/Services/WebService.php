<?php

namespace App\Services;

use App\Models\Banner;
use App\Models\File;
use App\Models\Page;
use App\Models\Popup;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WebService
{
    public function getAllBanners()
    {
        return Banner::orderBy('created_at', 'DESC')->where('status', 'published')->get();
    }

    public function getBannerById($id)
    {
        return Banner::findOrFail($id);
    }

    public function getAllPages()
    {
        return Page::orderBy('created_at', 'DESC')->get();
    }

    public function getPageById($id)
    {
        return Page::findOrFail($id);
    }

    public function getPageBySlug($slug)
    {
        return Page::where('slug', $slug)->firstOrFail();
    }

    public function getAllPopups()
    {
        return Popup::orderBy('created_at', 'DESC')->get();
    }

    public function getPopupById($id)
    {
        return Popup::findOrFail($id);
    }

    public function searchFiles(string $query)
    {
        return File::where('name', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->orderBy('created_at', 'DESC')
            ->get();
    }
}
