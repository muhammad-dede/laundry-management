<?php

namespace App\Http\Controllers;

use App\Enums\PickupStatusEnum;
use App\Http\Requests\OrderPickup\StoreRequest;
use App\Http\Requests\OrderPickup\UpdateRequest;
use App\Models\Customer;
use App\Models\OrderPickup;
use App\Models\User;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OrderPickupController extends Controller
{
    public function __construct(
        protected FonnteService $fonnte
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'per_page']);
        $search = $filters['search'] ?? null;
        $per_page = $filters['per_page'] ?? 5;

        $data = OrderPickup::query()
            ->with(['order', 'customer', 'courier'])
            ->when(!empty($search), function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->whereHas('customer', function ($customer) use ($search) {
                        $customer->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })->whereHas('courier', function ($courier) use ($search) {
                        $courier->where('name', 'like', "%{$search}%");
                    });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($per_page)
            ->withQueryString();

        return Inertia::render('order-pickup/Index', [
            'filters' => $filters,
            'data' => $data,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $couriers = User::role('Courier')->get();
        return Inertia::render('order-pickup/Create', [
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

            OrderPickup::create([
                'order_id' => null,
                'customer_id' => $customer->id,
                'courier_id' => $validated['courier_id'],
                'scheduled_at' => $validated['scheduled_at'],
                'pickup_at' => null,
                'notes' => $validated['notes'] ?? null,
                'pickup_status' => PickupStatusEnum::ASSIGNED,
            ]);
        });

        return redirect()->route('order-pickup.index')->with('success', 'Pickup Request berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $orderPickup = OrderPickup::with(['customer', 'courier'])->findOrFail($id);
        return Inertia::render('order-pickup/Show', [
            'orderPickup' => $orderPickup,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $orderPickup = OrderPickup::with(['customer', 'courier'])->findOrFail($id);
        if ($orderPickup->pickup_status !== PickupStatusEnum::ASSIGNED) {
            return back()->with('error', 'Pickup request tidak dapat diedit.');
        }
        $couriers = User::role('Courier')->get();
        return Inertia::render('order-pickup/Edit', [
            'orderPickup' => $orderPickup,
            'couriers' => $couriers,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, string $id)
    {
        $orderPickup = OrderPickup::findOrFail($id);

        if ($orderPickup->pickup_status !== PickupStatusEnum::ASSIGNED) {
            return back()->with('error', 'Pickup request tidak dapat diedit.');
        }

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $orderPickup) {
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

            $orderPickup->update([
                'customer_id' => $customer->id,
                'courier_id' => $validated['courier_id'],
                'scheduled_at' => $validated['scheduled_at'],
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()->route('order-pickup.index')->with('success', 'Pickup Request berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::transaction(function () use ($id) {
            $orderPickup = OrderPickup::findOrFail($id);
            $orderPickup->delete();
        });

        return redirect()->route('order-pickup.index')->with('success', 'Pickup request berhasil dihapus.');
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
