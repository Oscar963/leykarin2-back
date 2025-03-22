<?php

namespace App\Services;

use App\Models\Anexo;
use App\Models\Banner;
use App\Models\File;
use App\Models\Mobile;
use App\Models\Page;
use App\Models\Popup;

class WebService
{
    public function getAllBanners()
    {
        return Banner::where('status', 'published')
            ->where(function ($query) {
                $query->whereNull('date_expiration') // Si no tiene fecha de expiración, se muestra
                    ->orWhere('date_expiration', '>=', now()); // Si la fecha de expiración es en el futuro
            })
            ->orderBy('created_at', 'DESC')
            ->select(['image', 'link', 'date_expiration', 'status'])
            ->get();
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
        return Page::where('slug', $slug)->with('files')->select(['id', 'title', 'content', 'slug', 'status', 'image'])->firstOrFail();
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

    public function searchAnexos(?string $query, int $perPage = 15)
    {
        $queryBuilder = Anexo::orderBy('created_at', 'DESC');

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('internal_number', 'LIKE', "%{$query}%")
                    ->orWhere('external_number', 'LIKE', "%{$query}%")
                    ->orWhere('office', 'LIKE', "%{$query}%")
                    ->orWhere('unit', 'LIKE', "%{$query}%")
                    ->orWhere('person', 'LIKE', "%{$query}%");
            });
        }
        return $queryBuilder->select(['external_number', 'internal_number', 'office', 'unit', 'person'])->paginate($perPage);
    }

    public function searchMobiles(?string $query, int $perPage = 15)
    {
        $queryBuilder = Mobile::orderBy('created_at', 'DESC');

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('number', 'LIKE', "%{$query}%")
                    ->orWhere('office', 'LIKE', "%{$query}%")
                    ->orWhere('direction', 'LIKE', "%{$query}%")
                    ->orWhere('person', 'LIKE', "%{$query}%");
            });
        }

        return $queryBuilder->paginate($perPage);
    }
}
