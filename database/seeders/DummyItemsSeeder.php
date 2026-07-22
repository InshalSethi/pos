<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Company;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Warehouse;
use App\Models\Tax;
use App\Models\Tag;
use App\Models\Inventory;

class DummyItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Generates 2,000 dummy products (single & variable) across 10+ categories with tags, taxes, and warehouse inventory.
     */
    public function run(): void
    {
        DB::disableQueryLog();
        $this->command->info('🚀 Starting seeding of 2,000 dummy items...');

        // 1. Get or create Company
        $company = Company::first();
        $companyId = $company ? $company->id : 1;

        // 2. Ensure Warehouses exist
        $warehouses = $this->ensureWarehousesExist($companyId);
        $warehouseIds = $warehouses->pluck('id')->toArray();

        // 3. Ensure Taxes exist
        $taxesList = $this->ensureTaxesExist($companyId);

        // 4. Ensure Tags exist
        $tagsList = $this->ensureTagsExist($companyId);

        // 5. Ensure Categories exist (At least 12 categories)
        $categories = $this->ensureCategoriesExist($companyId);
        $categoryIds = $categories->pluck('id')->toArray();

        // 6. Category Name Templates for realistic item generation
        $productCatalogTemplates = [
            'Electronics & Gadgets' => [
                'Wireless Noise-Canceling Headphones', 'Smart Watch Ultra', 'Bluetooth Speaker Max',
                'USB-C Fast Charger 65W', 'Ultra HD Action Camera 4K', 'Wireless Mechanical Keyboard',
                'Ergonomic Gaming Mouse', '4K Portable Monitor 15.6"', 'Power Bank 20000mAh',
                'Smart LED Desk Lamp', 'True Wireless Earbuds Pro', 'Portable Mini Projector',
                'High-Speed Wi-Fi 6 Router', 'External SSD 1TB Portable', 'MagSafe Wireless Charger'
            ],
            'Clothing & Apparel' => [
                'Classic Heavyweight Cotton T-Shirt', 'Slim Fit Stretch Denim Jeans', 'Waterproof Outdoor Windbreaker',
                'Fleece Zip Hoodie Premium', 'Casual Pique Polo Shirt', 'Athletic Performance Joggers',
                'Winter Puffer Coat Insulation', 'Formal Oxford Button-Up Shirt', 'Lightweight Running Shorts',
                'Fleece Crewneck Sweatshirt', 'Cargo Pants Tactical', 'Classic Denim Jacket',
                'Thermal Layer Top', 'Oversized Streetwear Tee', 'Track Jacket Athletic'
            ],
            'Home & Kitchen' => [
                'Stainless Steel Cookware Set 10-Piece', 'Smart Electric Water Kettle 1.7L', 'Espresso Coffee Machine Pro',
                'Digital Air Fryer XL 5.5L', 'Non-Stick Cast Iron Frying Pan', 'Automatic Robot Vacuum Cleaner',
                'Microfiber Bed Sheet Set Queen', 'Ergonomic Memory Foam Pillow', 'Touchless Automatic Trash Can',
                'Ceramic Dinnerware Set 16-Piece', 'High-Speed Smoothie Blender 1200W', 'Handheld Garment Steamer',
                'Glass Food Storage Container Pack', 'Digital Precision Kitchen Scale', 'Electric Milk Frother & Steamer'
            ],
            'Health & Beauty' => [
                'Organic Face Moisturizer Cream', 'Hydrating Hyaluronic Serum 30ml', 'Vitamin C Brightening Cleanser',
                'Argan Oil Hair Repair Shampoo', 'Natural Coconut Conditioner Bar', 'Matte Long-Lasting Liquid Lipstick',
                'Purifying Charcoal Face Mask', 'Broad Spectrum Sunscreen SPF 50+', 'Electric Sonic Toothbrush Pro',
                'Pure Lavender Essential Oil 15ml', 'Herbal Refreshing Body Wash', 'Collagen Peptide Protein Powder',
                'Ultra Hydrating Lip Balm Pack', 'Anti-Aging Peptide Eye Cream', 'Organic Beard Oil Grooming Kit'
            ],
            'Sports & Outdoor' => [
                'Non-Slip High-Density Yoga Mat', 'Adjustable Dumbbell Set 50lbs', 'Heavy Duty Resistance Exercise Bands',
                'Insulated Stainless Steel Bottle 1L', 'Waterproof 4-Person Camping Tent', 'Ergonomic Aluminum Trekking Poles',
                'Compact Lightweight Sleeping Bag', 'Official Size Leather Basketball', 'High-Grip Match Soccer Ball',
                'Cycling Helmet with Rear LED', 'Running Hydration Vest Backpack', 'Folding Portable Camping Chair',
                'Pro Carbon Fiber Tennis Racket', 'Anti-Fog UV Swimming Goggles', 'Pickleball Paddle Set Pro'
            ],
            'Books & Stationery' => [
                'Hardcover Dot Grid Journal A5', 'Smooth Gel Ink Pen Set 12-Pack', 'Executive Faux Leather Notebook',
                'Financial Budget Planner Ledger', 'Spiral Architectural Sketchbook', 'Fine Nib Metal Fountain Pen',
                'Pastel Aesthetic Highlighter Set', 'Desktop Acrylic File Organizer', 'Multicolor Sticky Notes Value Pack',
                'Ergonomic Wooden Book Stand', 'Dry Erase Whiteboard Marker Pack', 'Heavy Duty Desktop Stapler Set',
                'Wireless Laser Presenter Clicker', 'Calligraphy Brush Pen Starter Kit', 'Advanced Scientific Calculator'
            ],
            'Automotive & Tools' => [
                'Digital Tire Pressure Gauge 150PSI', 'Heavy-Duty Car Jump Starter 2000A', 'Front & Rear 4K Dash Camera',
                'Microfiber Car Detailing Towels', 'Portable 12V Car Air Compressor', 'Cordless Power Drill Driver 20V',
                'Socket Wrench Set 108-Piece', 'Magnetic Precision Screwdriver Set', 'Rechargeable LED Work Light Bar',
                'Multi-Angle Digital Measuring Ruler', 'Car Leather Seat Gap Organizer', 'Universal Magnetic Car Phone Mount',
                'OBD2 Engine Diagnostic Scanner', 'Hydraulic Trolley Floor Jack 2-Ton', 'Heavy Duty Steering Wheel Lock'
            ],
            'Food & Groceries' => [
                'Organic Whole Coffee Beans 1kg', 'Dark Chocolate Sea Salt Bar 85%', 'Raw Unfiltered Honey 500g',
                'Ceremonial Grade Matcha Powder', 'Extra Virgin Cold Pressed Olive Oil 1L', 'Roasted Salted Whole Almonds 500g',
                'Organic Rolled Gluten-Free Oats', 'Sparkling Natural Mineral Water 12x', 'Premium Himalayan Basmati Rice 5kg',
                'Artisan Italian Tomato Pasta Sauce', 'Whey Protein Powder Chocolate Fudge', 'Organic Dried Mango Slices Unsweetened',
                'Raw Organic Apple Cider Vinegar', 'Herbal Chamomile Tea Bags 50-Count', 'Himalayan Pink Salt Ceramic Grinder'
            ],
            'Toys & Hobbies' => [
                'Building Bricks Classic Set 1000-Pcs', 'Remote Control Drone Quadcopter 4K', 'Strategy Tabletop Board Game',
                '3D Mechanical Wooden Puzzle Clock', 'Acrylic Paint Set with Canvases', 'Giant Plush Teddy Bear 36"',
                'Die-Cast Alloy Model Race Car 1:18', 'Educational STEM Science Lab Kit', 'Magnetic Wooden Chess Set Folding',
                'Speed Cube 3x3 Smooth Stickerless', 'Air-Dry Clay Sculpting Starter Pack', 'Wireless Bluetooth Karaoke Microphone',
                'RC Off-Road Monster Truck 4WD', 'Refractor Astronomy Telescope 70mm', 'Collectible Playing Cards Premium Deck'
            ],
            'Footwear & Shoes' => [
                'Breathable Lightweight Running Shoes', 'Classic Genuine Leather Sneakers', 'Waterproof Trail Hiking Boots',
                'Casual Slip-On Canvas Loafers', 'Memory Foam Indoor Cushion Slippers', 'High-Top Athletic Basketball Shoes',
                'Formal Patent Leather Dress Oxfords', 'Ergonomic Sport Sandal Slides', 'Steel Toe Anti-Slip Work Boots',
                'Lightweight Trail Running Trainers', 'Insulated Waterproof Snow Boots', 'Flexible Cross-Training Gym Shoes',
                'Handcrafted Leather Ankle Boots', 'Breathable Mesh Walking Shoes', 'Suede Driving Moccasins Soft Sole'
            ],
            'Jewelry & Accessories' => [
                'Sterling Silver Minimalist Pendant', 'Slim RFID Blocking Leather Wallet', 'Classic Polarized Aviator Sunglasses',
                'Sport Polarized UV400 Sunglasses', 'Stainless Steel Cuban Link Bracelet', '100% Pure Silk Pattern Necktie',
                'Compact Leather Card Holder Wallet', '14K Gold Plated Hoop Earrings', 'Water-Resistant Canvas Crossbody Bag',
                'Unisex Ribbed Knit Wool Beanie', 'Full Grain Genuine Leather Belt', 'Crystal Drop Dangle Earrings Set',
                'Windproof Automatic Travel Umbrella', 'Adjustable Cotton Snapback Trucker Cap', 'Velvet Portable Jewelry Organizer Case'
            ],
            'Office Supplies' => [
                'Ergonomic High-Back Mesh Desk Chair', 'Dual Motor Electric Standing Desk 55"', 'Dual Monitor Adjustable Gas Arm',
                'Large Felt Desk Pad Mat 35x16"', 'Cross-Cut Heavy Duty Paper Shredder', 'Thermal Shipping Label Printer 4x6',
                'Under-Desk Cable Management Tray', 'Desktop Tiered Document Tray Organizer', 'Memory Foam Ergonomic Foot Rest',
                'Adjustable Aluminum Laptop Riser', 'Magnetic Glass Whiteboard 36x24"', 'Precision Rotary Paper Trimmer',
                'Dimmable LED Video Ring Light 10"', 'Under-Desk Ergonomic Foot Hammock', 'Multi-Outlet Surge Protector Power Strip'
            ]
        ];

        // Variation Attribute Matrix
        $sizeOptions = ['Small', 'Medium', 'Large', 'X-Large'];
        $colorOptions = ['Midnight Black', 'Pure White', 'Navy Blue', 'Crimson Red', 'Space Gray', 'Olive Green'];
        $capacityOptions = ['64GB', '128GB', '256GB', '512GB'];
        $unitsOptions = ['pcs', 'box', 'pack', 'set', 'kg'];

        $targetTotal = 2000;
        $batchCount = 0;

        $categoryKeys = array_keys($productCatalogTemplates);
        $totalCategories = count($categoryKeys);

        $now = now()->toDateTimeString();

        $this->command->info("Creating {$targetTotal} dummy products in batches...");
        $bar = $this->command->getOutput()->createProgressBar($targetTotal);
        $bar->start();

        // Arrays for bulk DB insertion
        $productsToInsert = [];
        $variationsToInsert = [];
        $inventoriesToInsert = [];

        for ($i = 1; $i <= $targetTotal; $i++) {
            // Pick category round-robin
            $categoryName = $categoryKeys[($i - 1) % $totalCategories];
            $categoryObj = $categories->firstWhere('name', $categoryName) ?? $categories->first();
            $categoryId = $categoryObj->id;

            // Pick name template from category
            $templates = $productCatalogTemplates[$categoryName];
            $baseName = $templates[($i - 1) % count($templates)];
            $productName = $baseName . ' #' . (1000 + $i);

            $sku = 'SKU-' . strtoupper(substr(md5($i . $productName), 0, 8)) . '-' . sprintf('%04d', $i);
            $barcode = '890' . sprintf('%010d', $i);

            // Randomize pricing
            $costPrice = round(rand(500, 45000) / 100, 2); // $5.00 to $450.00
            $markup = rand(25, 75); // 25% - 75%
            $sellingPrice = round($costPrice * (1 + ($markup / 100)), 2);
            $wholesalePrice = round($sellingPrice * 0.85, 2);
            $taxRate = $taxesList[rand(0, count($taxesList) - 1)]['value'];

            // Pick tags & taxes JSON
            $productTags = $this->getRandomSubArray($tagsList, rand(1, 3));
            $productTaxes = [
                [
                    'name' => 'Tax ' . $taxRate . '%',
                    'rate' => $taxRate,
                ]
            ];

            $uom = $unitsOptions[rand(0, count($unitsOptions) - 1)];

            // ~50% products have variations, ~50% single products
            $hasVariations = ($i % 2 === 0);

            $productRecord = [
                'company_id' => $companyId,
                'name' => $productName,
                'description' => "High quality {$productName} designed for daily professional and retail use.",
                'sku' => $sku,
                'barcode' => $barcode,
                'cost_price' => $costPrice,
                'selling_price' => $sellingPrice,
                'wholesale_price' => $wholesalePrice,
                'markup_percentage' => $markup,
                'stock_quantity' => 0, // Will be computed from variations or assigned
                'min_stock_level' => rand(5, 15),
                'max_stock_level' => rand(100, 500),
                'unit_of_measure' => $uom,
                'track_inventory' => true,
                'is_active' => true,
                'has_variations' => $hasVariations,
                'status' => 'active',
                'category_id' => $categoryId,
                'weight' => round(rand(10, 300) / 100, 2),
                'tax_rate' => $taxRate,
                'tags' => json_encode($productTags),
                'taxes' => json_encode($productTaxes),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // Create product using Eloquent or DB::insert to get ID
            $createdProduct = Product::create($productRecord);
            $productId = $createdProduct->id;

            $productTotalStock = 0;

            if ($hasVariations) {
                // Generate 2 to 4 variations
                $variationCount = rand(2, 4);
                $isTechCategory = in_array($categoryName, ['Electronics & Gadgets', 'Office Supplies']);
                $optionPool = $isTechCategory ? $capacityOptions : $sizeOptions;

                for ($v = 0; $v < $variationCount; $v++) {
                    $opt1 = $optionPool[$v % count($optionPool)];
                    $opt2 = $colorOptions[rand(0, count($colorOptions) - 1)];

                    $combKey = strtolower(str_replace(' ', '-', "{$opt1}-{$opt2}"));
                    $varString = "{$opt1} / {$opt2}";
                    $varSku = "{$sku}-V" . ($v + 1);
                    $varBarcode = '891' . sprintf('%09d', ($i * 10) + $v);

                    $varCost = round($costPrice * (1 + ($v * 0.05)), 2);
                    $varRetail = round($sellingPrice * (1 + ($v * 0.05)), 2);
                    $varWholesale = round($wholesalePrice * (1 + ($v * 0.05)), 2);
                    $varStock = rand(15, 120);
                    $productTotalStock += $varStock;

                    $variationObj = ProductVariation::create([
                        'product_id' => $productId,
                        'company_id' => $companyId,
                        'combination_key' => $combKey,
                        'variation_name_string' => $varString,
                        'sku' => $varSku,
                        'barcode' => $varBarcode,
                        'retail_price' => $varRetail,
                        'wholesale_price' => $varWholesale,
                        'cost_price' => $varCost,
                        'on_sale' => rand(0, 1) === 1,
                        'sale_price' => round($varRetail * 0.9, 2),
                        'stock_qty' => $varStock,
                        'min_stock_alert' => rand(3, 10),
                        'unit_of_measure' => $uom,
                        'tax_rate' => $taxRate,
                        'tags' => json_encode($productTags),
                        'taxes' => json_encode($productTaxes),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    // Add Inventories for each warehouse for this variation
                    $stockPerWarehouse = (int) floor($varStock / count($warehouseIds));
                    foreach ($warehouseIds as $wIdx => $whId) {
                        $whStock = ($wIdx === 0) ? ($varStock - ($stockPerWarehouse * (count($warehouseIds) - 1))) : $stockPerWarehouse;
                        $inventoriesToInsert[] = [
                            'company_id' => $companyId,
                            'warehouse_id' => $whId,
                            'product_id' => $productId,
                            'product_variation_id' => $variationObj->id,
                            'stock_qty' => max(0, $whStock),
                            'min_stock_level' => rand(3, 10),
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                // Update product total stock
                $createdProduct->update(['stock_quantity' => $productTotalStock]);
            } else {
                // Single Product Stock
                $productStock = rand(20, 250);
                $createdProduct->update(['stock_quantity' => $productStock]);

                // Add Inventories for each warehouse for single product
                $stockPerWarehouse = (int) floor($productStock / count($warehouseIds));
                foreach ($warehouseIds as $wIdx => $whId) {
                    $whStock = ($wIdx === 0) ? ($productStock - ($stockPerWarehouse * (count($warehouseIds) - 1))) : $stockPerWarehouse;
                    $inventoriesToInsert[] = [
                        'company_id' => $companyId,
                        'warehouse_id' => $whId,
                        'product_id' => $productId,
                        'product_variation_id' => null,
                        'stock_qty' => max(0, $whStock),
                        'min_stock_level' => rand(5, 15),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            // Flush inventory records in batches of 300 to keep memory optimal
            if (count($inventoriesToInsert) >= 300) {
                Inventory::insert($inventoriesToInsert);
                $inventoriesToInsert = [];
            }

            $bar->advance();
        }

        // Insert any remaining inventory records
        if (count($inventoriesToInsert) > 0) {
            Inventory::insert($inventoriesToInsert);
        }

        $bar->finish();
        $this->command->info('');
        $this->command->info("✅ Successfully created 2,000 dummy products with variations, categories, tags, taxes & warehouse stock!");
    }

    /**
     * Helper to ensure default warehouses exist.
     */
    private function ensureWarehousesExist($companyId)
    {
        $defaultWarehouses = [
            ['name' => 'Main Warehouse', 'is_default' => true, 'is_active' => true, 'is_saleable' => true],
            ['name' => 'City Central Store', 'is_default' => false, 'is_active' => true, 'is_saleable' => true],
            ['name' => 'North Distribution Depot', 'is_default' => false, 'is_active' => true, 'is_saleable' => true],
        ];

        foreach ($defaultWarehouses as $wh) {
            Warehouse::firstOrCreate(
                ['name' => $wh['name'], 'company_id' => $companyId],
                array_merge($wh, ['company_id' => $companyId])
            );
        }

        return Warehouse::where('company_id', $companyId)->get();
    }

    /**
     * Helper to ensure categories exist.
     */
    private function ensureCategoriesExist($companyId)
    {
        $categoryList = [
            'Electronics & Gadgets' => 'Electronic devices, smart gadgets, and high-tech accessories.',
            'Clothing & Apparel' => 'Men and women fashion, activewear, and outerwear.',
            'Home & Kitchen' => 'Home appliances, cookware, furniture, and kitchenware.',
            'Health & Beauty' => 'Skincare, cosmetics, personal care, and wellness supplements.',
            'Sports & Outdoor' => 'Fitness equipment, camping gear, sports balls, and activewear.',
            'Books & Stationery' => 'Notebooks, fine pens, planners, and educational supplies.',
            'Automotive & Tools' => 'Car accessories, diagnostic tools, power tools, and hardware.',
            'Food & Groceries' => 'Gourmet snacks, organic coffee, pantry essentials, and beverages.',
            'Toys & Hobbies' => 'Board games, STEM kits, RC toys, and collectibles.',
            'Footwear & Shoes' => 'Athletic sneakers, formal leather shoes, boots, and sandals.',
            'Jewelry & Accessories' => 'Watches, leather wallets, sunglasses, and fine jewelry.',
            'Office Supplies' => 'Ergonomic chairs, standing desks, shredders, and paper products.'
        ];

        foreach ($categoryList as $name => $desc) {
            Category::firstOrCreate(
                ['name' => $name],
                [
                    'name' => $name,
                    'description' => $desc,
                    'is_active' => true,
                    'company_id' => $companyId,
                ]
            );
        }

        return Category::all();
    }

    /**
     * Helper to ensure Taxes exist.
     */
    private function ensureTaxesExist($companyId)
    {
        $taxDefinitions = [
            ['name' => 'Zero Rated', 'value' => 0.00, 'type' => 'percentage', 'is_active' => true],
            ['name' => 'Reduced GST', 'value' => 5.00, 'type' => 'percentage', 'is_active' => true],
            ['name' => 'Standard Sales Tax', 'value' => 8.25, 'type' => 'percentage', 'is_active' => true],
            ['name' => 'VAT 10%', 'value' => 10.00, 'type' => 'percentage', 'is_active' => true],
            ['name' => 'Luxury Goods Tax', 'value' => 18.00, 'type' => 'percentage', 'is_active' => true],
        ];

        foreach ($taxDefinitions as $tax) {
            Tax::firstOrCreate(
                ['name' => $tax['name']],
                array_merge($tax, ['company_id' => $companyId])
            );
        }

        return Tax::where('is_active', true)->get()->toArray();
    }

    /**
     * Helper to ensure Tags exist.
     */
    private function ensureTagsExist($companyId)
    {
        $tagNames = [
            'New Arrival', 'Featured', 'Best Seller', 'On Sale', 'Trending',
            'Clearance', 'Hot Item', 'Eco-Friendly', 'Premium', 'Limited Edition'
        ];

        foreach ($tagNames as $tName) {
            Tag::firstOrCreate(
                ['name' => $tName, 'company_id' => $companyId],
                ['name' => $tName, 'company_id' => $companyId]
            );
        }

        return $tagNames;
    }

    /**
     * Helper to pick random subset from array.
     */
    private function getRandomSubArray(array $arr, int $count): array
    {
        shuffle($arr);
        return array_slice($arr, 0, min($count, count($arr)));
    }
}
