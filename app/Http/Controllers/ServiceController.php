<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Enums\UnitTypeEnum;
use App\Http\Requests\Service\StoreRequest;
use App\Http\Requests\Service\UpdateRequest;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'per_page']);
        $search = $filters['search'] ?? null;
        $per_page = $filters['per_page'] ?? 5;

        $data = Service::query()
            ->when(!empty($search), function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($per_page)
            ->withQueryString();

        return Inertia::render('service/Index', [
            'filters' => $filters,
            'data' => $data,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('service/Create', [
            'unitTypeOptions' => UnitTypeEnum::options(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            Service::create([
                'name' => $validated['name'],
                'unit_type' => $validated['unit_type'],
                'price' => $validated['price'],
                'estimated_days' => $validated['estimated_days'],
                'is_active' => $validated['is_active'],
            ]);
        });

        return redirect()->route('service.index')->with('success', 'Layanan berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $service = Service::findOrFail($id);
        return Inertia::render('service/Edit', [
            'unitTypeOptions' => UnitTypeEnum::options(),
            'service' => $service,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, string $id)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $id) {
            $service = Service::findOrFail($id);
            $service->update([
                'name' => $validated['name'],
                'unit_type' => $validated['unit_type'],
                'price' => $validated['price'],
                'estimated_days' => $validated['estimated_days'],
                'is_active' => $validated['is_active'],
            ]);
        });

        return redirect()->route('service.index')->with('success', 'Layanan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::transaction(function () use ($id) {
            $service = Service::findOrFail($id);
            $service->delete();
        });

        return redirect()->route('service.index')->with('success', 'Layanan berhasil dihapus.');
    }
}
