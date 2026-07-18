<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\Tax;
use App\Models\Category;
use Illuminate\Http\Request;

class SalesInvoiceController extends Controller
{
    /**
     * Show the form for editing the specified sales invoice.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $invoice = Sale::with(['customer', 'saleItems.product', 'saleItems.warehouse'])->findOrFail($id);
        
        return view('app', [
            'invoice' => $invoice,
            'customers' => Customer::all(),
            'warehouses' => Warehouse::all(),
            'categories' => Category::all(),
            'taxes' => Tax::all(),
        ]);
    }
}
