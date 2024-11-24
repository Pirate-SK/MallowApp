<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\BillDenomination;
use App\Models\ProductDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;

class BillingController extends Controller
{
    public function create()
    {
        return view('form');
    }

    public function store(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'customer_email' => 'required|email',
                'product_id' => 'required|array',
                'product_id.*' => 'required|string',
                'quantity' => 'required|array',
                'quantity.*' => 'required|integer|min:1',
                'denomination' => 'required|array',
                'denomination.*' => 'nullable|integer|min:0',
                'amount_paid' => 'required|numeric|min:0',
            ]);

            // Validate denomination total
            $denominationTotal = 0;
            foreach ($request->denomination as $value => $count) {
                if (!empty($count)) {
                    $denominationTotal += $value * $count;
                }
            }
            if ($denominationTotal != $request->amount_paid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Total denomination amount must match the amount paid'
                ], 200);
            }

            DB::beginTransaction();

            // Create bill
            $bill = Bill::create([
                'customer_email' => $request->customer_email,
                'amount_paid' => $request->amount_paid,
            ]);

            // Create bill items
            foreach ($request->product_id as $index => $productId) {
                $product = ProductDetail::where('product_id', $productId)->first();
                BillItem::create([
                    'bill_id' => $bill->id,
                    'product_id' => $productId,
                    'quantity' => $request->quantity[$index],
                    'unit_price' => $product ? $product->price : 0.00, 
                    'tax_percentage' => $product ? $product->tax_percentage : 0.00, 
                ]);
            }

            // Create denominations
            foreach ($request->denomination as $value => $count) {
                if (!empty($count)) {
                    BillDenomination::create([
                        'bill_id' => $bill->id,
                        'denomination' => $value,
                        'count' => $count,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bill generated successfully',
                'redirect_url' => route('billing.show', $bill->id)
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate bill',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Bill $bill)
    {
        
        $bill->load(['items', 'denominations']);
       
        $totalPriceWithoutTax = $bill->items->sum(function($item) {
            return $item->unit_price * $item->quantity;
        });

        
        $totalTaxPayable = $bill->items->sum(function($item) {
            return ($item->unit_price * $item->quantity * $item->tax_percentage) / 100;
        });
        
        $netPrice = $totalPriceWithoutTax + $totalTaxPayable;
        $roundedNetPrice = floor($netPrice);
        $balancePayable = $bill->amount_paid - $roundedNetPrice;
        $amount_paid = $bill->amount_paid;
        return view('billing.show', compact(
            'bill',
            'totalPriceWithoutTax',
            'totalTaxPayable',
            'netPrice',
            'roundedNetPrice',
            'balancePayable',
            'amount_paid'
        ));
    }

    public function download(Bill $bill)
    {
        $bill->load(['items', 'denominations']);
        
        $totalPriceWithoutTax = $bill->items->sum(function($item) {
            return $item->unit_price * $item->quantity;
        });
        
        $totalTaxPayable = $bill->items->sum(function($item) {
            return ($item->unit_price * $item->quantity * $item->tax_percentage) / 100;
        });
        
        $netPrice = $totalPriceWithoutTax + $totalTaxPayable;
        $roundedNetPrice = floor($netPrice);
        $balancePayable = $bill->amount_paid - $roundedNetPrice;
        $amount_paid = $bill->amount_paid;

        $pdf = PDF::loadView('billing.show', compact(
            'bill',
            'totalPriceWithoutTax',
            'totalTaxPayable',
            'netPrice',
            'roundedNetPrice',
            'balancePayable',
            'amount_paid'
        ));

        return $pdf->download('bill-' . $bill->id . '.pdf');
    }
}