<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Seed initial subscription plans into database.
     */
    public function run(): void
    {
        $plans = [
            [
                'name'                  => 'Standard',
                'slug'                  => 'standard',
                'description'           => 'Free trial 14 days. 1 user & 1 company allowed.',
                'monthly_price'         => 0.00,
                'yearly_price'          => 0.00,
                'trial_days'            => 14,
                'max_companies'         => 1,
                'max_users_per_company' => 1,
                'is_popular'            => false,
                'is_custom'             => false,
                'is_active'             => true,
                'sort_order'            => 1,
                'features'              => [
                    '14-Day Free Trial',
                    '1 User Limit',
                    '1 Company Allowed',
                    'Essential POS Features',
                ],
            ],
            [
                'name'                  => 'Basic',
                'slug'                  => 'basic',
                'description'           => '$20/month. 1 user & 1 company allowed.',
                'monthly_price'         => 20.00,
                'yearly_price'          => 192.00,
                'trial_days'            => 0,
                'max_companies'         => 1,
                'max_users_per_company' => 1,
                'is_popular'            => false,
                'is_custom'             => false,
                'is_active'             => true,
                'sort_order'            => 2,
                'features'              => [
                    '1 User Limit',
                    '1 Company Allowed',
                    'Inventory & Sales',
                    'Standard Support',
                ],
            ],
            [
                'name'                  => 'Advance',
                'slug'                  => 'advance',
                'description'           => '$50/month. 20 users & 2 companies allowed (20 users each).',
                'monthly_price'         => 50.00,
                'yearly_price'          => 480.00,
                'trial_days'            => 0,
                'max_companies'         => 2,
                'max_users_per_company' => 20,
                'is_popular'            => true,
                'is_custom'             => false,
                'is_active'             => true,
                'sort_order'            => 3,
                'features'              => [
                    '2 Companies Allowed',
                    '20 Users per Company',
                    'Advanced Accounting & Analytics',
                    'Multi-Warehouse Access',
                ],
            ],
            [
                'name'                  => 'Enterprise',
                'slug'                  => 'enterprise',
                'description'           => '$100/month. 10 companies each allowing 100 users.',
                'monthly_price'         => 100.00,
                'yearly_price'          => 960.00,
                'trial_days'            => 0,
                'max_companies'         => 10,
                'max_users_per_company' => 100,
                'is_popular'            => false,
                'is_custom'             => false,
                'is_active'             => true,
                'sort_order'            => 4,
                'features'              => [
                    '10 Companies Allowed',
                    '100 Users per Company',
                    'Priority Support & SLA',
                    'Full System Access',
                ],
            ],
            [
                'name'                  => 'Custom',
                'slug'                  => 'custom',
                'description'           => 'Contact sales team for better pricing & bespoke scaling.',
                'monthly_price'         => 0.00,
                'yearly_price'          => 0.00,
                'trial_days'            => 0,
                'max_companies'         => 999,
                'max_users_per_company' => 999,
                'is_popular'            => false,
                'is_custom'             => true,
                'is_active'             => true,
                'sort_order'            => 5,
                'features'              => [
                    'Contact Sales Team',
                    'Bespoke Deployment',
                    'Unlimited Scaling',
                    'Dedicated Account Manager',
                ],
            ],
        ];

        foreach ($plans as $planData) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $planData['slug']],
                $planData
            );
        }
    }
}
