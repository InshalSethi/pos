<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SyncQueue;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Payment;
use App\Models\InventoryAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class SyncController extends Controller
{
    /**
     * Get health & sync status check endpoint
     */
    public function ping()
    {
        return response()->json([
            'status' => 'online',
            'server_time' => now()->toIso8601String(),
            'version' => '1.0.0-electron',
        ]);
    }

    /**
     * Push offline queued items from local terminal to Central Server
     */
    public function push(Request $request)
    {
        $request->validate([
            'terminal_id' => 'required|string',
            'items' => 'required|array',
            'items.*.id' => 'required|string',
            'items.*.entity_type' => 'required|string',
            'items.*.action' => 'required|string',
            'items.*.payload' => 'required|array',
        ]);

        $items = $request->input('items');
        $terminalId = $request->input('terminal_id');
        $companyId = auth()->user()->current_company_id ?? 1;

        $processedIds = [];
        $failedItems = [];

        foreach ($items as $item) {
            DB::beginTransaction();
            try {
                $entityType = $item['entity_type'];
                $action = $item['action'];
                $payload = $item['payload'];

                // Enforce tenant ID & terminal metadata
                $payload['company_id'] = $companyId;
                $payload['terminal_id'] = $terminalId;
                $payload['sync_status'] = 'synced';
                $payload['synced_at'] = now();

                switch ($entityType) {
                    case 'Sale':
                        $saleData = $payload;
                        $itemsData = $saleData['items'] ?? [];
                        unset($saleData['items']);

                        $sale = Sale::updateOrCreate(['id' => $saleData['id']], $saleData);
                        
                        // Sync items
                        if (!empty($itemsData)) {
                            SaleItem::where('sale_id', $sale->id)->delete();
                            foreach ($itemsData as $sItem) {
                                $sItem['sale_id'] = $sale->id;
                                $sItem['company_id'] = $companyId;
                                SaleItem::create($sItem);
                            }
                        }
                        break;

                    case 'Customer':
                        Customer::updateOrCreate(['id' => $payload['id']], $payload);
                        break;

                    case 'Payment':
                        Payment::updateOrCreate(['id' => $payload['id']], $payload);
                        break;

                    case 'InventoryAdjustment':
                        InventoryAdjustment::updateOrCreate(['id' => $payload['id']], $payload);
                        break;

                    default:
                        Log::warning("Unknown entity type during sync push: {$entityType}");
                        break;
                }

                DB::commit();
                $processedIds[] = $item['id'];
            } catch (Exception $e) {
                DB::rollBack();
                Log::error("Sync Push Error for Item {$item['id']}: " . $e->getMessage());
                $failedItems[] = [
                    'id' => $item['id'],
                    'error' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'success' => true,
            'processed_ids' => $processedIds,
            'failed_items' => $failedItems,
            'synced_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Pull updated master data from Central Server down to local desktop app
     */
    public function pull(Request $request)
    {
        $since = $request->query('since');
        $companyId = auth()->user()->current_company_id ?? 1;

        $productsQuery = Product::where('company_id', $companyId)->with(['variations', 'unit', 'tax', 'category', 'brand']);
        $categoriesQuery = Category::where('company_id', $companyId);
        $brandsQuery = Brand::where('company_id', $companyId);
        $taxesQuery = Tax::where('company_id', $companyId);
        $unitsQuery = Unit::where('company_id', $companyId);

        if ($since) {
            $productsQuery->where('updated_at', '>=', $since);
            $categoriesQuery->where('updated_at', '>=', $since);
            $brandsQuery->where('updated_at', '>=', $since);
            $taxesQuery->where('updated_at', '>=', $since);
            $unitsQuery->where('updated_at', '>=', $since);
        }

        return response()->json([
            'products' => $productsQuery->get(),
            'categories' => $categoriesQuery->get(),
            'brands' => $brandsQuery->get(),
            'taxes' => $taxesQuery->get(),
            'units' => $unitsQuery->get(),
            'server_timestamp' => now()->toIso8601String(),
        ]);
    }
}
