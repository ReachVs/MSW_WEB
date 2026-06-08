<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServicesController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $services = Service::query()
            ->active()
            ->orderBy('sort_order')
            ->get();

        // Group services by main_category
        $grouped = [
            'maintenance' => [
                'name' => 'Maintenance Services',
                'icon' => 'build',
                'subcategories' => [],
            ],
            'washing' => [
                'name' => 'Washing Services',
                'icon' => 'wash',
                'subcategories' => [],
            ],
            'engine_checkup' => [
                'name' => 'Engine Check Up',
                'icon' => 'monitor_heart',
                'subcategories' => [],
            ],
            'tuning' => [
                'name' => 'Tuning Performance',
                'icon' => 'speed',
                'subcategories' => [],
            ],
        ];

        foreach ($services as $service) {
            if (! isset($grouped[$service->main_category])) {
                continue;
            }

            $subCat = $service->sub_category ?? '_root';

            if (! isset($grouped[$service->main_category]['subcategories'][$subCat])) {
                $grouped[$service->main_category]['subcategories'][$subCat] = [
                    'name' => $service->sub_category ?? 'General',
                    'items' => [],
                ];
            }

            $grouped[$service->main_category]['subcategories'][$subCat]['items'][] = [
                'id' => $service->id,
                'code' => $service->code,
                'name' => $service->name,
                'description' => $service->description,
                'price' => $service->price,
                'icon' => $service->icon,
                'selection_mode' => $service->selection_mode,
            ];
        }

        return response()->json(['data' => $grouped]);
    }

    public function show(Service $service): ServiceResource
    {
        return new ServiceResource($service);
    }

    public function categories(): JsonResponse
    {
        $categories = Service::query()
            ->active()
            ->whereNotNull('main_category')
            ->select('main_category')
            ->distinct()
            ->orderBy('main_category')
            ->pluck('main_category');

        return response()->json(['data' => $categories]);
    }
}
