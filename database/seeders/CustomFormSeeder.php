<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BusinessType;
use App\Models\CustomForm;

class CustomFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Restaurant Business Type - Sale Invoice Form
        $restaurantType = BusinessType::where('slug', 'food_services_restaurant')
            ->orWhere('slug', 'food_services')
            ->orWhere('name', 'like', '%Restaurant%')
            ->first();

        if ($restaurantType) {
            CustomForm::updateOrCreate(
                [
                    'business_type_id' => $restaurantType->id,
                    'area_of_use' => 'sale_invoice',
                ],
                [
                    'name' => 'Restaurant Sale Invoice Dynamic Fields',
                    'description' => 'Custom fields for Dine-in tables, guest count, waiter ID, KOT number, order status and type.',
                    'is_active' => true,
                    'sort_order' => 1,
                    'fields' => [
                        [
                            'id' => 'table_assignment',
                            'name' => 'table_assignment',
                            'label' => 'Table Assignment (for Dine-in)',
                            'type' => 'select',
                            'placeholder' => 'Select Table',
                            'required' => false,
                            'width' => 'half',
                            'options' => [
                                ['label' => 'Table 01', 'value' => 'Table 01'],
                                ['label' => 'Table 02', 'value' => 'Table 02'],
                                ['label' => 'Table 03', 'value' => 'Table 03'],
                                ['label' => 'VIP Table A', 'value' => 'VIP Table A'],
                                ['label' => 'Patio Table 1', 'value' => 'Patio Table 1'],
                            ],
                            'help_text' => 'Required for dine-in seating tracking'
                        ],
                        [
                            'id' => 'guest_count',
                            'name' => 'guest_count',
                            'label' => 'Guest Count',
                            'type' => 'number',
                            'placeholder' => 'Number of guests',
                            'default_value' => 1,
                            'required' => true,
                            'width' => 'half',
                            'help_text' => 'Total number of guests at table'
                        ],
                        [
                            'id' => 'waiter_id',
                            'name' => 'waiter_id',
                            'label' => 'Waiter / Server Identification',
                            'type' => 'text',
                            'placeholder' => 'e.g. Waiter #4 (John Doe)',
                            'required' => true,
                            'width' => 'half',
                            'help_text' => 'Assigned server ID or name'
                        ],
                        [
                            'id' => 'order_type',
                            'name' => 'order_type',
                            'label' => 'Order Type',
                            'type' => 'select',
                            'default_value' => 'Dine-in',
                            'required' => true,
                            'width' => 'half',
                            'options' => [
                                ['label' => 'Dine-in', 'value' => 'Dine-in'],
                                ['label' => 'Takeaway', 'value' => 'Takeaway'],
                                ['label' => 'Delivery', 'value' => 'Delivery'],
                            ],
                            'help_text' => 'Type of order fulfillment'
                        ],
                        [
                            'id' => 'order_status',
                            'name' => 'order_status',
                            'label' => 'Order Status Tracking',
                            'type' => 'select',
                            'default_value' => 'Taken',
                            'required' => true,
                            'width' => 'half',
                            'options' => [
                                ['label' => 'Taken', 'value' => 'Taken'],
                                ['label' => 'Ready', 'value' => 'Ready'],
                                ['label' => 'Served', 'value' => 'Served'],
                                ['label' => 'Re-order', 'value' => 'Re-order'],
                                ['label' => 'Completed', 'value' => 'Completed'],
                            ],
                            'help_text' => 'Current kitchen / service status'
                        ],
                        [
                            'id' => 'kot_number',
                            'name' => 'kot_number',
                            'label' => 'KOT Numbering',
                            'type' => 'text',
                            'placeholder' => 'e.g. KOT-2026-0891',
                            'required' => false,
                            'width' => 'half',
                            'help_text' => 'Kitchen Order Ticket reference number'
                        ]
                    ]
                ]
            );
        }

        // 2. Commission Shop / Wholesale Business Type - Sale Invoice Form
        $commissionType = BusinessType::where('slug', 'wholesale_trade')
            ->orWhere('name', 'like', '%Wholesale%')
            ->orWhere('name', 'like', '%Commission%')
            ->first();

        if ($commissionType) {
            CustomForm::updateOrCreate(
                [
                    'business_type_id' => $commissionType->id,
                    'area_of_use' => 'sale_invoice',
                ],
                [
                    'name' => 'Commission Shop Sale Invoice Dynamic Fields',
                    'description' => 'Custom fields for commission calculations, automated fee application, loading, labor, packing, and transport charges.',
                    'is_active' => true,
                    'sort_order' => 2,
                    'fields' => [
                        [
                            'id' => 'commission_type',
                            'name' => 'commission_type',
                            'label' => 'Commission Calculation Type',
                            'type' => 'select',
                            'default_value' => 'Percentage (%)',
                            'required' => true,
                            'width' => 'half',
                            'options' => [
                                ['label' => 'Percentage (%)', 'value' => 'Percentage (%)'],
                                ['label' => 'Fixed Amount', 'value' => 'Fixed Amount'],
                            ],
                            'help_text' => 'Choose percentage rate or fixed fee calculation'
                        ],
                        [
                            'id' => 'commission_rate',
                            'name' => 'commission_rate',
                            'label' => 'Commission Rate / Amount',
                            'type' => 'number',
                            'placeholder' => 'e.g. 5.00',
                            'default_value' => 5.00,
                            'required' => true,
                            'width' => 'half',
                            'help_text' => 'Commission percentage or fixed monetary value'
                        ],
                        [
                            'id' => 'auto_fee_application',
                            'name' => 'auto_fee_application',
                            'label' => 'Automated Fee Application',
                            'type' => 'toggle',
                            'default_value' => true,
                            'required' => false,
                            'width' => 'full',
                            'help_text' => 'Automatically apply commission & charges to grand total'
                        ],
                        [
                            'id' => 'loading_charge',
                            'name' => 'loading_charge',
                            'label' => 'Loading / Unloading Charge',
                            'type' => 'number',
                            'placeholder' => '0.00',
                            'default_value' => 0.00,
                            'required' => false,
                            'width' => 'half',
                            'help_text' => 'Operational loading & unloading fees'
                        ],
                        [
                            'id' => 'labor_charge',
                            'name' => 'labor_charge',
                            'label' => 'Labor Charge',
                            'type' => 'number',
                            'placeholder' => '0.00',
                            'default_value' => 0.00,
                            'required' => false,
                            'width' => 'half',
                            'help_text' => 'Manual labor handling charges'
                        ],
                        [
                            'id' => 'packing_charge',
                            'name' => 'packing_charge',
                            'label' => 'Packing Charge',
                            'type' => 'number',
                            'placeholder' => '0.00',
                            'default_value' => 0.00,
                            'required' => false,
                            'width' => 'half',
                            'help_text' => 'Packaging and bagging costs'
                        ],
                        [
                            'id' => 'transport_charge',
                            'name' => 'transport_charge',
                            'label' => 'Transportation Charge',
                            'type' => 'number',
                            'placeholder' => '0.00',
                            'default_value' => 0.00,
                            'required' => false,
                            'width' => 'half',
                            'help_text' => 'Freight and carriage delivery fees'
                        ]
                    ]
                ]
            );
        }
    }
}
