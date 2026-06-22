<?php

namespace App\Http\Controllers;

use App\Enums\PickupStatusEnum;
use App\Models\Pickup;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $per_page = $filters['per_page'] ?? 10;

        $data = Pickup::query()
            ->with(['order', 'courier', 'customer'])
            ->when(!auth()->user()->hasRole('Super Admin'), function ($query) {
                $query->where('courier_id', Auth::id());
            })
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
        $pickup = Pickup::query()
            ->with(['order', 'courier', 'customer'])
            ->when(!auth()->user()->hasRole('Super Admin'), function ($query) {
                $query->where('courier_id', Auth::id());
            })
            ->whereKey($id)
            ->firstOrFail();
        return Inertia::render('pickup-task/Show', [
            'pickup' => $pickup,
        ]);
    }

    public function updateStatus(string $id)
    {
        $pickup = Pickup::query()
            ->with('customer')
            ->when(!auth()->user()->hasRole('Super Admin'), function ($query) {
                $query->where('courier_id', Auth::id());
            })
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
            return back()->with('error', 'Status tidak valid untuk diupdate.');
        }

        $pickup->update([
            'pickup_status' => $newStatus,
        ]);

        // ======================
        // WA MESSAGE
        // ======================
        $customerName = $pickup->customer?->name ?? 'Pelanggan';

        $message = match ($newStatus) {
            PickupStatusEnum::ON_THE_WAY => implode("\n", [
                "Halo {$customerName}",
                "",
                "Kurir sedang dalam perjalanan menuju lokasi Anda.",
                "Mohon pastikan berada di tempat saat kurir tiba.",
                "",
                "Terima kasih.",
            ]),

            PickupStatusEnum::PICKED_UP => implode("\n", [
                "Halo {$customerName}",
                "",
                "Laundry Anda telah berhasil dijemput oleh kurir.",
                "Pesanan sedang diproses di outlet kami.",
                "",
                "Terima kasih telah menggunakan layanan kami.",
            ]),

            PickupStatusEnum::RECEIVED => implode("\n", [
                "Halo {$customerName}",
                "",
                "Pesanan Anda sudah diterima di outlet laundry.",
                "Kami akan segera memproses cucian Anda.",
                "",
                "Terima kasih.",
            ]),

            default => null,
        };

        // kirim WA hanya jika ada message
        if (!empty($message)) {
            $this->fonnte->send(
                $pickup->customer->phone,
                $message
            );
        }

        return back()->with('success', 'Status berhasil diubah menjadi ' . $newStatus->label());
    }
}
