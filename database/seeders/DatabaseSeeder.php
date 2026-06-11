<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Supplier;
use App\Models\PaperSize;
use App\Models\Item;
use App\Models\Operation;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(AdminUserSeeder::class);
        $this->call(AppSettingsSeeder::class);
        $this->call(OperationTypeSeeder::class);

        // 1. Create categories
        $categories = [
            ['name' => 'Coated Paper', 'description' => 'Glossy or matte coated paper for premium printing.'],
            ['name' => 'Uncoated Paper', 'description' => 'Standard offset, writing, and photocopy paper.'],
            ['name' => 'Specialty Board', 'description' => 'Thick cardstock, textured board, and packaging materials.'],
            ['name' => 'Inks & Toners', 'description' => 'Offset inks, digital toners, and specialty varnishes.'],
            ['name' => 'Binding & Finishing', 'description' => 'Wires, glue, laminating films, and spiral coils.'],
        ];
        
        foreach ($categories as $cat) {
            Category::create($cat);
        }

        // 2. Create suppliers
        $suppliers = [
            ['name' => 'Global Paper Distributing', 'email' => 'sales@globalpaper.com', 'phone' => '+1-555-0199', 'address' => '100 Industrial Pkwy, Chicago, IL'],
            ['name' => 'Apex Print Supplies Co.', 'email' => 'contact@apexprint.com', 'phone' => '+1-555-0145', 'address' => '450 Commerce Rd, Atlanta, GA'],
            ['name' => 'FineArt Paper Mills', 'email' => 'info@fineartmills.com', 'phone' => '+44-20-7946-0958', 'address' => 'Milford Haven, Wales, UK'],
            ['name' => 'Spectrum Inks Ltd.', 'email' => 'orders@spectruminks.com', 'phone' => '+1-555-0187', 'address' => '12 Inkjet St, Rochester, NY'],
        ];

        foreach ($suppliers as $sup) {
            Supplier::create($sup);
        }

        // 3. Create paper sizes
        $paperSizes = [
            ['name' => 'A4', 'width' => 210.00, 'height' => 297.00],
            ['name' => 'A3', 'width' => 297.00, 'height' => 420.00],
            ['name' => 'A5', 'width' => 148.00, 'height' => 210.00],
            ['name' => 'SRA3', 'width' => 320.00, 'height' => 450.00],
            ['name' => 'Letter', 'width' => 215.90, 'height' => 279.40],
            ['name' => 'Tabloid', 'width' => 279.40, 'height' => 431.80],
        ];

        foreach ($paperSizes as $ps) {
            PaperSize::create($ps);
        }

        // Fetch them to link
        $cats = Category::all();
        $sups = Supplier::all();
        $sizes = PaperSize::all();

        // 4. Create items
        $items = [
            [
                'name' => 'Glossy Coated 150gsm',
                'sku' => 'PAP-GLS-150-A4',
                'description' => '150 gsm glossy art paper, ideal for brochures and flyers.',
                'category_id' => $cats->where('name', 'Coated Paper')->first()->id,
                'supplier_id' => $sups->where('name', 'Global Paper Distributing')->first()->id,
                'paper_size_id' => $sizes->where('name', 'A4')->first()->id,
                'price' => 12.50,
                'stock' => 1500,
            ],
            [
                'name' => 'Matte Coated 300gsm',
                'sku' => 'PAP-MAT-300-SRA3',
                'description' => 'Heavyweight 300 gsm matte paper, ideal for business cards and covers.',
                'category_id' => $cats->where('name', 'Coated Paper')->first()->id,
                'supplier_id' => $sups->where('name', 'Global Paper Distributing')->first()->id,
                'paper_size_id' => $sizes->where('name', 'SRA3')->first()->id,
                'price' => 45.00,
                'stock' => 800,
            ],
            [
                'name' => 'Offset Uncoated 80gsm',
                'sku' => 'PAP-OFF-080-A4',
                'description' => 'Standard photocopy and printing paper.',
                'category_id' => $cats->where('name', 'Uncoated Paper')->first()->id,
                'supplier_id' => $sups->where('name', 'FineArt Paper Mills')->first()->id,
                'paper_size_id' => $sizes->where('name', 'A4')->first()->id,
                'price' => 5.20,
                'stock' => 5000,
            ],
            [
                'name' => 'Textured Linen Card 280gsm',
                'sku' => 'PAP-LIN-280-A3',
                'description' => 'Elegant linen textured cardstock for luxury invitations.',
                'category_id' => $cats->where('name', 'Specialty Board')->first()->id,
                'supplier_id' => $sups->where('name', 'FineArt Paper Mills')->first()->id,
                'paper_size_id' => $sizes->where('name', 'A3')->first()->id,
                'price' => 85.00,
                'stock' => 300,
            ],
            [
                'name' => 'Premium Cyan Ink (Offset)',
                'sku' => 'INK-CYAN-OFF',
                'description' => 'High intensity offset cyan printing ink.',
                'category_id' => $cats->where('name', 'Inks & Toners')->first()->id,
                'supplier_id' => $sups->where('name', 'Spectrum Inks Ltd.')->first()->id,
                'paper_size_id' => null,
                'price' => 28.00,
                'stock' => 120,
            ],
            [
                'name' => 'Black Toner Cartridge X25',
                'sku' => 'TON-BLK-X25',
                'description' => 'Heavy duty black laser toner cartridge.',
                'category_id' => $cats->where('name', 'Inks & Toners')->first()->id,
                'supplier_id' => $sups->where('name', 'Apex Print Supplies Co.')->first()->id,
                'paper_size_id' => null,
                'price' => 110.00,
                'stock' => 45,
            ],
        ];

        foreach ($items as $itemData) {
            Item::create($itemData);
        }

        // 5. Create some operations
        $allCreatedItems = Item::all();
        
        $op1 = Operation::create([
            'operation_number' => 'OP-2026-0001',
            'operation_date' => '2026-06-01',
            'status' => 'Completed',
            'notes' => 'Bulk supply of standard office paper and toner setup.',
            'total_amount' => 0.00, // Will calculate
        ]);

        $op1Items = [
            [
                'item_id' => $allCreatedItems->where('sku', 'PAP-OFF-080-A4')->first()->id,
                'quantity' => 10,
                'unit_price' => 5.20,
                'notes' => '10 boxes of standard A4'
            ],
            [
                'item_id' => $allCreatedItems->where('sku', 'TON-BLK-X25')->first()->id,
                'quantity' => 2,
                'unit_price' => 110.00,
                'notes' => 'Spare black toners'
            ]
        ];

        $total1 = 0;
        foreach ($op1Items as $oItem) {
            $op1->items()->attach($oItem['item_id'], [
                'quantity' => $oItem['quantity'],
                'unit_price' => $oItem['unit_price'],
                'notes' => $oItem['notes']
            ]);
            $total1 += $oItem['quantity'] * $oItem['unit_price'];
        }
        $op1->update(['total_amount' => $total1]);

        $op2 = Operation::create([
            'operation_number' => 'OP-2026-0002',
            'operation_date' => '2026-06-02',
            'status' => 'Processing',
            'notes' => 'Premium brochure print run preparations.',
            'total_amount' => 0.00,
        ]);

        $op2Items = [
            [
                'item_id' => $allCreatedItems->where('sku', 'PAP-GLS-150-A4')->first()->id,
                'quantity' => 5,
                'unit_price' => 12.50,
                'notes' => 'Glossy inner pages'
            ],
            [
                'item_id' => $allCreatedItems->where('sku', 'PAP-MAT-300-SRA3')->first()->id,
                'quantity' => 3,
                'unit_price' => 45.00,
                'notes' => 'Matte covers'
            ],
            [
                'item_id' => $allCreatedItems->where('sku', 'INK-CYAN-OFF')->first()->id,
                'quantity' => 1,
                'unit_price' => 28.00,
                'notes' => 'Extra ink replenishment'
            ]
        ];

        $total2 = 0;
        foreach ($op2Items as $oItem) {
            $op2->items()->attach($oItem['item_id'], [
                'quantity' => $oItem['quantity'],
                'unit_price' => $oItem['unit_price'],
                'notes' => $oItem['notes']
            ]);
            $total2 += $oItem['quantity'] * $oItem['unit_price'];
        }
        $op2->update(['total_amount' => $total2]);

        $this->call(NewEntitiesSeeder::class);
    }
}
