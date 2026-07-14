<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Contract;
use App\Models\Document;
use App\Models\Exhibition;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Revenue;
use App\Models\SetupStep;
use App\Models\StockItem;
use App\Models\Task;
use Illuminate\Database\Seeder;

/**
 * Optional sample data for demos / local exploration.
 * Run with: php artisan db:seed --class=DemoSeeder
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $exhibitions = $this->seedExhibitions();
        $contacts = $this->seedContacts();
        $accounts = $this->seedAccounts();
        $this->seedStock();
        $this->seedTasks($exhibitions);
        $this->seedContracts($contacts, $exhibitions);
        $this->seedInvoices($contacts);
        $this->seedExhibitionRecords($exhibitions, $accounts);
    }

    private function seedExhibitions(): array
    {
        $data = [
            ['title' => 'معرض الرياض للكتاب', 'location' => 'الرياض', 'start_date' => '2025-06-10', 'end_date' => '2025-06-20', 'status' => 'Active', 'tag' => 'High demand', 'tag_color' => 'blue'],
            ['title' => 'تك إكسبو السعودية', 'location' => 'جدة', 'start_date' => '2025-07-01', 'end_date' => '2025-07-05', 'status' => 'Upcoming', 'tag' => 'Featured', 'tag_color' => 'green'],
            ['title' => 'معرض الأثاث الدولي', 'location' => 'الدمام', 'start_date' => '2025-05-15', 'end_date' => '2025-05-22', 'status' => 'Completed', 'tag' => 'Sold out', 'tag_color' => 'gray'],
            ['title' => 'بينالي الفنون', 'location' => 'الرياض', 'start_date' => '2025-08-12', 'end_date' => '2025-08-30', 'status' => 'Upcoming', 'tag' => 'Featured', 'tag_color' => 'green'],
            ['title' => 'معرض السيارات', 'location' => 'جدة', 'start_date' => '2025-06-05', 'end_date' => '2025-06-09', 'status' => 'Active', 'tag' => 'High demand', 'tag_color' => 'blue'],
            ['title' => 'إكسبو العقار', 'location' => 'الرياض', 'start_date' => '2025-04-01', 'end_date' => '2025-04-04', 'status' => 'Cancelled', 'tag' => 'Needs review', 'tag_color' => 'red'],
            ['title' => 'معرض الأغذية والمشروبات', 'location' => 'الخبر', 'start_date' => '2025-09-10', 'end_date' => '2025-09-14', 'status' => 'Upcoming', 'tag' => 'New', 'tag_color' => 'amber'],
            ['title' => 'ملتقى ريادة الأعمال', 'location' => 'الرياض', 'start_date' => '2025-05-28', 'end_date' => '2025-05-30', 'status' => 'Completed', 'tag' => 'Sold out', 'tag_color' => 'gray'],
            ['title' => 'معرض الصحة العالمي', 'location' => 'جدة', 'start_date' => '2025-06-18', 'end_date' => '2025-06-25', 'status' => 'Active', 'tag' => 'High demand', 'tag_color' => 'blue'],
            ['title' => 'معرض التعليم الدولي', 'location' => 'مكة', 'start_date' => '2025-10-01', 'end_date' => '2025-10-06', 'status' => 'Upcoming', 'tag' => 'New', 'tag_color' => 'amber'],
            ['title' => 'معرض السياحة', 'location' => 'العلا', 'start_date' => '2025-03-12', 'end_date' => '2025-03-18', 'status' => 'Completed', 'tag' => 'Sold out', 'tag_color' => 'gray'],
            ['title' => 'معرض التقنية المالية', 'location' => 'الرياض', 'start_date' => '2025-11-02', 'end_date' => '2025-11-05', 'status' => 'Upcoming', 'tag' => 'Featured', 'tag_color' => 'green'],
        ];

        $out = [];
        foreach ($data as $row) {
            $out[] = Exhibition::create($row);
        }

        return $out;
    }

    private function seedContacts(): array
    {
        $entities = [
            ['name' => 'هيئة المعارض', 'phone' => '0112233445', 'email' => 'info@expo.gov.sa', 'representative' => 'م. فهد', 'persons' => 5, 'status' => 'Active'],
            ['name' => 'غرفة الرياض', 'phone' => '0114455667', 'email' => 'contact@riyadhchamber.sa', 'representative' => 'أ. نورة', 'persons' => 3, 'status' => 'Active'],
            ['name' => 'مركز المؤتمرات', 'phone' => '0126677889', 'email' => 'hello@conv.sa', 'representative' => 'أ. سعد', 'persons' => 2, 'status' => 'Upcoming'],
        ];
        $entityModels = [];
        foreach ($entities as $e) {
            $entityModels[] = Contact::create($e + ['type' => 'entity']);
        }

        $clients = [
            ['name' => 'شركة الواحة التجارية', 'phone' => '0551234567', 'email' => 'info@alwaha.sa', 'entity_id' => $entityModels[0]->id, 'representative' => 'أ. نورة العتيبي', 'vat_no' => '31099887700003', 'address' => 'طريق الملك فهد، حي العليا، الرياض', 'status' => 'Active'],
            ['name' => 'مؤسسة النخبة', 'phone' => '0507654321', 'email' => 'sales@nukhba.com', 'entity_id' => $entityModels[1]->id, 'representative' => 'أ. سعد القحطاني', 'vat_no' => '30088776600002', 'address' => 'شارع التحلية، جدة', 'status' => 'Active'],
            ['name' => 'Global Events Co.', 'phone' => '0561112233', 'email' => 'contact@globalevents.com', 'entity_id' => $entityModels[2]->id, 'representative' => 'M. Adam', 'vat_no' => '31077665500001', 'address' => 'King Abdullah Rd, Riyadh', 'status' => 'Active'],
        ];
        $clientModels = [];
        foreach ($clients as $c) {
            $clientModels[] = Contact::create($c + ['type' => 'client']);
        }

        $organizers = [
            ['name' => 'فريق التنظيم الذهبي', 'phone' => '0533219876', 'email' => 'team@golden.sa', 'events' => 12, 'status' => 'Active'],
            ['name' => 'إيفنت ماسترز', 'phone' => '0544455667', 'email' => 'ops@eventmasters.sa', 'events' => 7, 'status' => 'Active'],
        ];
        foreach ($organizers as $o) {
            Contact::create($o + ['type' => 'organizer']);
        }

        $suppliers = [
            ['name' => 'تك سبلاي', 'phone' => '0590001122', 'email' => 'sales@techsupply.sa', 'category' => 'أجهزة', 'status' => 'Active'],
            ['name' => 'بناء برو', 'phone' => '0591122334', 'email' => 'info@buildpro.sa', 'category' => 'تجهيزات', 'status' => 'Active'],
            ['name' => 'الذواقة للضيافة', 'phone' => '0592233445', 'email' => 'order@catering.sa', 'category' => 'خدمات', 'status' => 'Upcoming'],
        ];
        foreach ($suppliers as $s) {
            Contact::create($s + ['type' => 'supplier']);
        }

        return ['clients' => $clientModels, 'entities' => $entityModels];
    }

    private function seedAccounts(): array
    {
        $accounts = [
            ['name' => 'Bank Account', 'icon' => 'bi-bank'],
            ['name' => 'Petty Cash', 'icon' => 'bi-cash-stack'],
            ['name' => 'Treasury', 'icon' => 'bi-safe2'],
        ];
        $out = [];
        foreach ($accounts as $a) {
            $out[] = Account::create($a);
        }

        return $out;
    }

    private function seedStock(): void
    {
        $equipment = [
            ['name' => 'شاشة عرض 55"', 'sku' => 'SCR-55', 'quantity' => 24, 'available' => 18, 'status' => 'Active'],
            ['name' => 'طاولة استقبال', 'sku' => 'TBL-01', 'quantity' => 40, 'available' => 32, 'status' => 'Active'],
            ['name' => 'نظام صوت', 'sku' => 'AUD-09', 'quantity' => 12, 'available' => 5, 'status' => 'Upcoming'],
            ['name' => 'إضاءة LED', 'sku' => 'LED-22', 'quantity' => 80, 'available' => 0, 'status' => 'Completed'],
        ];
        foreach ($equipment as $e) {
            StockItem::create($e + ['type' => 'equipment']);
        }

        $services = [
            ['name' => 'خدمة التركيب', 'unit' => 'لكل جناح', 'price' => 1500, 'status' => 'Active'],
            ['name' => 'خدمة الضيافة', 'unit' => 'لكل يوم', 'price' => 3000, 'status' => 'Active'],
            ['name' => 'خدمة الأمن', 'unit' => 'لكل وردية', 'price' => 900, 'status' => 'Active'],
        ];
        foreach ($services as $s) {
            StockItem::create($s + ['type' => 'service']);
        }
    }

    private function seedTasks(array $exhibitions): void
    {
        $byTitle = collect($exhibitions)->keyBy('title');
        $tasks = [
            ['title' => 'تجهيز جناح العميل A', 'exhibition' => 'معرض الرياض للكتاب', 'assignee' => 'خالد', 'due_date' => '2025-06-12', 'priority' => 'High', 'status' => 'Active'],
            ['title' => 'تأكيد عقد التوريد', 'exhibition' => 'تك إكسبو السعودية', 'assignee' => 'سارة', 'due_date' => '2025-06-15', 'priority' => 'Medium', 'status' => 'Upcoming'],
            ['title' => 'استلام الأجهزة من المورد', 'exhibition' => 'معرض الأثاث الدولي', 'assignee' => 'أحمد', 'due_date' => '2025-05-20', 'priority' => 'High', 'status' => 'Completed'],
            ['title' => 'إصدار فاتورة الخدمات', 'exhibition' => 'بينالي الفنون', 'assignee' => 'منى', 'due_date' => '2025-06-18', 'priority' => 'Low', 'status' => 'Upcoming'],
            ['title' => 'مراجعة مصروفات التشغيل', 'exhibition' => 'معرض السيارات', 'assignee' => 'خالد', 'due_date' => '2025-06-09', 'priority' => 'Medium', 'status' => 'Active'],
        ];
        foreach ($tasks as $t) {
            Task::create([
                'title' => $t['title'],
                'exhibition_id' => $byTitle->get($t['exhibition'])?->id,
                'assignee' => $t['assignee'],
                'due_date' => $t['due_date'],
                'priority' => $t['priority'],
                'status' => $t['status'],
            ]);
        }
    }

    private function seedContracts(array $contacts, array $exhibitions): void
    {
        $clients = collect($contacts['clients'])->keyBy('name');
        $exh = collect($exhibitions)->keyBy('title');

        $contracts = [
            [
                'number' => 'CT-2051', 'client' => 'شركة الواحة التجارية', 'exhibition' => 'معرض الرياض للكتاب',
                'type' => 'عقد خدمات', 'start_date' => '2025-06-01', 'end_date' => '2025-08-31', 'status' => 'Active',
                'items' => [
                    ['description' => 'تجهيز وتأجير جناح العميل', 'qty' => 1, 'price' => 80000],
                    ['description' => 'خدمات تشغيل وإشراف ميداني', 'qty' => 1, 'price' => 40000],
                ],
                'schedules' => [
                    ['description' => 'دفعة مقدمة عند التوقيع', 'percent' => 50, 'due_date' => '2025-06-01'],
                    ['description' => 'دفعة عند اكتمال التجهيز', 'percent' => 30, 'due_date' => '2025-07-15'],
                    ['description' => 'دفعة نهائية عند التسليم', 'percent' => 20, 'due_date' => '2025-08-31'],
                ],
                'terms' => [
                    'تبدأ مدة العقد من تاريخ البداية وتنتهي بتاريخ النهاية المذكورين أعلاه.',
                    'تلتزم المؤسسة بتنفيذ بنود العقد وفق الجدول الزمني والمواصفات المتفق عليها.',
                    'يُسدَّد المقابل المالي وفق جدول الدفعات الموضّح في هذا العقد.',
                    'في حال إلغاء العقد بعد التوقيع تُستحق الدفعة المقدمة كاملةً غير قابلة للاسترداد.',
                ],
            ],
            [
                'number' => 'CT-2050', 'client' => 'Global Events Co.', 'exhibition' => 'تك إكسبو السعودية',
                'type' => 'عقد رعاية', 'start_date' => '2025-04-22', 'end_date' => '2025-07-05', 'status' => 'Upcoming',
                'items' => [
                    ['description' => 'رعاية ماسية', 'qty' => 1, 'price' => 150000],
                    ['description' => 'مساحة عرض إضافية', 'qty' => 2, 'price' => 18000],
                ],
                'schedules' => [
                    ['description' => 'دفعة عند التوقيع', 'percent' => 60, 'due_date' => '2025-04-22'],
                    ['description' => 'دفعة نهائية', 'percent' => 40, 'due_date' => '2025-07-01'],
                ],
                'terms' => [
                    'تشمل الرعاية إبراز شعار الراعي في كل المواد الدعائية للمعرض.',
                    'يلتزم الراعي بسداد الدفعات وفق الجدول المتفق عليه.',
                ],
            ],
            [
                'number' => 'CT-2049', 'client' => 'مؤسسة النخبة', 'exhibition' => 'معرض الأثاث الدولي',
                'type' => 'عقد تأجير جناح', 'start_date' => '2025-03-18', 'end_date' => '2025-05-22', 'status' => 'Completed',
                'items' => [
                    ['description' => 'تأجير جناح (24م²)', 'qty' => 1, 'price' => 56000],
                ],
                'schedules' => [
                    ['description' => 'دفعة كاملة', 'percent' => 100, 'due_date' => '2025-03-18'],
                ],
                'terms' => [
                    'يُسلَّم الجناح جاهزاً قبل افتتاح المعرض بيومين.',
                ],
            ],
        ];

        foreach ($contracts as $row) {
            $contract = Contract::create([
                'number' => $row['number'],
                'client_id' => $clients->get($row['client'])?->id,
                'exhibition_id' => $exh->get($row['exhibition'])?->id,
                'type' => $row['type'],
                'currency' => 'ر.س',
                'start_date' => $row['start_date'],
                'end_date' => $row['end_date'],
                'status' => $row['status'],
                'vat_rate' => 15,
            ]);

            foreach ($row['items'] as $i => $item) {
                $contract->items()->create($item + ['position' => $i]);
            }
            foreach ($row['schedules'] as $i => $sched) {
                $contract->schedules()->create($sched + ['position' => $i]);
            }
            foreach ($row['terms'] as $i => $body) {
                $contract->terms()->create(['body' => $body, 'position' => $i]);
            }
        }
    }

    private function seedInvoices(array $contacts): void
    {
        $clients = collect($contacts['clients'])->keyBy('name');
        $contracts = Contract::query()->pluck('id', 'number');

        $invoices = [
            [
                'number' => 'INV-1042', 'client' => 'شركة الواحة التجارية', 'contract' => 'CT-2051',
                'issue_date' => '2025-05-28', 'due_date' => '2025-06-12', 'status' => 'Paid', 'discount' => 3000, 'paid' => 126500, 'po' => 'PO-2208',
                'items' => [
                    ['description' => 'تجهيز وتأجير جناح العميل (12م²)', 'qty' => 1, 'price' => 80000],
                    ['description' => 'خدمات تشغيل وإشراف ميداني', 'qty' => 3, 'price' => 4000],
                    ['description' => 'شاشات عرض 55"', 'qty' => 6, 'price' => 1500],
                    ['description' => 'خدمة الضيافة (لكل يوم)', 'qty' => 4, 'price' => 3000],
                ],
            ],
            [
                'number' => 'INV-1041', 'client' => 'Global Events Co.', 'contract' => 'CT-2050',
                'issue_date' => '2025-05-25', 'due_date' => '2025-06-25', 'status' => 'Unpaid', 'discount' => 0, 'paid' => 0, 'po' => 'PO-2210',
                'items' => [
                    ['description' => 'رعاية ماسية', 'qty' => 1, 'price' => 150000],
                ],
            ],
            [
                'number' => 'INV-1040', 'client' => 'مؤسسة النخبة', 'contract' => 'CT-2049',
                'issue_date' => '2025-05-20', 'due_date' => '2025-06-04', 'status' => 'Paid', 'discount' => 0, 'paid' => 64400, 'po' => null,
                'items' => [
                    ['description' => 'تأجير جناح (24م²)', 'qty' => 1, 'price' => 56000],
                ],
            ],
        ];

        foreach ($invoices as $row) {
            $invoice = Invoice::create([
                'number' => $row['number'],
                'client_id' => $clients->get($row['client'])?->id,
                'contract_id' => $contracts[$row['contract']] ?? null,
                'currency' => 'ر.س',
                'issue_date' => $row['issue_date'],
                'due_date' => $row['due_date'],
                'status' => $row['status'],
                'vat_rate' => 15,
                'discount' => $row['discount'],
                'paid' => $row['paid'],
                'po' => $row['po'],
            ]);

            foreach ($row['items'] as $i => $item) {
                $invoice->items()->create($item + ['position' => $i]);
            }
        }
    }

    private function seedExhibitionRecords(array $exhibitions, array $accounts): void
    {
        $first = $exhibitions[0];

        foreach ([
            ['title' => 'عقد العميل.pdf', 'type' => 'PDF', 'size' => '240 KB', 'doc_date' => '2025-05-10'],
            ['title' => 'مخطط الجناح.dwg', 'type' => 'DWG', 'size' => '1.2 MB', 'doc_date' => '2025-05-12'],
            ['title' => 'فاتورة التوريد.pdf', 'type' => 'PDF', 'size' => '180 KB', 'doc_date' => '2025-05-18'],
        ] as $d) {
            Document::create($d + ['exhibition_id' => $first->id]);
        }

        foreach ([
            ['item' => 'تأجير أجهزة', 'vendor' => 'تك سبلاي', 'amount' => 18000, 'expense_date' => '2025-05-15', 'status' => 'Paid'],
            ['item' => 'تجهيز الجناح', 'vendor' => 'بناء برو', 'amount' => 15500, 'expense_date' => '2025-05-16', 'status' => 'Paid'],
            ['item' => 'خدمات ضيافة', 'vendor' => 'الذواقة', 'amount' => 9000, 'expense_date' => '2025-05-17', 'status' => 'Unpaid'],
        ] as $e) {
            Expense::create($e + ['exhibition_id' => $first->id]);
        }

        foreach ([
            ['step' => 'استلام الموقع', 'owner' => 'أحمد', 'step_date' => '2025-06-08', 'status' => 'Completed'],
            ['step' => 'تركيب الأجهزة', 'owner' => 'خالد', 'step_date' => '2025-06-09', 'status' => 'Active'],
            ['step' => 'الاختبار النهائي', 'owner' => 'سارة', 'step_date' => '2025-06-10', 'status' => 'Upcoming'],
        ] as $s) {
            SetupStep::create($s + ['exhibition_id' => $first->id]);
        }

        $bank = $accounts[0];
        foreach ([
            ['item' => 'إيجار قاعة المعرض', 'category' => 'تشغيل', 'amount' => 1200, 'expense_date' => '2025-06-01', 'status' => 'Paid'],
            ['item' => 'تجهيزات صوت وإضاءة', 'category' => 'معدات', 'amount' => 850, 'expense_date' => '2025-06-03', 'status' => 'Paid'],
            ['item' => 'خدمات ضيافة', 'category' => 'خدمات', 'amount' => 430, 'expense_date' => '2025-06-05', 'status' => 'Unpaid'],
            ['item' => 'نقل وشحن', 'category' => 'لوجستيات', 'amount' => 275, 'expense_date' => '2025-06-08', 'status' => 'Paid'],
        ] as $e) {
            Expense::create($e + ['account_id' => $bank->id]);
        }

        foreach ([
            ['source' => 'شركة الواحة التجارية', 'reference' => 'INV-1042', 'amount' => 12500, 'revenue_date' => '2025-06-02', 'status' => 'Paid'],
            ['source' => 'Global Events Co.', 'reference' => 'INV-1041', 'amount' => 34000, 'revenue_date' => '2025-06-04', 'status' => 'Unpaid'],
            ['source' => 'مؤسسة النخبة', 'reference' => 'INV-1040', 'amount' => 8750, 'revenue_date' => '2025-06-06', 'status' => 'Paid'],
        ] as $r) {
            Revenue::create($r + ['account_id' => $bank->id]);
        }
    }
}
