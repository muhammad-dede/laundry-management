<?php

namespace App\Http\Controllers;

use App\Enums\DeliveryStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\PaymentTypeEnum;
use App\Http\Requests\Order\StoreFromPickupRequest;
use App\Http\Requests\Order\StoreRequest;
use App\Http\Requests\Order\UpdateRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderPickup;
use App\Models\Service;
use App\Services\FonnteService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OrderController extends Controller
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

        $order =  DB::transaction(function () use ($validated) {
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
            $deliveryFee = $validated['delivery_required'] ? $validated['delivery_fee'] : 0;
            $grandTotal = max(0, $totalAmount + $deliveryFee - $discount);

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
                'delivery_required' => $validated['delivery_required'],
                'total_amount' => $totalAmount,
                'pickup_fee' => 0,
                'delivery_fee' => $deliveryFee,
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
                $order->payment()->create([
                    'payment_date' => now(),
                    'amount' => $grandTotal,
                    'payment_method' => $validated['payment_method'],
                    'notes' => 'Bayar Lunas',
                    'created_by' => Auth::id(),
                ]);
            }

            if ($validated['delivery_required']) {
                $order->orderDelivery()->create([
                    'customer_id' => $order->customer_id,
                    'courier_id' => null,
                    'scheduled_at' => $order->estimated_finish_date,
                    'delivery_status' => DeliveryStatusEnum::PENDING,
                    'notes' => null,
                ]);
            }

            return $order;
        });

        // Send Whatsapp
        $order->load('customer');
        $message = implode("\n", [
            "Halo {$order->customer->name}",
            "",
            "Pesanan laundry Anda berhasil dibuat.",
            "",
            "Invoice : {$order->invoice_number}",
            "Total : Rp " . number_format($order->grand_total, 0, ',', '.'),
            "Estimasi selesai : {$order->estimated_finish_date->format('d/m/Y')}",
            "",
            "Terima kasih.",
        ]);
        $this->fonnte->send(
            $order->customer->phone,
            $message
        );

        return redirect()->route('order.show', $order->id)->with('success', 'Pesanan berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $order = Order::with(['customer', 'createdBy', 'orderDetails', 'orderDetails.service', 'orderStatusHistories', 'payment', 'orderPickup', 'orderDelivery'])->findOrFail($id);
        return Inertia::render('order/Show', [
            'order' => $order,
            'paymentMethodOptions' => PaymentMethodEnum::options(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $order = Order::with(['customer', 'createdBy', 'orderDetails', 'orderDetails.service', 'orderStatusHistories', 'payment', 'orderPickup', 'orderDelivery'])->findOrFail($id);

        if ($order->order_status !== OrderStatusEnum::QUEUED) {
            return redirect()->route('order.show', $order->id)->with('error', 'Pesanan tidak dapat diedit.');
        }

        $services = Service::query()->where('is_active', true)->get();

        return Inertia::render('order/Edit', [
            'order' => $order,
            'services' => $services,
            'paymentTypeOptions' => PaymentTypeEnum::options(),
            'paymentMethodOptions' => PaymentMethodEnum::options(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, string $id)
    {
        $order = Order::with(['orderPickup', 'orderDelivery'])->findOrFail($id);

        if ($order->order_status !== OrderStatusEnum::QUEUED) {
            return back()->with('error', 'Pesanan tidak dapat diedit.');
        }

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $order) {

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

            $pickupFee = $validated['pickup_fee'] ?? 0;
            $deliveryFee = $validated['delivery_required'] ? $validated['delivery_fee'] : 0;
            $discount = $validated['discount'] ?? 0;
            $grandTotal = max(0, $totalAmount + $pickupFee + $deliveryFee - $discount);

            if ($validated['payment_type'] === PaymentTypeEnum::FULL_PAYMENT->value) {
                $paymentType = PaymentTypeEnum::FULL_PAYMENT;
                $paymentStatus = PaymentStatusEnum::PAID;
            } else {
                $paymentType = PaymentTypeEnum::PAY_LATER;
                $paymentStatus = PaymentStatusEnum::UNPAID;
            }

            $estimatedFinishDate = now()->addDays($maxEstimatedDays);

            $order->update([
                'customer_id' => $customer->id,
                'estimated_finish_date' => $estimatedFinishDate,
                'delivery_required' => $validated['delivery_required'],
                'total_amount' => $totalAmount,
                'pickup_fee' => $pickupFee,
                'delivery_fee' => $deliveryFee,
                'discount' => $discount,
                'grand_total' => $grandTotal,
                'payment_type' => $paymentType,
                'payment_status' => $paymentStatus,
                'notes' => $validated['notes'] ?? null,
            ]);

            $order->orderDetails()->delete();

            foreach ($details as $detail) {
                $order->orderDetails()->create($detail);
            }

            if ($paymentType === PaymentTypeEnum::FULL_PAYMENT) {
                $order->payment()->delete();
                $order->payment()->create([
                    'payment_date' => now(),
                    'amount' => $grandTotal,
                    'payment_method' => $validated['payment_method'],
                    'notes' => 'Bayar Lunas',
                    'created_by' => Auth::id(),
                ]);
            } else {
                $order->payment()->delete();
            }

            if ($validated['delivery_required']) {
                $order->orderDelivery()->updateOrCreate(
                    [
                        'order_id' => $order->id,
                    ],
                    [
                        'customer_id' => $order->customer_id,
                        'courier_id' => null,
                        'scheduled_at' => $estimatedFinishDate,
                        'delivery_status' => DeliveryStatusEnum::PENDING->value,
                        'notes' => null,
                    ]
                );
            } else {
                $order->orderDelivery()->delete();
            }
        });

        return redirect()->route('order.show', $order->id)->with('success', 'Pesanan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::transaction(function () use ($id) {
            $service = Order::findOrFail($id);
            $service->delete();
        });

        return redirect()->route('order.index')->with('success', 'Pesanan berhasil dihapus.');
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

    public function updateStatus(string $id)
    {
        $order = Order::with('customer')->findOrFail($id);

        if ($order->order_status === OrderStatusEnum::COMPLETED) {
            return back()->with('error', 'Pesanan sudah selesai.');
        }

        $newStatus = match ($order->order_status) {
            OrderStatusEnum::QUEUED => OrderStatusEnum::PROCESS,
            OrderStatusEnum::PROCESS => OrderStatusEnum::READY,
            OrderStatusEnum::READY => OrderStatusEnum::COMPLETED,
            default => null,
        };

        if ($newStatus === OrderStatusEnum::COMPLETED && $order->payment_status === PaymentStatusEnum::UNPAID) {
            return back()->with('error', 'Lakukan pembayaran sebelum menyelesaikan pesanan.');
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

        // Send Whatsapp
        if ($newStatus === OrderStatusEnum::READY) {
            $message = implode("\n", [
                "Halo {$order->customer->name}",
                "",
                "Laundry Anda sudah selesai dan siap diambil.",
                "",
                "Invoice : {$order->invoice_number}",
                "Total : Rp " . number_format($order->grand_total, 0, ',', '.'),
                "",
                "Silakan datang ke outlet untuk mengambil pesanan.",
                "",
                "Terima kasih.",
            ]);
            $this->fonnte->send(
                $order->customer->phone,
                $message
            );
        }

        return back()->with('success', 'Status berhasil diubah menjadi ' . $newStatus->label());
    }

    public function payment(Request $request, string $id)
    {
        $order = Order::findOrFail($id);

        if ($order->payment_status === PaymentStatusEnum::PAID) {
            return back()->with('error', 'Pembayaran sudah dilakukan.');
        }

        DB::transaction(function () use ($order, $request) {
            $order->update([
                'payment_status' => PaymentStatusEnum::PAID,
            ]);
            $order->payment()->create([
                'payment_date' => Carbon::now(),
                'amount' => $order->grand_total ?? 0,
                'payment_method' => $request->payment_method,
                'notes' => 'Bayar Nanti Lunas',
                'created_by' => Auth::id(),
            ]);
        });
        return redirect()->back()->with('success', 'Pembayaran berhasil disimpan.');
    }

    public function print(string $id)
    {
        $order = Order::with(['customer', 'createdBy', 'orderDetails', 'orderDetails.service', 'orderStatusHistories', 'payment'])->findOrFail($id);
        return Inertia::render('order/Print', [
            'order' => $order,
        ]);
    }

    public function createFromPickup(string $order_pickup_id)
    {
        $orderPickup = OrderPickup::with(['customer', 'courier'])->findOrFail($order_pickup_id);
        $services = Service::query()->where('is_active', true)->get();
        return Inertia::render('order/CreateFromPickup', [
            'orderPickup' => $orderPickup,
            'services' => $services,
            'paymentTypeOptions' => PaymentTypeEnum::options(),
            'paymentMethodOptions' => PaymentMethodEnum::options(),
        ]);
    }

    public function storeFromPickup(StoreFromPickupRequest $request, string $order_pickup_id)
    {
        $orderPickup = OrderPickup::with(['customer', 'courier'])->findOrFail($order_pickup_id);

        $validated = $request->validated();

        $order =  DB::transaction(function () use ($validated, $orderPickup) {
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
            $deliveryFee = $validated['delivery_required'] ? $validated['delivery_fee'] : 0;
            $pickupFee = $validated['pickup_fee'] ?? 0;
            $discount = $validated['discount'] ?? 0;
            $grandTotal = max(0, $totalAmount + $pickupFee + $deliveryFee - $discount);
            $paymentType = PaymentTypeEnum::PAY_LATER;
            $paymentStatus = PaymentStatusEnum::UNPAID;
            $orderDate = Carbon::now();
            $estimatedFinishDate = $orderDate->copy()->addDays($maxEstimatedDays);
            $orderStatus = OrderStatusEnum::QUEUED;
            $order = Order::create([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $orderPickup->customer_id,
                'order_date' => $orderDate,
                'estimated_finish_date' => $estimatedFinishDate,
                'delivery_required' => $validated['delivery_required'],
                'total_amount' => $totalAmount,
                'pickup_fee' => $pickupFee,
                'delivery_fee' => $deliveryFee,
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
            $orderPickup->update([
                'order_id' => $order->id,
            ]);
            if ($validated['delivery_required']) {
                $order->orderDelivery()->create([
                    'customer_id' => $order->customer_id,
                    'courier_id' => null,
                    'scheduled_at' => $order->estimated_finish_date,
                    'delivery_status' => DeliveryStatusEnum::PENDING,
                    'notes' => null,
                ]);
            }
            return $order;
        });

        // Send Whatsapp
        $order->load('customer');
        $message = implode("\n", [
            "Halo {$order->customer->name}",
            "",
            "Pesanan laundry Anda berhasil dibuat.",
            "",
            "Invoice : {$order->invoice_number}",
            "Total : Rp " . number_format($order->grand_total, 0, ',', '.'),
            "Estimasi selesai : {$order->estimated_finish_date->format('d/m/Y')}",
            "",
            "Terima kasih.",
        ]);
        $this->fonnte->send(
            $order->customer->phone,
            $message
        );

        return redirect()->route('order.show', $order->id)->with('success', 'Pesanan berhasil ditambahkan.');
    }
}
