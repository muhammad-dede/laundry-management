<?php

namespace App\Http\Controllers;

use App\Enums\PickupStatusEnum;
use App\Http\Requests\Pickup\StoreRequest;
use App\Http\Requests\Pickup\UpdateRequest;
use App\Models\Customer;
use App\Models\Pickup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PickupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'per_page']);
        $search = $filters['search'] ?? null;
        $per_page = $filters['per_page'] ?? 5;

        $data = Pickup::query()
            ->with(['order', 'courier', 'customer'])
            ->when(!empty($search), function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('pickup_number', 'like', "%{$search}%")
                        ->orWhereHas('courier', function ($customer) use ($search) {
                            $customer->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('customer', function ($customer) use ($search) {
                            $customer->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($per_page)
            ->withQueryString();

        return Inertia::render('pickup/Index', [
            'filters' => $filters,
            'data' => $data,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $couriers = User::query()->role('courier')->select('id', 'name')->orderBy('name')->get();
        return Inertia::render('pickup/Create', [
            'couriers' => $couriers,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            // Customer
            $customer = Customer::firstOrCreate(
                [
                    'phone' => $validated['customer_phone'],
                ],
                [
                    'code' => 'CUST-' . time(),
                    'name' => strtoupper($validated['customer_name']),
                    'address' => $validated['customer_address'] ?? null,
                ]
            );
            $customer->update([
                'name' => strtoupper($validated['customer_name']),
                'address' => $validated['customer_address'] ?? null,
            ]);

            $pickupNumber = 'PR-' . time();

            Pickup::create([
                'pickup_number' => $pickupNumber,
                'customer_id' => $customer->id,
                'address' => $customer->address,
                'pickup_at' => $validated['pickup_at'],
                'courier_id' => $validated['courier_id'],
                'notes' => $validated['notes'] ?? null,
                'pickup_status' => PickupStatusEnum::ASSIGNED,
            ]);
        });

        return redirect()->route('pickup.index')->with('success', 'Pengambilan berhasil ditambahkan.');
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
        $pickup = Pickup::with(['order', 'courier', 'customer'])->findOrFail($id);

        if ($pickup->pickup_status !== PickupStatusEnum::ASSIGNED) {
            return redirect()->route('pickup.index')->with('error', 'Pengambilan tidak dapat diedit.');
        }

        $couriers = User::query()->role('courier')->select('id', 'name')->orderBy('name')->get();

        return Inertia::render('pickup/Edit', [
            'pickup' => $pickup,
            'couriers' => $couriers,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, string $id)
    {
        $pickup = Pickup::with(['order', 'courier', 'customer'])->findOrFail($id);

        if ($pickup->pickup_status !== PickupStatusEnum::ASSIGNED) {
            return redirect()->route('pickup.index')->with('error', 'Pengambilan tidak dapat diedit.');
        }

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $pickup) {
            // Customer
            $customer = Customer::firstOrCreate(
                [
                    'phone' => $validated['customer_phone'],
                ],
                [
                    'code' => 'CUST-' . time(),
                    'name' => strtoupper($validated['customer_name']),
                    'address' => $validated['customer_address'] ?? null,
                ]
            );
            $customer->update([
                'name' => strtoupper($validated['customer_name']),
                'address' => $validated['customer_address'] ?? null,
            ]);

            $pickup->update([
                'customer_id' => $customer->id,
                'address' => $customer->address,
                'pickup_at' => $validated['pickup_at'],
                'courier_id' => $validated['courier_id'],
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()->route('pickup.index')->with('success', 'Pengambilan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $pickup = Pickup::findOrFail($id);

        if ($pickup->pickup_status !== PickupStatusEnum::ASSIGNED) {
            return redirect()->route('pickup.index')->with('error', 'Pengambilan tidak dapat dihapus.');
        }

        DB::transaction(function () use ($pickup) {
            $pickup->delete();
        });

        return redirect()->route('pickup.index')->with('success', 'Pengambilan berhasil dihapus.');
    }

    public function searchCustomer(Request $request)
    {
        $search = $request->search;
        return Customer::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->limit(10)
            ->get([
                'id',
                'name',
                'phone',
                'address',
            ]);
    }
}
