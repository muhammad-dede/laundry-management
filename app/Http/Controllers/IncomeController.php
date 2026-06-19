<?php

namespace App\Http\Controllers;

use App\Http\Requests\Income\StoreRequest;
use App\Http\Requests\Income\UpdateRequest;
use App\Models\Income;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class IncomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'per_page']);
        $search = $filters['search'] ?? null;
        $per_page = $filters['per_page'] ?? 5;

        $data = Income::query()
            ->when(!empty($search), function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('description', 'like', "%{$search}%")
                        ->orWhere('amount', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($per_page)
            ->withQueryString();

        return Inertia::render('income/Index', [
            'filters' => $filters,
            'data' => $data,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('income/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            Income::create([
                'income_date' => $validated['income_date'],
                'description' => $validated['description'],
                'amount' => $validated['amount'],
                'created_by' => Auth::id(),
            ]);
        });

        return redirect()->route('income.index')->with('success', 'Pemasukan lain berhasil ditambahkan.');
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
        $income = Income::findOrFail($id);
        return Inertia::render('income/Edit', [
            'income' => $income,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, string $id)
    {
        $income = Income::findOrFail($id);

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $income) {
            $income->update([
                'income_date' => $validated['income_date'],
                'description' => $validated['description'],
                'amount' => $validated['amount'],
            ]);
        });

        return redirect()->route('income.index')->with('success', 'Pemasukan lain berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::transaction(function () use ($id) {
            $income = Income::findOrFail($id);
            $income->delete();
        });

        return redirect()->route('income.index')->with('success', 'Pemasukan lain berhasil dihapus.');
    }
}
