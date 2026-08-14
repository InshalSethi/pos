<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountingSetting;
use App\Models\Company;
use App\Models\ExpenseCategory;
use App\Models\Scopes\CompanyScope;
use Illuminate\Support\Facades\Log;

class ChartOfAccountService
{
    /**
     * Get the master enterprise chart of accounts definition.
     * Structured according to GAAP/IFRS standards for ERP & POS software.
     */
    public static function getStandardAccountDefinitions(): array
    {
        return [
            // -------------------------------------------------------------
            // 1000 - ASSETS (1000-1999)
            // -------------------------------------------------------------
            // Parent: Current Assets
            [
                'account_code' => '1000',
                'account_name' => 'Current Assets',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'description' => 'Header for liquid cash, bank, receivables, inventory, and short-term assets',
                'is_system_account' => true,
                'parent_code' => null,
            ],
            [
                'account_code' => '1010',
                'account_name' => 'Cash on Hand',
                'account_type' => 'asset',
                'account_subtype' => 'cash_and_bank',
                'description' => 'Main petty cash and counter vault cash balance',
                'is_system_account' => true,
                'parent_code' => '1000',
            ],
            [
                'account_code' => '1011',
                'account_name' => 'Register & Cash Drawer (POS Till)',
                'account_type' => 'asset',
                'account_subtype' => 'cash_and_bank',
                'description' => 'Point of Sale register drawer cash balance',
                'is_system_account' => false,
                'parent_code' => '1000',
            ],
            [
                'account_code' => '1020',
                'account_name' => 'Main Operating Bank Account',
                'account_type' => 'asset',
                'account_subtype' => 'cash_and_bank',
                'description' => 'Primary commercial bank account for business operations',
                'is_system_account' => true,
                'parent_code' => '1000',
            ],
            [
                'account_code' => '1021',
                'account_name' => 'POS Card Payment Clearing',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'description' => 'Uncleared credit/debit card payments from POS card terminals',
                'is_system_account' => false,
                'parent_code' => '1000',
            ],
            [
                'account_code' => '1022',
                'account_name' => 'Digital Wallets & Payment Gateway Clearing',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'description' => 'Clearing ledger for Stripe, PayPal, JazzCash, EasyPaisa, etc.',
                'is_system_account' => false,
                'parent_code' => '1000',
            ],
            [
                'account_code' => '1030',
                'account_name' => 'Accounts Receivable',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'description' => 'Money owed by customers for credit sales (Trade Debtors)',
                'is_system_account' => true,
                'parent_code' => '1000',
            ],
            [
                'account_code' => '1035',
                'account_name' => 'Allowance for Doubtful Accounts',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'description' => 'Contra-asset reserve for potential uncollectible receivables',
                'is_system_account' => false,
                'parent_code' => '1000',
            ],
            [
                'account_code' => '1040',
                'account_name' => 'Inventory on Hand',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'description' => 'Valuation of finished products and stock items currently on hand',
                'is_system_account' => true,
                'parent_code' => '1000',
            ],
            [
                'account_code' => '1041',
                'account_name' => 'Raw Materials Inventory',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'description' => 'Raw materials and ingredients reserved for production/assembly',
                'is_system_account' => false,
                'parent_code' => '1000',
            ],
            [
                'account_code' => '1042',
                'account_name' => 'Work-in-Progress (WIP) Inventory',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'description' => 'Goods in process of manufacturing or bundling',
                'is_system_account' => false,
                'parent_code' => '1000',
            ],
            [
                'account_code' => '1045',
                'account_name' => 'Goods Received Not Invoiced (GRNI)',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'description' => 'Clearing account for inventory physically received prior to supplier invoice',
                'is_system_account' => false,
                'parent_code' => '1000',
            ],
            [
                'account_code' => '1050',
                'account_name' => 'Prepaid Expenses',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'description' => 'Advance payments for rent, insurance, software licenses, etc.',
                'is_system_account' => false,
                'parent_code' => '1000',
            ],
            [
                'account_code' => '1060',
                'account_name' => 'Employee Loans & Advances',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'description' => 'Short-term salary advances and loans extended to staff',
                'is_system_account' => false,
                'parent_code' => '1000',
            ],
            [
                'account_code' => '1070',
                'account_name' => 'Input Sales Tax / VAT Recoverable',
                'account_type' => 'asset',
                'account_subtype' => 'current_asset',
                'description' => 'Sales tax/VAT paid on purchases and expenses eligible for credit',
                'is_system_account' => true,
                'parent_code' => '1000',
            ],
            [
                'account_code' => '1080',
                'account_name' => 'Security Deposits & Guarantees',
                'account_type' => 'asset',
                'account_subtype' => 'other_asset',
                'description' => 'Refundable deposits paid for building leases, utilities, equipment',
                'is_system_account' => false,
                'parent_code' => '1000',
            ],

            // Parent: Non-Current / Fixed Assets
            [
                'account_code' => '1500',
                'account_name' => 'Fixed Assets',
                'account_type' => 'asset',
                'account_subtype' => 'fixed_asset',
                'description' => 'Header for long-term physical property, plant, and equipment assets',
                'is_system_account' => true,
                'parent_code' => null,
            ],
            [
                'account_code' => '1510',
                'account_name' => 'Land & Buildings',
                'account_type' => 'asset',
                'account_subtype' => 'fixed_asset',
                'description' => 'Real estate property, land parcels, and commercial structures',
                'is_system_account' => false,
                'parent_code' => '1500',
            ],
            [
                'account_code' => '1520',
                'account_name' => 'Leasehold Improvements',
                'account_type' => 'asset',
                'account_subtype' => 'fixed_asset',
                'description' => 'Renovations and structural enhancements made to rented store premises',
                'is_system_account' => false,
                'parent_code' => '1500',
            ],
            [
                'account_code' => '1530',
                'account_name' => 'Furniture, Fixtures & Store Fittings',
                'account_type' => 'asset',
                'account_subtype' => 'fixed_asset',
                'description' => 'Display racks, shelving units, checkout counters, furniture',
                'is_system_account' => false,
                'parent_code' => '1500',
            ],
            [
                'account_code' => '1540',
                'account_name' => 'POS Terminals, Computers & IT Hardware',
                'account_type' => 'asset',
                'account_subtype' => 'fixed_asset',
                'description' => 'Receipt printers, barcode scanners, servers, computers, POS hardware',
                'is_system_account' => false,
                'parent_code' => '1500',
            ],
            [
                'account_code' => '1550',
                'account_name' => 'Machinery & Warehouse Storage Equipment',
                'account_type' => 'asset',
                'account_subtype' => 'fixed_asset',
                'description' => 'Forklifts, packaging machines, industrial warehouse equipment',
                'is_system_account' => false,
                'parent_code' => '1500',
            ],
            [
                'account_code' => '1560',
                'account_name' => 'Delivery Vehicles & Logistics Fleet',
                'account_type' => 'asset',
                'account_subtype' => 'fixed_asset',
                'description' => 'Vans, trucks, and motorbikes used for order delivery and stock transport',
                'is_system_account' => false,
                'parent_code' => '1500',
            ],
            [
                'account_code' => '1570',
                'account_name' => 'Accumulated Depreciation - Fixtures & IT',
                'account_type' => 'asset',
                'account_subtype' => 'fixed_asset',
                'description' => 'Cumulative depreciation deduction for store fixtures and IT equipment',
                'is_system_account' => false,
                'parent_code' => '1500',
            ],
            [
                'account_code' => '1580',
                'account_name' => 'Accumulated Depreciation - Vehicles & Fleet',
                'account_type' => 'asset',
                'account_subtype' => 'fixed_asset',
                'description' => 'Cumulative depreciation deduction for delivery vehicles',
                'is_system_account' => false,
                'parent_code' => '1500',
            ],
            [
                'account_code' => '1590',
                'account_name' => 'Intangible Assets & Software Licenses',
                'account_type' => 'asset',
                'account_subtype' => 'other_asset',
                'description' => 'Trademarks, domain names, patents, and ERP software rights',
                'is_system_account' => false,
                'parent_code' => '1500',
            ],

            // -------------------------------------------------------------
            // 2000 - LIABILITIES (2000-2999)
            // -------------------------------------------------------------
            // Parent: Current Liabilities
            [
                'account_code' => '2000',
                'account_name' => 'Current Liabilities',
                'account_type' => 'liability',
                'account_subtype' => 'current_liability',
                'description' => 'Header for obligations and payables due within 1 year',
                'is_system_account' => true,
                'parent_code' => null,
            ],
            [
                'account_code' => '2010',
                'account_name' => 'Accounts Payable',
                'account_type' => 'liability',
                'account_subtype' => 'current_liability',
                'description' => 'Trade creditors and unpaid supplier invoices',
                'is_system_account' => true,
                'parent_code' => '2000',
            ],
            [
                'account_code' => '2020',
                'account_name' => 'Output Sales Tax / VAT Payable',
                'account_type' => 'liability',
                'account_subtype' => 'current_liability',
                'description' => 'Sales tax/VAT collected from customers due to tax authorities',
                'is_system_account' => true,
                'parent_code' => '2000',
            ],
            [
                'account_code' => '2025',
                'account_name' => 'Income Tax & Withholding Tax Payable',
                'account_type' => 'liability',
                'account_subtype' => 'current_liability',
                'description' => 'Accrued corporate income tax and vendor withholding taxes payable',
                'is_system_account' => false,
                'parent_code' => '2000',
            ],
            [
                'account_code' => '2030',
                'account_name' => 'Accrued Expenses',
                'account_type' => 'liability',
                'account_subtype' => 'current_liability',
                'description' => 'Operating expenses incurred but not yet billed or paid',
                'is_system_account' => false,
                'parent_code' => '2000',
            ],
            [
                'account_code' => '2035',
                'account_name' => 'Salaries & Wages Payable',
                'account_type' => 'liability',
                'account_subtype' => 'current_liability',
                'description' => 'Earned employee payroll liabilities awaiting payout',
                'is_system_account' => true,
                'parent_code' => '2000',
            ],
            [
                'account_code' => '2040',
                'account_name' => 'Unearned Revenue & Customer Gift Cards',
                'account_type' => 'liability',
                'account_subtype' => 'current_liability',
                'description' => 'Advance customer deposits, unredeemed gift cards, and prepaid orders',
                'is_system_account' => false,
                'parent_code' => '2000',
            ],
            [
                'account_code' => '2050',
                'account_name' => 'Short-Term Loans & Bank Overdrafts',
                'account_type' => 'liability',
                'account_subtype' => 'current_liability',
                'description' => 'Working capital credit lines, bank overdrafts, short-term debt',
                'is_system_account' => false,
                'parent_code' => '2000',
            ],
            [
                'account_code' => '2060',
                'account_name' => 'POS Cash Clearing / Undeposited Funds',
                'account_type' => 'liability',
                'account_subtype' => 'current_liability',
                'description' => 'Temporary holding ledger for shift end cash waiting for bank deposit',
                'is_system_account' => false,
                'parent_code' => '2000',
            ],

            // Parent: Long-Term Liabilities
            [
                'account_code' => '2500',
                'account_name' => 'Long-term Liabilities',
                'account_type' => 'liability',
                'account_subtype' => 'long_term_liability',
                'description' => 'Header for long-term debts due after 1 year',
                'is_system_account' => true,
                'parent_code' => null,
            ],
            [
                'account_code' => '2510',
                'account_name' => 'Long-Term Mortgages & Bank Loans',
                'account_type' => 'liability',
                'account_subtype' => 'long_term_liability',
                'description' => 'Commercial mortgages, institutional business loans maturing >1 yr',
                'is_system_account' => false,
                'parent_code' => '2500',
            ],
            [
                'account_code' => '2520',
                'account_name' => 'Equipment & Vehicle Finance Leases',
                'account_type' => 'liability',
                'account_subtype' => 'long_term_liability',
                'description' => 'Long-term capital lease obligations for fleet or store machinery',
                'is_system_account' => false,
                'parent_code' => '2500',
            ],
            [
                'account_code' => '2530',
                'account_name' => 'Director & Owner Loans Payable',
                'account_type' => 'liability',
                'account_subtype' => 'long_term_liability',
                'description' => 'Funds loaned to the company by owners or directors',
                'is_system_account' => false,
                'parent_code' => '2500',
            ],

            // -------------------------------------------------------------
            // 3000 - EQUITY (3000-3999)
            // -------------------------------------------------------------
            // Parent: Owner's Equity
            [
                'account_code' => '3000',
                'account_name' => "Owner's Equity",
                'account_type' => 'equity',
                'account_subtype' => 'equity',
                'description' => 'Header for capital, paid-in surplus, and cumulative retained earnings',
                'is_system_account' => true,
                'parent_code' => null,
            ],
            [
                'account_code' => '3010',
                'account_name' => "Owner's Capital / Share Capital",
                'account_type' => 'equity',
                'account_subtype' => 'owner_equity',
                'description' => 'Initial and additional capital invested by business owners/shareholders',
                'is_system_account' => false,
                'parent_code' => '3000',
            ],
            [
                'account_code' => '3020',
                'account_name' => 'Retained Earnings',
                'account_type' => 'equity',
                'account_subtype' => 'equity',
                'description' => 'Accumulated net profits retained in the business from prior fiscal years',
                'is_system_account' => true,
                'parent_code' => '3000',
            ],
            [
                'account_code' => '3030',
                'account_name' => 'Current Year Net Income',
                'account_type' => 'equity',
                'account_subtype' => 'equity',
                'description' => 'System account tracking current fiscal year net income before period close',
                'is_system_account' => true,
                'parent_code' => '3000',
            ],
            [
                'account_code' => '3040',
                'account_name' => "Owner Drawings & Distributions",
                'account_type' => 'equity',
                'account_subtype' => 'owner_equity',
                'description' => 'Withdrawals and dividend distributions paid out to owners/partners',
                'is_system_account' => false,
                'parent_code' => '3000',
            ],

            // -------------------------------------------------------------
            // 4000 - REVENUE / INCOME (4000-4999)
            // -------------------------------------------------------------
            // Parent: Operating Revenue
            [
                'account_code' => '4000',
                'account_name' => 'Operating Revenue',
                'account_type' => 'revenue',
                'account_subtype' => 'operating_revenue',
                'description' => 'Header for main business operating gross revenues',
                'is_system_account' => true,
                'parent_code' => null,
            ],
            [
                'account_code' => '4010',
                'account_name' => 'Sales Revenue - POS Store Sales',
                'account_type' => 'revenue',
                'account_subtype' => 'operating_revenue',
                'description' => 'Primary revenue generated from retail counter and store sales',
                'is_system_account' => true,
                'parent_code' => '4000',
            ],
            [
                'account_code' => '4015',
                'account_name' => 'Sales Revenue - Online / E-Commerce',
                'account_type' => 'revenue',
                'account_subtype' => 'operating_revenue',
                'description' => 'Gross revenue generated from web shop and online sales',
                'is_system_account' => false,
                'parent_code' => '4000',
            ],
            [
                'account_code' => '4020',
                'account_name' => 'Service & Installation Revenue',
                'account_type' => 'revenue',
                'account_subtype' => 'operating_revenue',
                'description' => 'Income earned from repair, installation, and technical services',
                'is_system_account' => false,
                'parent_code' => '4000',
            ],
            [
                'account_code' => '4030',
                'account_name' => 'Wholesale Sales Revenue',
                'account_type' => 'revenue',
                'account_subtype' => 'operating_revenue',
                'description' => 'Bulk sales revenue to corporate clients and sub-distributors',
                'is_system_account' => false,
                'parent_code' => '4000',
            ],
            [
                'account_code' => '4040',
                'account_name' => 'Shipping & Delivery Revenue Charged',
                'account_type' => 'revenue',
                'account_subtype' => 'operating_revenue',
                'description' => 'Freight and delivery charges billed to customers',
                'is_system_account' => false,
                'parent_code' => '4000',
            ],
            [
                'account_code' => '4050',
                'account_name' => 'Sales Discounts Allowed',
                'account_type' => 'revenue',
                'account_subtype' => 'operating_revenue',
                'description' => 'Contra-revenue account for checkout promotional discounts and price cuts',
                'is_system_account' => false,
                'parent_code' => '4000',
            ],
            [
                'account_code' => '4060',
                'account_name' => 'Sales Returns & Allowances',
                'account_type' => 'revenue',
                'account_subtype' => 'operating_revenue',
                'description' => 'Contra-revenue account for customer returns and item refunds',
                'is_system_account' => true,
                'parent_code' => '4000',
            ],

            // Parent: Other Revenue
            [
                'account_code' => '4900',
                'account_name' => 'Other Revenue',
                'account_type' => 'revenue',
                'account_subtype' => 'other_revenue',
                'description' => 'Header for secondary, non-operating income streams',
                'is_system_account' => true,
                'parent_code' => null,
            ],
            [
                'account_code' => '4910',
                'account_name' => 'Interest & Financial Income',
                'account_type' => 'revenue',
                'account_subtype' => 'other_revenue',
                'description' => 'Interest earned on savings deposits and investment accounts',
                'is_system_account' => false,
                'parent_code' => '4900',
            ],
            [
                'account_code' => '4920',
                'account_name' => 'Vendor Rebates & Volume Allowances',
                'account_type' => 'revenue',
                'account_subtype' => 'other_revenue',
                'description' => 'Cash rebates and target incentives received from suppliers',
                'is_system_account' => false,
                'parent_code' => '4900',
            ],
            [
                'account_code' => '4930',
                'account_name' => 'Gain / Loss on Sale of Assets',
                'account_type' => 'revenue',
                'account_subtype' => 'other_revenue',
                'description' => 'Net profit or loss realized from disposing old fixed assets',
                'is_system_account' => false,
                'parent_code' => '4900',
            ],
            [
                'account_code' => '4940',
                'account_name' => 'Scrap & Packaging Material Sales',
                'account_type' => 'revenue',
                'account_subtype' => 'other_revenue',
                'description' => 'Revenue from selling empty cartons, pallets, and scrap metal/paper',
                'is_system_account' => false,
                'parent_code' => '4900',
            ],
            [
                'account_code' => '4990',
                'account_name' => 'Miscellaneous Revenue',
                'account_type' => 'revenue',
                'account_subtype' => 'other_revenue',
                'description' => 'Uncategorized non-operational small receipts',
                'is_system_account' => false,
                'parent_code' => '4900',
            ],

            // -------------------------------------------------------------
            // 5000 - COST OF GOODS SOLD (COGS) (5000-5999)
            // -------------------------------------------------------------
            // Parent: Cost of Goods Sold Header
            [
                'account_code' => '5000',
                'account_name' => 'Cost of Goods Sold',
                'account_type' => 'expense',
                'account_subtype' => 'cost_of_goods_sold',
                'description' => 'Header for direct costs associated with merchandise sold',
                'is_system_account' => true,
                'parent_code' => null,
            ],
            [
                'account_code' => '5010',
                'account_name' => 'Cost of Goods Sold - Finished Goods',
                'account_type' => 'expense',
                'account_subtype' => 'cost_of_goods_sold',
                'description' => 'Direct acquisition cost of products sold to customers',
                'is_system_account' => true,
                'parent_code' => '5000',
            ],
            [
                'account_code' => '5020',
                'account_name' => 'Purchase Discounts Received',
                'account_type' => 'expense',
                'account_subtype' => 'cost_of_goods_sold',
                'description' => 'Contra-cost account for early settlement discounts from suppliers',
                'is_system_account' => false,
                'parent_code' => '5000',
            ],
            [
                'account_code' => '5030',
                'account_name' => 'Freight-In & Import Duty Charges',
                'account_type' => 'expense',
                'account_subtype' => 'cost_of_goods_sold',
                'description' => 'Inbound transport, customs tariffs, and port handling charges',
                'is_system_account' => false,
                'parent_code' => '5000',
            ],
            [
                'account_code' => '5040',
                'account_name' => 'Inventory Shrinkage & Write-offs',
                'account_type' => 'expense',
                'account_subtype' => 'cost_of_goods_sold',
                'description' => 'Losses due to damaged stock, theft, spoilage, or physical audit variance',
                'is_system_account' => false,
                'parent_code' => '5000',
            ],
            [
                'account_code' => '5050',
                'account_name' => 'Direct Labor & Packaging Expenses',
                'account_type' => 'expense',
                'account_subtype' => 'cost_of_goods_sold',
                'description' => 'Direct assembly wages and product wrapping costs',
                'is_system_account' => false,
                'parent_code' => '5000',
            ],

            // -------------------------------------------------------------
            // 6000 - OPERATING EXPENSES (6000-6999)
            // -------------------------------------------------------------
            // Parent: Operating Expenses Header
            [
                'account_code' => '6000',
                'account_name' => 'Operating Expenses',
                'account_type' => 'expense',
                'account_subtype' => 'operating_expense',
                'description' => 'Header for general administrative, selling, and operational expenses',
                'is_system_account' => true,
                'parent_code' => null,
            ],
            [
                'account_code' => '6010',
                'account_name' => 'Rent & Store Lease Expenses',
                'account_type' => 'expense',
                'account_subtype' => 'operating_expense',
                'description' => 'Monthly rental and property lease payments for outlets and offices',
                'is_system_account' => false,
                'parent_code' => '6000',
            ],
            [
                'account_code' => '6020',
                'account_name' => 'Electricity, Gas & Water Utilities',
                'account_type' => 'expense',
                'account_subtype' => 'operating_expense',
                'description' => 'Power, water, heating, and utility bills for business premises',
                'is_system_account' => false,
                'parent_code' => '6000',
            ],
            [
                'account_code' => '6030',
                'account_name' => 'Salaries & Wages Expense',
                'account_type' => 'expense',
                'account_subtype' => 'operating_expense',
                'description' => 'Regular monthly staff payroll and wage payments',
                'is_system_account' => true,
                'parent_code' => '6000',
            ],
            [
                'account_code' => '6035',
                'account_name' => 'Staff Health & Medical Benefits',
                'account_type' => 'expense',
                'account_subtype' => 'operating_expense',
                'description' => 'Employee health insurance, bonuses, and social security contributions',
                'is_system_account' => false,
                'parent_code' => '6000',
            ],
            [
                'account_code' => '6040',
                'account_name' => 'Sales Commissions & Performance Bonuses',
                'account_type' => 'expense',
                'account_subtype' => 'operating_expense',
                'description' => 'Incentives paid to sales reps and cashier target bonuses',
                'is_system_account' => false,
                'parent_code' => '6000',
            ],
            [
                'account_code' => '6050',
                'account_name' => 'POS Software & Cloud Subscriptions',
                'account_type' => 'expense',
                'account_subtype' => 'operating_expense',
                'description' => 'ERP subscriptions, cloud hosting, software licenses, domain fees',
                'is_system_account' => false,
                'parent_code' => '6000',
            ],
            [
                'account_code' => '6060',
                'account_name' => 'Credit Card Merchant Processing Fees',
                'account_type' => 'expense',
                'account_subtype' => 'operating_expense',
                'description' => 'Bank commission percentages charged on credit/debit card transactions',
                'is_system_account' => false,
                'parent_code' => '6000',
            ],
            [
                'account_code' => '6070',
                'account_name' => 'Advertising & Marketing Expenses',
                'account_type' => 'expense',
                'account_subtype' => 'operating_expense',
                'description' => 'Social media ads, Google ads, flyers, billboards, promotional campaigns',
                'is_system_account' => false,
                'parent_code' => '6000',
            ],
            [
                'account_code' => '6080',
                'account_name' => 'Store Maintenance & Equipment Repair',
                'account_type' => 'expense',
                'account_subtype' => 'operating_expense',
                'description' => 'Servicing for AC units, generators, POS printers, store repairs',
                'is_system_account' => false,
                'parent_code' => '6000',
            ],
            [
                'account_code' => '6090',
                'account_name' => 'Office Supplies & Shopping Bags',
                'account_type' => 'expense',
                'account_subtype' => 'operating_expense',
                'description' => 'Paper, thermal rolls, shopping bags, tape, office stationery',
                'is_system_account' => false,
                'parent_code' => '6000',
            ],
            [
                'account_code' => '6100',
                'account_name' => 'Depreciation Expense - Fixtures & IT',
                'account_type' => 'expense',
                'account_subtype' => 'operating_expense',
                'description' => 'Period depreciation write-off for store fixtures and computer gear',
                'is_system_account' => false,
                'parent_code' => '6000',
            ],
            [
                'account_code' => '6110',
                'account_name' => 'Depreciation Expense - Delivery Vehicles',
                'account_type' => 'expense',
                'account_subtype' => 'operating_expense',
                'description' => 'Period depreciation write-off for fleet vehicles',
                'is_system_account' => false,
                'parent_code' => '6000',
            ],
            [
                'account_code' => '6120',
                'account_name' => 'Insurance Expense (Property & General)',
                'account_type' => 'expense',
                'account_subtype' => 'operating_expense',
                'description' => 'Fire, theft, liability, and inventory insurance premiums',
                'is_system_account' => false,
                'parent_code' => '6000',
            ],
            [
                'account_code' => '6130',
                'account_name' => 'Legal, Audit & Professional Fees',
                'account_type' => 'expense',
                'account_subtype' => 'operating_expense',
                'description' => 'External auditor, lawyer, tax consultant fees',
                'is_system_account' => false,
                'parent_code' => '6000',
            ],
            [
                'account_code' => '6140',
                'account_name' => 'Internet, Phone & Communication',
                'account_type' => 'expense',
                'account_subtype' => 'operating_expense',
                'description' => 'Broadband fiber, mobile bills, SMS gateway notification fees',
                'is_system_account' => false,
                'parent_code' => '6000',
            ],
            [
                'account_code' => '6150',
                'account_name' => 'Bad Debts Expense',
                'account_type' => 'expense',
                'account_subtype' => 'operating_expense',
                'description' => 'Irrecoverable customer account write-offs',
                'is_system_account' => false,
                'parent_code' => '6000',
            ],
            [
                'account_code' => '6160',
                'account_name' => 'Travel, Fuel & Logistics Conveyance',
                'account_type' => 'expense',
                'account_subtype' => 'operating_expense',
                'description' => 'Vehicle fuel, courier charges, staff travel allowances',
                'is_system_account' => false,
                'parent_code' => '6000',
            ],
            [
                'account_code' => '6170',
                'account_name' => 'Meals & Staff Refreshments',
                'account_type' => 'expense',
                'account_subtype' => 'operating_expense',
                'description' => 'Tea, coffee, staff lunches, client entertainment',
                'is_system_account' => false,
                'parent_code' => '6000',
            ],
            [
                'account_code' => '6180',
                'account_name' => 'Janitorial & Cleaning Expenses',
                'account_type' => 'expense',
                'account_subtype' => 'operating_expense',
                'description' => 'Cleaning supplies, sanitizers, pest control services',
                'is_system_account' => false,
                'parent_code' => '6000',
            ],
            [
                'account_code' => '6190',
                'account_name' => 'Bank Transaction & Account Fees',
                'account_type' => 'expense',
                'account_subtype' => 'operating_expense',
                'description' => 'Bank monthly ledger fees, cheque book costs, transfer charges',
                'is_system_account' => false,
                'parent_code' => '6000',
            ],
            [
                'account_code' => '6200',
                'account_name' => 'Miscellaneous Operating Expenses',
                'account_type' => 'expense',
                'account_subtype' => 'other_expense',
                'description' => 'Unclassified minor store and office operational expenses',
                'is_system_account' => false,
                'parent_code' => '6000',
            ],
        ];
    }

    /**
     * Ensure default accounts are completely populated for a given company.
     * Works for both newly registered users and pre-existing users/companies!
     */
    public static function ensureDefaultAccountsForCompany(int $companyId): void
    {
        if (!$companyId) {
            return;
        }

        $definitions = static::getStandardAccountDefinitions();

        // Load existing accounts for this company indexed by account_code
        $existingAccounts = Account::withoutGlobalScope(CompanyScope::class)
            ->where('company_id', $companyId)
            ->get()
            ->keyBy('account_code');

        // Pass 1: Ensure parent accounts (parent_code === null) are created first
        foreach ($definitions as $def) {
            if ($def['parent_code'] !== null) {
                continue;
            }

            if (!$existingAccounts->has($def['account_code'])) {
                $account = Account::create([
                    'company_id'        => $companyId,
                    'account_code'      => $def['account_code'],
                    'account_name'      => $def['account_name'],
                    'account_type'      => $def['account_type'],
                    'account_subtype'   => $def['account_subtype'],
                    'description'      => $def['description'],
                    'is_active'         => true,
                    'is_system_account' => $def['is_system_account'],
                    'opening_balance'   => 0.00,
                    'current_balance'   => 0.00,
                    'parent_account_id' => null,
                ]);

                $existingAccounts->put($def['account_code'], $account);
            }
        }

        // Pass 2: Ensure child accounts are created and attached to their parent IDs
        foreach ($definitions as $def) {
            if ($def['parent_code'] === null) {
                continue;
            }

            if (!$existingAccounts->has($def['account_code'])) {
                $parentAccount = $existingAccounts->get($def['parent_code']);

                $account = Account::create([
                    'company_id'        => $companyId,
                    'account_code'      => $def['account_code'],
                    'account_name'      => $def['account_name'],
                    'account_type'      => $def['account_type'],
                    'account_subtype'   => $def['account_subtype'],
                    'description'      => $def['description'],
                    'is_active'         => true,
                    'is_system_account' => $def['is_system_account'],
                    'opening_balance'   => 0.00,
                    'current_balance'   => 0.00,
                    'parent_account_id' => $parentAccount?->id,
                ]);

                $existingAccounts->put($def['account_code'], $account);
            }
        }

        // Auto-configure accounting settings mappings for this company if unconfigured
        static::ensureAccountingSettingsForCompany($companyId, $existingAccounts);

        // Ensure expense categories in expense module are created for COA expense type accounts
        static::ensureExpenseCategoriesForCompany($companyId);
    }

    /**
     * Seed or update default accounts for ALL active companies in the system.
     */
    public static function seedAllCompanies(): void
    {
        $companies = Company::pluck('id');
        foreach ($companies as $companyId) {
            static::ensureDefaultAccountsForCompany($companyId);
        }
    }

    /**
     * Link default accounting system account IDs in accounting_settings table.
     */
    protected static function ensureAccountingSettingsForCompany(int $companyId, $accountsByCode): void
    {
        $settings = AccountingSetting::withoutGlobalScope(CompanyScope::class)->where('company_id', $companyId)->first();

        if (!$settings) {
            $settings = AccountingSetting::create([
                'company_id' => $companyId,
            ]);
        }

        $codeMap = [
            'sales_invoice_revenue_account_id'    => '4010',
            'sales_invoice_receivable_account_id' => '1030',
            'sales_invoice_tax_account_id'        => '2020',
            'sales_return_revenue_account_id'     => '4060',
            'sales_return_receivable_account_id'  => '1030',
            'sales_return_tax_account_id'         => '2020',
            'purchase_invoice_expense_account_id' => '5010',
            'purchase_invoice_payable_account_id' => '2010',
            'purchase_invoice_tax_account_id'     => '1070',
            'purchase_return_expense_account_id'  => '5010',
            'purchase_return_payable_account_id'  => '2010',
            'purchase_return_tax_account_id'      => '1070',
            'expense_default_account_id'          => '6000',
            'expense_payable_account_id'          => '2010',
            'cash_account_id'                     => '1010',
            'bank_account_id'                     => '1020',
            'inventory_asset_account_id'          => '1040',
            'cost_of_goods_sold_account_id'       => '5010',
        ];

        $updates = [];
        foreach ($codeMap as $settingField => $accountCode) {
            if (!$settings->$settingField) {
                $acc = $accountsByCode->get($accountCode);
                if ($acc) {
                    $updates[$settingField] = $acc->id;
                }
            }
        }

        if (!empty($updates)) {
            $settings->update($updates);
        }
    }

    /**
     * Create/sync expense categories in the expense module corresponding to COA expense type accounts.
     */
    public static function ensureExpenseCategoriesForCompany(int $companyId): void
    {
        if (!$companyId) {
            return;
        }

        // Get all expense type accounts for this company
        $expenseAccounts = Account::withoutGlobalScope(CompanyScope::class)
            ->where('company_id', $companyId)
            ->where('account_type', 'expense')
            ->get();

        if ($expenseAccounts->isEmpty()) {
            return;
        }

        // Load existing categories for this company indexed by code or name
        $existingCategories = ExpenseCategory::withoutGlobalScope(CompanyScope::class)
            ->where('company_id', $companyId)
            ->get()
            ->keyBy(function ($cat) {
                return $cat->code ?: $cat->name;
            });

        // Pass 1: Ensure top-level expense categories (parent_account_id is null) are created first
        foreach ($expenseAccounts as $account) {
            if ($account->parent_account_id !== null) {
                continue;
            }

            $key = $account->account_code ?: $account->account_name;
            if (!$existingCategories->has($key)) {
                $category = ExpenseCategory::create([
                    'company_id'         => $companyId,
                    'name'               => $account->account_name,
                    'description'        => $account->description,
                    'code'               => $account->account_code,
                    'is_active'          => true,
                    'parent_category_id' => null,
                ]);

                $existingCategories->put($key, $category);
            }
        }

        // Pass 2: Ensure child expense categories are created and attached to their parent category
        foreach ($expenseAccounts as $account) {
            if ($account->parent_account_id === null) {
                continue;
            }

            $key = $account->account_code ?: $account->account_name;
            if (!$existingCategories->has($key)) {
                $parentAccount = $expenseAccounts->firstWhere('id', $account->parent_account_id);
                $parentCatKey = $parentAccount ? ($parentAccount->account_code ?: $parentAccount->account_name) : null;
                $parentCategory = $parentCatKey ? $existingCategories->get($parentCatKey) : null;

                $category = ExpenseCategory::create([
                    'company_id'         => $companyId,
                    'name'               => $account->account_name,
                    'description'        => $account->description,
                    'code'               => $account->account_code,
                    'is_active'          => true,
                    'parent_category_id' => $parentCategory?->id,
                ]);

                $existingCategories->put($key, $category);
            }
        }
    }
}
