<?php

namespace App\Http\Controllers;

use App\Enums\PickupStatusEnum;
use App\Models\OrderPickup;
use App\Services\FonnteService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PickupTaskController extends Controller
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
            ->when(
                !auth()->user()->hasRole('Super Admin'),
                fn($query) => $query->where('courier_id', auth()->id())
            )
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

        return Inertia::render('pickup-task/Index', [
            'filters' => $filters,
            'data' => $data,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $pickup = OrderPickup::query()
            ->with(['customer', 'courier'])
            ->when(
                !auth()->user()->hasRole('Super Admin'),
                fn($query) => $query->where('courier_id', auth()->id())
            )
            ->whereKey($id)
            ->firstOrFail();

        return Inertia::render('pickup-task/Show', [
            'pickup' => $pickup,
        ]);
    }

    public function updateStatus(string $id)
    {
        $pickup = OrderPickup::query()
            ->with(['customer', 'courier'])
            ->when(
                auth()->user()->hasRole('Courier'),
                fn($query) => $query->where('courier_id', auth()->id())
            )
            ->whereKey($id)
            ->firstOrFail();

        if ($pickup->pickup_status === PickupStatusEnum::RECEIVED) {
            return back()->with('error', 'Pengambilan sudah selesai.');
        }

        $newStatus = match ($pickup->pickup_status) {
            PickupStatusEnum::ASSIGNED => PickupStatusEnum::ON_THE_WAY,
            PickupStatusEnum::ON_THE_WAY => PickupStatusEnum::PICKED_UP,
            PickupStatusEnum::PICKED_UP => PickupStatusEnum::RECEIVED,
            default => null,
        };

        if (!$newStatus) {
            return back()->with('error', 'Status pickup tidak dapat diubah.');
        }

        $pickupAt = $pickup->pickup_at;
        if ($newStatus === PickupStatusEnum::PICKED_UP) {
            $pickupAt = Carbon::now();
        }

        DB::transaction(function () use ($pickup, $newStatus, $pickupAt) {
            $pickup->update([
                'pickup_status' => $newStatus,
                'pickup_at' => $pickupAt,
            ]);
        });

        try {
            $message = match ($newStatus) {
                PickupStatusEnum::ON_THE_WAY => implode("\n", [
                    "Halo {$pickup->customer->name},",
                    "",
                    "Kurir sedang menuju lokasi untuk menjemput laundry Anda.",
                    "",
                    "Kurir: {$pickup->courier?->name}",
                    "",
                    "Terima kasih."
                ]),

                PickupStatusEnum::PICKED_UP => implode("\n", [
                    "Halo {$pickup->customer->name},",
                    "",
                    "Laundry Anda telah berhasil dijemput oleh kurir dan sedang dalam perjalanan ke outlet.",
                    "",
                    "Terima kasih."
                ]),

                PickupStatusEnum::RECEIVED => implode("\n", [
                    "Halo {$pickup->customer->name},",
                    "",
                    "Laundry Anda telah diterima oleh outlet dan akan segera diproses.",
                    "",
                    "Terima kasih."
                ]),

                default => null,
            };

            if ($message) {
                $this->fonnte->send(
                    $pickup->customer->phone,
                    $message
                );
            }
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim notifikasi pickup', [
                'pickup_id' => $pickup->id,
                'error' => $e->getMessage(),
            ]);
        }

        return back()->with('success', "Status pengambilan berhasil diubah menjadi {$newStatus->label()}.");
    }
}
