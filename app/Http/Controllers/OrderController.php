<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\PaymentTypeEnum;
use App\Http\Requests\Order\StoreRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'per_page']);
        $search = $filters['search'] ?? null;
        $per_page = $filters['per_page'] ?? 5;

        $data = Order::query()
            ->with(['customer', 'createdBy'])
            ->when(!empty($search), function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('invoice_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($customer) use ($search) {
                            $customer->where('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($per_page)
            ->withQueryString();

        return Inertia::render('order/Index', [
            'filters' => $filters,
            'data' => $data,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $services = Service::query()->where('is_active', true)->get();
        return Inertia::render('order/Create', [
            'services' => $services,
            'paymentTypeOptions' => PaymentTypeEnum::options(),
            'paymentMethodOptions' => PaymentMethodEnum::options(),
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

            $invoiceNumber = 'INV-' . time();
            $totalAmount = 0;
            $maxEstimatedDays = 0;
            $details = [];
            foreach ($validated['order_detail'] as $item) {
                $service = Service::query()->where('is_active', true)->findOrFail($item['service_id']);
                $price = $service->price;
                $subtotal = $price * $item['quantity'];
                $totalAmount += $subtotal;
                $maxEstimatedDays = max(
                    $maxEstimatedDays,
                    $service->estimated_days
                );
                $details[] = [
                    'service_id' => $service->id,
                    'quantity' => $item['quantity'],
                    'price' => $price,
                    'subtotal' => $subtotal,
                ];
            }
            $discount = $validated['discount'] ?? 0;
            $grandTotal = max(0, $totalAmount - $discount);
            if ($validated['payment_type'] === PaymentTypeEnum::FULL_PAYMENT->value) {
                $paymentType = PaymentTypeEnum::FULL_PAYMENT;
                $paymentStatus = PaymentStatusEnum::PAID;
            } else {
                $paymentType = PaymentTypeEnum::PAY_LATER;
                $paymentStatus = PaymentStatusEnum::UNPAID;
            }

            $orderDate = Carbon::now();
            $estimatedFinishDate = $orderDate->copy()->addDays($maxEstimatedDays);
            $orderStatus = OrderStatusEnum::QUEUED;

            $order = Order::create([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $customer->id,
                'order_date' => $orderDate,
                'estimated_finish_date' => $estimatedFinishDate,
                'total_amount' => $totalAmount,
                'discount' => $discount,
                'grand_total' => $grandTotal,
                'payment_type' => $paymentType,
                'payment_status' => $paymentStatus,
                'order_status' => $orderStatus,
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            foreach ($details as $detail) {
                $order->orderDetails()->create($detail);
            }

            $order->orderStatusHistories()->create([
                'status' => $orderStatus,
                'notes' => 'Dalam Antrian',
                'created_by' => Auth::id(),
            ]);

            if ($paymentType === PaymentTypeEnum::FULL_PAYMENT) {
                $order->payments()->create([
                    'payment_date' => now(),
                    'amount' => $grandTotal,
                    'payment_method' => $validated['payment_method'],
                    'notes' => 'Bayar Lunas',
                    'created_by' => Auth::id(),
                ]);
            }
        });

        return redirect()->route('order.index')->with('success', 'Transaksi berhasil ditambahkan.');
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
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

    public function orderStatusUpdate(Request $request, string $id)
    {
        $order = Order::findOrFail($id);

        if ($order->order_status === OrderStatusEnum::COMPLETED) {
            return back()->with('error', 'Pesanan sudah selesai.');
        }

        $newStatus = match ($order->order_status) {
            OrderStatusEnum::QUEUED => OrderStatusEnum::PROCESS,
            OrderStatusEnum::PROCESS => OrderStatusEnum::READY,
            default => null,
        };

        if (!$newStatus) {
            return back()->with('error', 'Pesanan belum bisa diselesaikan karena pembayaran belum lunas.');
        }

        DB::transaction(function () use ($order, $newStatus) {
            $order->update([
                'order_status' => $newStatus,
            ]);

            $order->orderStatusHistories()->create([
                'status' => $newStatus,
                'notes' => 'Status diubah menjadi ' . $newStatus->label(),
                'created_by' => Auth::id(),
            ]);
        });

        return back()->with('success', 'Status berhasil diubah menjadi ' . $newStatus->label());
    }
}
