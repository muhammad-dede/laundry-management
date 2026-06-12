<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customer\StoreRequest;
use App\Http\Requests\Customer\UpdateRequest;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'per_page']);
        $search = $filters['search'] ?? null;
        $per_page = $filters['per_page'] ?? 5;

        $data = Customer::query()
            ->when(!empty($search), function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($per_page)
            ->withQueryString();

        return Inertia::render('customer/Index', [
            'filters' => $filters,
            'data' => $data,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('customer/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            Customer::create([
                'code' => 'CUST-' . time(),
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
            ]);
        });

        return redirect()->route('customer.index')->with('success', 'Pelanggan berhasil ditambahkan.');
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
        $customer = Customer::findOrFail($id);

        return Inertia::render('customer/Edit', [
            'customer' => $customer,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, string $id)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $id) {
            $customer = Customer::findOrFail($id);
            $customer->update([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
            ]);
        });

        return redirect()->route('customer.index')->with('success', 'Pelanggan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::transaction(function () use ($id) {
            $customer = Customer::findOrFail($id);
            $customer->delete();
        });

        return redirect()->route('customer.index')->with('success', 'Pelanggan berhasil dihapus.');
    }
}
