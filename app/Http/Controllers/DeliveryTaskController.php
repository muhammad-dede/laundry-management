<?php

namespace App\Http\Controllers;

use App\Enums\DeliveryStatusEnum;
use App\Models\OrderDelivery;
use App\Services\FonnteService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class DeliveryTaskController extends Controller
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

        $data = OrderDelivery::query()
            ->with(['order', 'customer', 'courier'])
            ->when(
                !auth()->user()->hasRole('Super Admin'),
                fn($query) => $query->where('courier_id', auth()->id())
            )
            ->when(!empty($search), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->orWhereHas('customer', function ($customer) use ($search) {
                        $customer->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
                    $q->orWhereHas('courier', function ($courier) use ($search) {
                        $courier->where('name', 'like', "%{$search}%");
                    });
                    $q->orWhereHas('order', function ($order) use ($search) {
                        $order->where('invoice_number', 'like', "%{$search}%");
                    });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($per_page)
            ->withQueryString();

        return Inertia::render('delivery-task/Index', [
            'filters' => $filters,
            'data' => $data,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $delivery = OrderDelivery::query()
            ->with(['customer', 'courier'])
            ->when(
                !auth()->user()->hasRole('Super Admin'),
                fn($query) => $query->where('courier_id', auth()->id())
            )
            ->whereKey($id)
            ->firstOrFail();

        return Inertia::render('delivery-task/Show', [
            'delivery' => $delivery,
        ]);
    }

    public function updateStatus(string $id)
    {
        $delivery = OrderDelivery::query()
            ->with(['customer', 'courier', 'order'])
            ->when(
                auth()->user()->hasRole('Courier'),
                fn($query) => $query->where('courier_id', auth()->id())
            )
            ->whereKey($id)
            ->firstOrFail();

        if (!$delivery->courier_id) {
            return back()->with('error', 'Kurir belum ditugaskan.');
        }

        if ($delivery->delivery_status === DeliveryStatusEnum::DELIVERED) {
            return back()->with('error', 'Pengiriman sudah selesai.');
        }

        $newStatus = match ($delivery->delivery_status) {
            DeliveryStatusEnum::ASSIGNED => DeliveryStatusEnum::ON_THE_WAY,
            DeliveryStatusEnum::ON_THE_WAY => DeliveryStatusEnum::DELIVERED,
            default => null,
        };

        if (!$newStatus) {
            return back()->with('error', 'Status tidak dapat diubah.');
        }

        $deliveredAt = $delivery->delivered_at;
        if ($newStatus === DeliveryStatusEnum::DELIVERED) {
            $deliveredAt = Carbon::now();
        }

        DB::transaction(function () use ($delivery, $newStatus, $deliveredAt) {
            $delivery->update([
                'delivery_status' => $newStatus,
                'delivered_at' => $deliveredAt,
            ]);
        });

        try {
            $message = match ($newStatus) {
                DeliveryStatusEnum::ON_THE_WAY => implode("\n", [
                    "Halo {$delivery->customer->name},",
                    "",
                    "Laundry Anda sedang dalam perjalanan.",
                    "",
                    "Kurir: {$delivery->courier?->name}",
                    "",
                    "Terima kasih."
                ]),

                DeliveryStatusEnum::DELIVERED => implode("\n", [
                    "Halo {$delivery->customer->name},",
                    "",
                    "Laundry Anda telah berhasil diantarkan.",
                    "",
                    "Invoice: {$delivery->order?->invoice_number}",
                    "",
                    "Terima kasih telah menggunakan layanan kami."
                ]),

                default => null,
            };

            if ($message) {
                $this->fonnte->send(
                    $delivery->customer->phone,
                    $message
                );
            }
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim notifikasi', [
                'delivery_id' => $delivery->id,
                'error' => $e->getMessage(),
            ]);
        }

        return back()->with('success', "Status berhasil diubah menjadi {$newStatus->label()}.");
    }
}
