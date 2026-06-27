<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Templates;

use App\Modules\Onboarding\Contracts\TenantTemplate;

class BookstoreTemplate implements TenantTemplate
{
    public function businessType(): string
    {
        return 'bookstore';
    }

    public function defaultCategories(): array
    {
        return [
            ['name' => 'Fiction', 'children' => [
                ['name' => 'Literary Fiction'],
                ['name' => 'Mystery & Thriller'],
                ['name' => 'Science Fiction'],
                ['name' => 'Fantasy'],
                ['name' => 'Romance'],
            ]],
            ['name' => 'Non-Fiction', 'children' => [
                ['name' => 'History'],
                ['name' => 'Science & Technology'],
                ['name' => 'Business & Economics'],
                ['name' => 'Self-Help'],
                ['name' => 'Biography'],
            ]],
            ['name' => 'Educational', 'children' => [
                ['name' => 'Textbooks'],
                ['name' => 'Reference'],
                ['name' => 'Language Learning'],
            ]],
            ['name' => "Children's Books", 'children' => [
                ['name' => 'Picture Books'],
                ['name' => 'Early Readers'],
                ['name' => 'Young Adult'],
            ]],
            ['name' => 'Stationery', 'children' => [
                ['name' => 'Writing Instruments'],
                ['name' => 'Notebooks'],
                ['name' => 'Art Supplies'],
            ]],
        ];
    }

    public function productSchema(): array
    {
        return [
            'fields' => [
                [
                    'slug' => 'author',
                    'label' => 'Author',
                    'type' => 'string',
                    'required' => true,
                    'section' => 'details',
                    'order' => 1,
                ],
                [
                    'slug' => 'isbn',
                    'label' => 'ISBN',
                    'type' => 'string',
                    'required' => true,
                    'section' => 'details',
                    'order' => 2,
                ],
                [
                    'slug' => 'publisher',
                    'label' => 'Publisher',
                    'type' => 'string',
                    'required' => false,
                    'section' => 'details',
                    'order' => 3,
                ],
                [
                    'slug' => 'genre',
                    'label' => 'Genre',
                    'type' => 'select',
                    'required' => true,
                    'section' => 'classification',
                    'order' => 4,
                    'options' => ['Fiction', 'Non-Fiction', 'Educational', "Children's", 'Academic', 'Reference'],
                ],
                [
                    'slug' => 'page_count',
                    'label' => 'Page Count',
                    'type' => 'number',
                    'required' => false,
                    'section' => 'details',
                    'order' => 5,
                ],
                [
                    'slug' => 'language',
                    'label' => 'Language',
                    'type' => 'select',
                    'required' => false,
                    'section' => 'details',
                    'order' => 6,
                    'options' => ['English', 'Arabic', 'French', 'Swahili', 'Amharic', 'Somali', 'Other'],
                ],
                [
                    'slug' => 'edition',
                    'label' => 'Edition',
                    'type' => 'string',
                    'required' => false,
                    'section' => 'details',
                    'order' => 7,
                ],
            ],
            'sections' => [
                'details' => ['title' => 'Book Details', 'order' => 1],
                'classification' => ['title' => 'Classification', 'order' => 2],
            ],
            'search_columns' => ['name', 'author', 'isbn', 'publisher'],
            'list_columns' => ['name', 'author', 'price', 'stock', 'genre'],
        ];
    }

    public function dashboardLayout(): array
    {
        return [
            ['widget' => 'daily_revenue', 'width' => 'half', 'order' => 1],
            ['widget' => 'popular_books', 'width' => 'half', 'order' => 2],
            ['widget' => 'low_stock', 'width' => 'half', 'order' => 3],
            ['widget' => 'top_selling', 'width' => 'full', 'order' => 4],
        ];
    }

    public function posDefaults(): array
    {
        return [
            'layout' => 'grid',
            'has_barcode_scan' => true,
            'tender_types' => ['cash', 'card', 'mobile_banking'],
        ];
    }

    public function defaultTeam(): array
    {
        return [
            'roles' => ['admin', 'manager', 'cashier'],
        ];
    }

    public function notificationDefaults(): array
    {
        return [
            'email_notifications' => true,
            'order_confirmation' => true,
            'low_stock_alerts' => true,
        ];
    }

    public function initialData(): array
    {
        return [];
    }

    public function defaultStores(): array
    {
        return [
            [
                'name' => 'Main Store',
                'slug' => 'main',
                'code' => 'STORE-001',
                'currency' => 'XOF',
                'timezone' => 'Africa/Porto-Novo',
                'is_default' => true,
                'status' => 'active',
            ],
        ];
    }
}
