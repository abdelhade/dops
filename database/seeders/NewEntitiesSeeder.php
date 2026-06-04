<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Material;
use App\Models\PaperType;
use App\Models\Service;
use App\Models\Stage;
use Illuminate\Database\Seeder;

class NewEntitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed Clients
        $clients = [
            ['name' => 'دار الهلال للنشر والتوزيع', 'phone' => '01012345678', 'email' => 'contact@alhilal.com', 'address' => 'وسط البلد، القاهرة', 'notes' => 'عميل طباعة كتب ومجلات دورية.'],
            ['name' => 'مكتبة الإسكندرية', 'phone' => '034830000', 'email' => 'info@bibalex.org', 'address' => 'الشاطبي، الإسكندرية', 'notes' => 'عميل طباعة وترميم وثائق.'],
            ['name' => 'مطبعة الأمل للدعاية والإعلان', 'phone' => '01298765432', 'email' => 'sales@alamalprint.com', 'address' => 'المنطقة الصناعية، العبور', 'notes' => 'عميل تجاري لطلبات التعهيد.'],
        ];
        foreach ($clients as $client) {
            Client::firstOrCreate(['name' => $client['name']], $client);
        }

        // Seed Materials
        $materials = [
            ['name' => 'غراء تجليد حراري (حار)', 'code' => 'MAT-GLU-HOT', 'unit' => 'كيلو جرام', 'price' => 150.00, 'stock' => 50, 'description' => 'حبيبات غراء تجليد تستخدم في ماكينات التجليد الحراري.'],
            ['name' => 'سلك حلزوني بلاستيك 10 مم', 'code' => 'MAT-COIL-10P', 'unit' => 'علبة (100 قطعة)', 'price' => 180.00, 'stock' => 20, 'description' => 'أسلاك بلاستيكية لتجليد الدفاتر والكتيبات.'],
            ['name' => 'كرتون مقوى رمادي 2 مم', 'code' => 'MAT-BRD-GRAY2', 'unit' => 'فرخ', 'price' => 25.00, 'stock' => 500, 'description' => 'كرتون مقوى لتصنيع أغلفة الكتب الفاخرة (هاردكفر).'],
        ];
        foreach ($materials as $material) {
            Material::firstOrCreate(['name' => $material['name']], $material);
        }

        // Seed Paper Types
        $paperTypes = [
            ['name' => 'كوشيه لامع (Glossy Coated)', 'weight_gsm' => 150, 'finish' => 'لامع (Glossy)', 'description' => 'ورق مصقول لامع مناسب للمجلات والبروشورات.'],
            ['name' => 'أوفست أبيض (Offset Uncoated)', 'weight_gsm' => 80, 'finish' => 'غير مصقول (Matte)', 'description' => 'ورق عادي للطباعة المكتبية والكتب المدرسية.'],
            ['name' => 'مكربن (Carbonless Paper)', 'weight_gsm' => 60, 'finish' => 'مكربن لصورة كربونية', 'description' => 'ورق مخصص لدفاتر الفواتير والإيصالات متعددة الطبقات.'],
        ];
        foreach ($paperTypes as $pt) {
            PaperType::firstOrCreate(['name' => $pt['name']], $pt);
        }

        // Seed Services
        $services = [
            ['name' => 'تصميم فني وتجهيز زنكات', 'price' => 300.00, 'description' => 'خدمة تجهيز التصميم وضبط الفرز الفني قبل الطباعة.'],
            ['name' => 'تجليد فاخر بغلاف مقوى (Hardcover)', 'price' => 75.00, 'description' => 'تجليد فني يدوي للكتب باستخدام الكرتون المقوى والجلد أو القماش.'],
            ['name' => 'سلوفان مطفي وجه واحد', 'price' => 1.50, 'description' => 'تغطية الفرخ المطبوع بطبقة سلوفان لحمايته وإعطائه مظهراً فاخراً.'],
        ];
        foreach ($services as $service) {
            Service::firstOrCreate(['name' => $service['name']], $service);
        }

        // Seed Stages
        $stages = [
            ['name' => 'التصميم وما قبل الطباعة (Pre-press)', 'sort_order' => 1, 'description' => 'مرحلة التصميم والمراجعة الفنية وتجهيز اللوحات (الزنكات).'],
            ['name' => 'الطباعة التشغيلية (Press / Printing)', 'sort_order' => 2, 'description' => 'مرحلة طباعة المحتوى على ماكينات الأوفست أو الديجيتال.'],
            ['name' => 'التجهيز والتشطيب (Finishing & Binding)', 'sort_order' => 3, 'description' => 'مراحل القص، التجميع، التجليد، السلوفان والريجة.'],
            ['name' => 'مراقبة الجودة والتسليم', 'sort_order' => 4, 'description' => 'المراجعة النهائية والتغليف للشحن أو تسليم العميل.'],
        ];
        foreach ($stages as $stage) {
            Stage::firstOrCreate(['name' => $stage['name']], $stage);
        }

    }
}
