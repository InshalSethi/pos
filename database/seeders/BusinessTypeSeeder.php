<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use App\Models\BusinessType;

class BusinessTypeSeeder extends Seeder
{
    /**
     * Run the database seeds for top 50 business types.
     */
    public function run(): void
    {
        $types = [
            [
                'slug' => 'retail',
                'name' => 'Retail (E-Commerce and Offline)',
                'description' => 'General retail shop, online store, or consumer products outlet',
                'icon' => 'fas fa-shopping-cart',
                'sort_order' => 1,
            ],
            [
                'slug' => 'supermarket_grocery',
                'name' => 'Supermarket & Grocery Store',
                'description' => 'Grocery mart, food store, hypermarket, or neighborhood bodega',
                'icon' => 'fas fa-shopping-basket',
                'sort_order' => 2,
            ],
            [
                'slug' => 'food_services_restaurant',
                'name' => 'Restaurant, Cafe & Fast Food',
                'description' => 'Dining establishments, cafes, fast food outlets, food trucks, and bistros',
                'icon' => 'fas fa-utensils',
                'sort_order' => 3,
            ],
            [
                'slug' => 'pharmacy_medical',
                'name' => 'Pharmacy, Medical & Healthcare',
                'description' => 'Retail pharmacy, medical supply store, clinical practice, and diagnostics',
                'icon' => 'fas fa-notes-medical',
                'sort_order' => 4,
            ],
            [
                'slug' => 'apparel_fashion',
                'name' => 'Apparel & Fashion Boutique',
                'description' => 'Clothing boutique, footwear, fashion accessories, and apparel retail',
                'icon' => 'fas fa-shirt',
                'sort_order' => 5,
            ],
            [
                'slug' => 'hardware_electronics',
                'name' => 'Hardware & Electronics',
                'description' => 'Building materials, hand tools, electrical supplies, and consumer electronics',
                'icon' => 'fas fa-laptop',
                'sort_order' => 6,
            ],
            [
                'slug' => 'wholesale_trade',
                'name' => 'Wholesale Trade & Distribution',
                'description' => 'Bulk supply, B2B trade distribution, import/export, and merchant wholesaling',
                'icon' => 'fas fa-boxes-packing',
                'sort_order' => 7,
            ],
            [
                'slug' => 'construction_trades',
                'name' => 'Construction, Trades & Contracting',
                'description' => 'General contracting, home renovation, civil works, and trade services',
                'icon' => 'fas fa-hammer',
                'sort_order' => 8,
            ],
            [
                'slug' => 'real_estate',
                'name' => 'Real Estate & Property Management',
                'description' => 'Property brokerage, real estate sales, rentals, and asset management',
                'icon' => 'fas fa-building',
                'sort_order' => 9,
            ],
            [
                'slug' => 'software_development',
                'name' => 'Software Development & SaaS',
                'description' => 'Software products, cloud applications, SaaS platforms, and tech startup',
                'icon' => 'fas fa-cubes',
                'sort_order' => 10,
            ],
            [
                'slug' => 'it_services',
                'name' => 'Information Technology & Managed Services',
                'description' => 'IT support, network infrastructure, cybersecurity, and cloud consulting',
                'icon' => 'fas fa-laptop-code',
                'sort_order' => 11,
            ],
            [
                'slug' => 'education_training',
                'name' => 'Education, Schools & Training Academies',
                'description' => 'Private schools, tutoring institutes, vocational academies, and online learning',
                'icon' => 'fas fa-graduation-cap',
                'sort_order' => 12,
            ],
            [
                'slug' => 'financial_insurance',
                'name' => 'Financial Services & Insurance',
                'description' => 'Banking, microfinance, insurance brokers, and wealth management',
                'icon' => 'fas fa-piggy-bank',
                'sort_order' => 13,
            ],
            [
                'slug' => 'accounting_tax',
                'name' => 'Accounting, Auditing & Tax Consulting',
                'description' => 'Bookkeeping, tax compliance, financial auditing, and advisory services',
                'icon' => 'fas fa-calculator',
                'sort_order' => 14,
            ],
            [
                'slug' => 'legal_services',
                'name' => 'Legal Services & Law Firms',
                'description' => 'Corporate law, legal advocacy, notary public, and compliance advisory',
                'icon' => 'fas fa-gavel',
                'sort_order' => 15,
            ],
            [
                'slug' => 'marketing_advertising',
                'name' => 'Marketing, Advertising & PR Agencies',
                'description' => 'Digital marketing, SEO, brand strategy, PR, and advertising campaigns',
                'icon' => 'fas fa-bullhorn',
                'sort_order' => 16,
            ],
            [
                'slug' => 'automotive_repair',
                'name' => 'Auto Repair & Mechanic Shops',
                'description' => 'Car maintenance, engine repair, tire shops, and auto detailing',
                'icon' => 'fas fa-car',
                'sort_order' => 17,
            ],
            [
                'slug' => 'auto_dealership',
                'name' => 'Automobile Dealership & Car Rental',
                'description' => 'Vehicle sales showroom, used car dealership, and vehicle rental fleet',
                'icon' => 'fas fa-car-side',
                'sort_order' => 18,
            ],
            [
                'slug' => 'beauty_salon_spa',
                'name' => 'Beauty Salon, Barber & Spa',
                'description' => 'Hair salons, barbershops, skin care clinics, and wellness spas',
                'icon' => 'fas fa-cut',
                'sort_order' => 19,
            ],
            [
                'slug' => 'fitness_gym',
                'name' => 'Fitness Center, Gym & Personal Training',
                'description' => 'Health clubs, gyms, crossfit studios, yoga centers, and personal trainers',
                'icon' => 'fas fa-dumbbell',
                'sort_order' => 20,
            ],
            [
                'slug' => 'hospitality_hotel',
                'name' => 'Hotels, Lodging & Resorts',
                'description' => 'Hotels, guest houses, boutique resorts, and vacation rental management',
                'icon' => 'fas fa-hotel',
                'sort_order' => 21,
            ],
            [
                'slug' => 'travel_tourism',
                'name' => 'Travel Agency & Tour Operator',
                'description' => 'Ticketing, tour packages, visa services, and travel logistics',
                'icon' => 'fas fa-plane',
                'sort_order' => 22,
            ],
            [
                'slug' => 'logistics_freight',
                'name' => 'Logistics, Shipping & Freight Transport',
                'description' => 'Cargo transportation, freight forwarding, trucking, and supply chain logistics',
                'icon' => 'fas fa-truck-fast',
                'sort_order' => 23,
            ],
            [
                'slug' => 'courier_delivery',
                'name' => 'Courier & Last-Mile Delivery Services',
                'description' => 'Express parcel delivery, postal services, and last-mile dispatching',
                'icon' => 'fas fa-box',
                'sort_order' => 24,
            ],
            [
                'slug' => 'manufacturing',
                'name' => 'Manufacturing & Factory Production',
                'description' => 'Industrial manufacturing, OEM production, processing, and assembly lines',
                'icon' => 'fas fa-industry',
                'sort_order' => 25,
            ],
            [
                'slug' => 'agriculture_farming',
                'name' => 'Agriculture, Farming & Crop Production',
                'description' => 'Crop farming, greenhouses, grain production, and agricultural equipment',
                'icon' => 'fas fa-seedling',
                'sort_order' => 26,
            ],
            [
                'slug' => 'livestock_poultry',
                'name' => 'Livestock, Poultry & Dairy Farming',
                'description' => 'Cattle farming, poultry production, dairy processing, and animal feed',
                'icon' => 'fas fa-cow',
                'sort_order' => 27,
            ],
            [
                'slug' => 'art_design',
                'name' => 'Art, Graphic Design & Creative Studios',
                'description' => 'Graphic design, branding, interior decoration, and studio artwork',
                'icon' => 'fas fa-palette',
                'sort_order' => 28,
            ],
            [
                'slug' => 'photography_videography',
                'name' => 'Photography & Videography Studios',
                'description' => 'Commercial photography, video production, studio rentals, and editing',
                'icon' => 'fas fa-camera',
                'sort_order' => 29,
            ],
            [
                'slug' => 'event_management',
                'name' => 'Event Planning & Catering Services',
                'description' => 'Wedding planning, corporate events, party equipment hire, and catering',
                'icon' => 'fas fa-calendar-check',
                'sort_order' => 30,
            ],
            [
                'slug' => 'hr_staffing',
                'name' => 'Human Resources, Recruitment & Staffing',
                'description' => 'Executive search, manpower recruitment, staffing solutions, and payroll outsourcing',
                'icon' => 'fas fa-users-gear',
                'sort_order' => 31,
            ],
            [
                'slug' => 'cleaning_janitorial',
                'name' => 'Cleaning, Janitorial & Sanitation Services',
                'description' => 'Commercial cleaning, residential janitorial, carpet care, and pest control',
                'icon' => 'fas fa-broom',
                'sort_order' => 32,
            ],
            [
                'slug' => 'security_services',
                'name' => 'Security & Surveillance Services',
                'description' => 'Manned guarding, CCTV installations, alarm monitoring, and risk management',
                'icon' => 'fas fa-shield-halved',
                'sort_order' => 33,
            ],
            [
                'slug' => 'landscaping_gardening',
                'name' => 'Landscaping & Lawn Care Services',
                'description' => 'Garden design, lawn maintenance, tree care, and irrigation systems',
                'icon' => 'fas fa-leaf',
                'sort_order' => 34,
            ],
            [
                'slug' => 'plumbing_hvac',
                'name' => 'Plumbing, Heating & Air Conditioning (HVAC)',
                'description' => 'HVAC installation, duct cleaning, plumbing repair, and climate control',
                'icon' => 'fas fa-wrench',
                'sort_order' => 35,
            ],
            [
                'slug' => 'electrical_services',
                'name' => 'Electrical Contracting & Solar Installation',
                'description' => 'Electrician services, solar panel installation, wiring, and power solutions',
                'icon' => 'fas fa-bolt',
                'sort_order' => 36,
            ],
            [
                'slug' => 'bakery_confectionery',
                'name' => 'Bakery, Pastry & Confectionery Shop',
                'description' => 'Artisanal bakeries, cake shops, sweet marts, and chocolate makers',
                'icon' => 'fas fa-bread-slice',
                'sort_order' => 37,
            ],
            [
                'slug' => 'jewelry_watches',
                'name' => 'Jewelry, Gemstones & Watch Retail',
                'description' => 'Gold and silver jewelry, diamond merchants, luxury watch sales, and repair',
                'icon' => 'fas fa-gem',
                'sort_order' => 38,
            ],
            [
                'slug' => 'furniture_home_decor',
                'name' => 'Furniture, Interior & Home Decor',
                'description' => 'Furniture showrooms, home furnishings, lighting, and decor accessories',
                'icon' => 'fas fa-couch',
                'sort_order' => 39,
            ],
            [
                'slug' => 'mobile_accessories',
                'name' => 'Mobile Shops & Accessories Repair',
                'description' => 'Smartphone retail, mobile repair centers, and gadget accessories',
                'icon' => 'fas fa-mobile-screen-button',
                'sort_order' => 40,
            ],
            [
                'slug' => 'optics_eyewear',
                'name' => 'Optical Store & Eyewear Retail',
                'description' => 'Prescription glasses, designer sunglasses, contact lenses, and eye exams',
                'icon' => 'fas fa-glasses',
                'sort_order' => 41,
            ],
            [
                'slug' => 'stationery_bookstore',
                'name' => 'Stationery, Printing & Bookstore',
                'description' => 'Office stationery, book shops, digital printing, and copy centers',
                'icon' => 'fas fa-book',
                'sort_order' => 42,
            ],
            [
                'slug' => 'pet_clinic_supplies',
                'name' => 'Pet Shop, Grooming & Veterinary Clinic',
                'description' => 'Pet food supplies, animal grooming salons, and veterinary care',
                'icon' => 'fas fa-dog',
                'sort_order' => 43,
            ],
            [
                'slug' => 'laundry_dry_cleaning',
                'name' => 'Laundry & Dry Cleaning Services',
                'description' => 'Laundromat, dry cleaning, garment pressing, and commercial laundry',
                'icon' => 'fas fa-shirt',
                'sort_order' => 44,
            ],
            [
                'slug' => 'warehouse_storage',
                'name' => 'Warehousing, Storage & Cold Logistics',
                'description' => 'Self-storage facilities, commercial warehousing, and temperature-controlled storage',
                'icon' => 'fas fa-warehouse',
                'sort_order' => 45,
            ],
            [
                'slug' => 'renewable_energy',
                'name' => 'Renewable Energy & Solar Solutions',
                'description' => 'Solar power systems, wind energy equipment, and green technology',
                'icon' => 'fas fa-solar-panel',
                'sort_order' => 46,
            ],
            [
                'slug' => 'publishing_media',
                'name' => 'Media, Publishing & Content Creation',
                'description' => 'Magazines, digital news portals, podcast production, and publishing',
                'icon' => 'fas fa-newspaper',
                'sort_order' => 47,
            ],
            [
                'slug' => 'non_profit',
                'name' => 'Non-Profit, NGO & Charitable Organization',
                'description' => 'Community welfare, non-profit foundations, charities, and social causes',
                'icon' => 'fas fa-hands-holding-circle',
                'sort_order' => 48,
            ],
            [
                'slug' => 'development_programming',
                'name' => 'Custom App & Web Development',
                'description' => 'Web applications, API integrations, enterprise software development',
                'icon' => 'fas fa-code',
                'sort_order' => 49,
            ],
            [
                'slug' => 'other',
                'name' => 'General Business / Other Services',
                'description' => 'Miscellaneous business activities and specialized services',
                'icon' => 'fas fa-ellipsis-h',
                'sort_order' => 50,
            ],
        ];

        // Clear existing business types to ensure clean 50 items
        Schema::disableForeignKeyConstraints();
        BusinessType::truncate();
        Schema::enableForeignKeyConstraints();

        foreach ($types as $type) {
            BusinessType::create([
                'slug' => $type['slug'],
                'name' => $type['name'],
                'description' => $type['description'],
                'icon' => $type['icon'],
                'is_active' => true,
                'sort_order' => $type['sort_order'],
            ]);
        }
    }
}
