<?php

namespace App\Http\Controllers;

class PageController extends Controller
{
    /**
     * Dashboard — summary stats, recent exhibitions and activity.
     * NOTE: all data here is placeholder for the frontend phase.
     */
    public function dashboard()
    {
        $cards = [
            ['title' => 'Exhibitions', 'metrics' => [
                ['label' => 'Total', 'value' => '48', 'icon' => 'bi-easel2', 'change' => '+12%', 'dir' => 'up'],
                ['label' => 'Active', 'value' => '9', 'icon' => 'bi-broadcast', 'change' => '+3%', 'dir' => 'up'],
            ]],
            ['title' => 'Contacts', 'metrics' => [
                ['label' => 'Total', 'value' => '1,254', 'icon' => 'bi-people', 'change' => '+8%', 'dir' => 'up'],
                ['label' => 'New', 'value' => '37', 'icon' => 'bi-person-plus', 'change' => '+5%', 'dir' => 'up'],
            ]],
            ['title' => 'Revenue', 'metrics' => [
                ['label' => 'Collected', 'value' => '324K', 'icon' => 'bi-cash-stack', 'change' => '+18%', 'dir' => 'up'],
                ['label' => 'Pending', 'value' => '45K', 'icon' => 'bi-hourglass-split', 'change' => '-2%', 'dir' => 'down'],
            ]],
        ];

        $recent = $this->sampleExhibitions(8);

        $activity = [
            ['text' => 'تم تسجيل عميل جديد: شركة الواحة', 'time' => 'قبل 10 دقائق', 'icon' => 'bi-person-plus', 'color' => 'success'],
            ['text' => 'فاتورة #1042 تم دفعها', 'time' => 'قبل 35 دقيقة', 'icon' => 'bi-cash', 'color' => 'primary'],
            ['text' => 'معرض "تك إكسبو" انتهى', 'time' => 'قبل ساعتين', 'icon' => 'bi-flag', 'color' => 'secondary'],
            ['text' => 'حجز جديد لمعرض "الرياض للكتاب"', 'time' => 'قبل 3 ساعات', 'icon' => 'bi-ticket-perforated', 'color' => 'info'],
        ];

        return view('pages.dashboard', compact('cards', 'recent', 'activity'));
    }

    /**
     * Tasks — cross-system task list (also surfaced inside each exhibition).
     */
    public function tasks()
    {
        $tasks = [
            ['title' => 'تجهيز جناح العميل A', 'exhibition' => 'معرض الرياض للكتاب', 'assignee' => 'خالد', 'due' => '2025-06-12', 'priority' => 'High', 'status' => 'Active'],
            ['title' => 'تأكيد عقد التوريد', 'exhibition' => 'تك إكسبو السعودية', 'assignee' => 'سارة', 'due' => '2025-06-15', 'priority' => 'Medium', 'status' => 'Upcoming'],
            ['title' => 'استلام الأجهزة من المورد', 'exhibition' => 'معرض الأثاث الدولي', 'assignee' => 'أحمد', 'due' => '2025-05-20', 'priority' => 'High', 'status' => 'Completed'],
            ['title' => 'إصدار فاتورة الخدمات', 'exhibition' => 'بينالي الفنون', 'assignee' => 'منى', 'due' => '2025-06-18', 'priority' => 'Low', 'status' => 'Upcoming'],
            ['title' => 'مراجعة مصروفات التشغيل', 'exhibition' => 'معرض السيارات', 'assignee' => 'خالد', 'due' => '2025-06-09', 'priority' => 'Medium', 'status' => 'Active'],
        ];

        return view('pages.tasks', compact('tasks'));
    }

    public function exhibitions()
    {
        return view('pages.exhibitions', ['exhibitions' => $this->sampleExhibitions(12)]);
    }

    /**
     * Exhibition detail — tabbed view: summary, documents, stock, expenses,
     * setup (operations) and tasks. Driven by ?tab= query param.
     */
    public function exhibitionShow(string $id)
    {
        $all = $this->sampleExhibitions(12);
        $index = max(0, ((int) $id - 1)) % count($all);
        $exhibition = $all[$index];
        $exhibition['id'] = $id;

        $tabs = [
            'summary'   => ['label' => 'Summary',   'icon' => 'bi-clipboard-data'],
            'documents' => ['label' => 'Documents', 'icon' => 'bi-folder2-open'],
            'stock'     => ['label' => 'Stock',     'icon' => 'bi-box-seam'],
            'expenses'  => ['label' => 'Expenses',  'icon' => 'bi-wallet2'],
            'setup'     => ['label' => 'Setup',     'icon' => 'bi-tools'],
            'tasks'     => ['label' => 'Tasks',     'icon' => 'bi-list-check'],
        ];

        $active = request('tab', 'summary');
        if (! array_key_exists($active, $tabs)) {
            $active = 'summary';
        }

        $summary = [
            ['key' => 'Revenue',  'value' => '120,000 ر.س', 'icon' => 'bi-cash-stack',   'color' => 'green'],
            ['key' => 'Expenses', 'value' => '42,500 ر.س',  'icon' => 'bi-wallet2',      'color' => 'red'],
            ['key' => 'Net Profit', 'value' => '77,500 ر.س', 'icon' => 'bi-graph-up-arrow', 'color' => 'brand'],
            ['key' => 'Tasks',    'value' => '8 / 12',      'icon' => 'bi-list-check',   'color' => 'blue'],
        ];

        $documents = [
            ['title' => 'عقد العميل.pdf', 'type' => 'PDF', 'size' => '240 KB', 'date' => '2025-05-10'],
            ['title' => 'مخطط الجناح.dwg', 'type' => 'DWG', 'size' => '1.2 MB', 'date' => '2025-05-12'],
            ['title' => 'فاتورة التوريد.pdf', 'type' => 'PDF', 'size' => '180 KB', 'date' => '2025-05-18'],
        ];

        $stockItems = [
            ['name' => 'شاشة عرض 55"', 'category' => 'أجهزة', 'qty' => 6, 'status' => 'Active'],
            ['name' => 'طاولة استقبال', 'category' => 'معدات', 'qty' => 4, 'status' => 'Active'],
            ['name' => 'خدمة تركيب', 'category' => 'خدمات', 'qty' => 1, 'status' => 'Upcoming'],
        ];

        $expenses = [
            ['item' => 'تأجير أجهزة', 'vendor' => 'تك سبلاي', 'amount' => '18,000 ر.س', 'date' => '2025-05-15'],
            ['item' => 'تجهيز الجناح', 'vendor' => 'بناء برو', 'amount' => '15,500 ر.س', 'date' => '2025-05-16'],
            ['item' => 'خدمات ضيافة', 'vendor' => 'الذواقة', 'amount' => '9,000 ر.س', 'date' => '2025-05-17'],
        ];

        $setup = [
            ['step' => 'استلام الموقع', 'owner' => 'أحمد', 'date' => '2025-06-08', 'status' => 'Completed'],
            ['step' => 'تركيب الأجهزة', 'owner' => 'خالد', 'date' => '2025-06-09', 'status' => 'Active'],
            ['step' => 'الاختبار النهائي', 'owner' => 'سارة', 'date' => '2025-06-10', 'status' => 'Upcoming'],
        ];

        $tasks = [
            ['title' => 'تجهيز جناح العميل A', 'assignee' => 'خالد', 'due' => '2025-06-12', 'priority' => 'High', 'status' => 'Active'],
            ['title' => 'تأكيد عقد التوريد', 'assignee' => 'سارة', 'due' => '2025-06-15', 'priority' => 'Medium', 'status' => 'Upcoming'],
            ['title' => 'استلام الأجهزة', 'assignee' => 'أحمد', 'due' => '2025-06-07', 'priority' => 'High', 'status' => 'Completed'],
        ];

        return view('pages.exhibition-show', compact(
            'exhibition', 'tabs', 'active', 'summary',
            'documents', 'stockItems', 'expenses', 'setup', 'tasks'
        ));
    }

    /**
     * Contacts — entities, clients, organizers and suppliers. The active
     * directory is chosen via the ?type= query param.
     */
    public function contacts()
    {
        $types = [
            'entities'   => ['label' => 'Entities',   'icon' => 'bi-building'],
            'clients'    => ['label' => 'Clients',    'icon' => 'bi-person-badge'],
            'organizers' => ['label' => 'Organizers', 'icon' => 'bi-people'],
            'suppliers'  => ['label' => 'Suppliers',  'icon' => 'bi-truck'],
        ];

        $active = request('type', 'entities');
        if (! array_key_exists($active, $types)) {
            $active = 'entities';
        }

        $directories = [
            'entities' => [
                'add' => 'Add Entity',
                'columns' => ['Entity Name', 'Contact Numbers', 'Email', 'Representative', 'Persons'],
                'rows' => [
                    ['name' => 'هيئة المعارض', 'phone' => '0112233445', 'email' => 'info@expo.gov.sa', 'rep' => 'م. فهد', 'persons' => 5, 'status' => 'Active'],
                    ['name' => 'غرفة الرياض', 'phone' => '0114455667', 'email' => 'contact@riyadhchamber.sa', 'rep' => 'أ. نورة', 'persons' => 3, 'status' => 'Active'],
                    ['name' => 'مركز المؤتمرات', 'phone' => '0126677889', 'email' => 'hello@conv.sa', 'rep' => 'أ. سعد', 'persons' => 2, 'status' => 'Upcoming'],
                ],
            ],
            'clients' => [
                'add' => 'Add Client',
                'columns' => ['Client Name', 'Phone', 'Entity', 'Email', 'Bookings'],
                'rows' => [
                    ['name' => 'شركة الواحة التجارية', 'phone' => '0551234567', 'entity' => 'هيئة المعارض', 'email' => 'info@alwaha.sa', 'bookings' => 8, 'status' => 'Active'],
                    ['name' => 'مؤسسة النخبة', 'phone' => '0507654321', 'entity' => 'غرفة الرياض', 'email' => 'sales@nukhba.com', 'bookings' => 3, 'status' => 'Active'],
                    ['name' => 'Global Events Co.', 'phone' => '0561112233', 'entity' => 'مركز المؤتمرات', 'email' => 'contact@globalevents.com', 'bookings' => 15, 'status' => 'Active'],
                ],
            ],
            'organizers' => [
                'add' => 'Add Organizer',
                'columns' => ['Organizer Name', 'Phone', 'Email', 'Events', 'Status'],
                'rows' => [
                    ['name' => 'فريق التنظيم الذهبي', 'phone' => '0533219876', 'email' => 'team@golden.sa', 'events' => 12, 'status' => 'Active'],
                    ['name' => 'إيفنت ماسترز', 'phone' => '0544455667', 'email' => 'ops@eventmasters.sa', 'events' => 7, 'status' => 'Active'],
                ],
            ],
            'suppliers' => [
                'add' => 'Add Supplier',
                'columns' => ['Supplier Name', 'Phone', 'Email', 'Category', 'Status'],
                'rows' => [
                    ['name' => 'تك سبلاي', 'phone' => '0590001122', 'email' => 'sales@techsupply.sa', 'category' => 'أجهزة', 'status' => 'Active'],
                    ['name' => 'بناء برو', 'phone' => '0591122334', 'email' => 'info@buildpro.sa', 'category' => 'تجهيزات', 'status' => 'Active'],
                    ['name' => 'الذواقة للضيافة', 'phone' => '0592233445', 'email' => 'order@catering.sa', 'category' => 'خدمات', 'status' => 'Upcoming'],
                ],
            ],
        ];

        return view('pages.contacts', compact('types', 'active', 'directories'));
    }

    public function finance()
    {
        $summary = [
            ['key' => 'Total Revenue', 'value' => '324,500 ر.س', 'icon' => 'bi-cash-stack', 'color' => 'green'],
            ['key' => 'Expenses', 'value' => '98,200 ر.س', 'icon' => 'bi-wallet2', 'color' => 'red'],
            ['key' => 'Net Profit', 'value' => '226,300 ر.س', 'icon' => 'bi-graph-up-arrow', 'color' => 'brand'],
            ['key' => 'Pending Payments', 'value' => '45,000 ر.س', 'icon' => 'bi-hourglass-split', 'color' => 'amber'],
        ];

        $invoices = [
            ['no' => 'INV-1042', 'customer' => 'شركة الواحة التجارية', 'amount' => '12,500 ر.س', 'date' => '2025-05-28', 'status' => 'Paid'],
            ['no' => 'INV-1041', 'customer' => 'Global Events Co.', 'amount' => '34,000 ر.س', 'date' => '2025-05-25', 'status' => 'Unpaid'],
            ['no' => 'INV-1040', 'customer' => 'مؤسسة النخبة', 'amount' => '8,750 ر.س', 'date' => '2025-05-20', 'status' => 'Paid'],
            ['no' => 'INV-1039', 'customer' => 'أحمد الغامدي', 'amount' => '2,300 ر.س', 'date' => '2025-05-12', 'status' => 'Overdue'],
            ['no' => 'INV-1038', 'customer' => 'سارة المطيري', 'amount' => '5,100 ر.س', 'date' => '2025-05-08', 'status' => 'Paid'],
        ];

        return view('pages.finance', compact('summary', 'invoices'));
    }

    /**
     * Contracts & Invoices — create contracts / invoices and track them.
     */
    public function contracts()
    {
        $contracts = [
            ['no' => 'CT-2051', 'client' => 'شركة الواحة التجارية', 'exhibition' => 'معرض الرياض للكتاب', 'value' => '120,000 ر.س', 'date' => '2025-05-01', 'status' => 'Active'],
            ['no' => 'CT-2050', 'client' => 'Global Events Co.', 'exhibition' => 'تك إكسبو السعودية', 'value' => '210,000 ر.س', 'date' => '2025-04-22', 'status' => 'Upcoming'],
            ['no' => 'CT-2049', 'client' => 'مؤسسة النخبة', 'exhibition' => 'معرض الأثاث الدولي', 'value' => '64,000 ر.س', 'date' => '2025-03-18', 'status' => 'Completed'],
        ];

        $invoices = [
            ['no' => 'INV-1042', 'client' => 'شركة الواحة التجارية', 'contract' => 'CT-2051', 'amount' => '12,500 ر.س', 'date' => '2025-05-28', 'status' => 'Paid'],
            ['no' => 'INV-1041', 'client' => 'Global Events Co.', 'contract' => 'CT-2050', 'amount' => '34,000 ر.س', 'date' => '2025-05-25', 'status' => 'Unpaid'],
            ['no' => 'INV-1040', 'client' => 'مؤسسة النخبة', 'contract' => 'CT-2049', 'amount' => '8,750 ر.س', 'date' => '2025-05-20', 'status' => 'Paid'],
        ];

        return view('pages.contracts', compact('contracts', 'invoices'));
    }

    /**
     * Stock — devices & equipment and services.
     */
    public function stock()
    {
        $types = [
            'equipment' => ['label' => 'Devices & Equipment', 'icon' => 'bi-pc-display'],
            'services'  => ['label' => 'Services',            'icon' => 'bi-tools'],
        ];

        $active = request('type', 'equipment');
        if (! array_key_exists($active, $types)) {
            $active = 'equipment';
        }

        $equipment = [
            ['name' => 'شاشة عرض 55"', 'sku' => 'SCR-55', 'qty' => 24, 'available' => 18, 'status' => 'Active'],
            ['name' => 'طاولة استقبال', 'sku' => 'TBL-01', 'qty' => 40, 'available' => 32, 'status' => 'Active'],
            ['name' => 'نظام صوت', 'sku' => 'AUD-09', 'qty' => 12, 'available' => 5, 'status' => 'Upcoming'],
            ['name' => 'إضاءة LED', 'sku' => 'LED-22', 'qty' => 80, 'available' => 0, 'status' => 'Completed'],
        ];

        $services = [
            ['name' => 'خدمة التركيب', 'unit' => 'لكل جناح', 'price' => '1,500 ر.س', 'status' => 'Active'],
            ['name' => 'خدمة الضيافة', 'unit' => 'لكل يوم', 'price' => '3,000 ر.س', 'status' => 'Active'],
            ['name' => 'خدمة الأمن', 'unit' => 'لكل وردية', 'price' => '900 ر.س', 'status' => 'Active'],
        ];

        return view('pages.stock', compact('types', 'active', 'equipment', 'services'));
    }

    /**
     * "This is Oman" — standalone public documentary-series landing page.
     * Self-contained view (does not use the admin shell); episode/season data
     * is driven client-side by public/oman/oman-data.js.
     */
    public function oman()
    {
        return view('pages.oman');
    }

    public function archive()
    {
        $items = [
            ['title' => 'معرض الرياض للكتاب 2024', 'type' => 'Exhibition', 'date' => '2024-12-15'],
            ['title' => 'العميل: شركة المستقبل (محذوف)', 'type' => 'Customer', 'date' => '2024-11-02'],
            ['title' => 'فاتورة INV-0921', 'type' => 'Invoice', 'date' => '2024-10-19'],
            ['title' => 'معرض تك إكسبو 2024', 'type' => 'Exhibition', 'date' => '2024-09-30'],
        ];

        return view('pages.archive', compact('items'));
    }

    /**
     * Data — master/reference data hub linking the directory sections.
     */
    public function data()
    {
        $sections = [
            ['title' => 'Entities', 'desc' => 'الجهات والممثلين', 'icon' => 'bi-building', 'color' => 'blue', 'count' => '32', 'route' => 'contacts', 'param' => ['type' => 'entities']],
            ['title' => 'Clients', 'desc' => 'العملاء وبيانات التواصل', 'icon' => 'bi-person-badge', 'color' => 'green', 'count' => '1,254', 'route' => 'contacts', 'param' => ['type' => 'clients']],
            ['title' => 'Organizers', 'desc' => 'فرق التنظيم', 'icon' => 'bi-people', 'color' => 'brand', 'count' => '18', 'route' => 'contacts', 'param' => ['type' => 'organizers']],
            ['title' => 'Suppliers', 'desc' => 'الموردين والفئات', 'icon' => 'bi-truck', 'color' => 'amber', 'count' => '47', 'route' => 'contacts', 'param' => ['type' => 'suppliers']],
            ['title' => 'Devices & Equipment', 'desc' => 'مخزون الأجهزة والمعدات', 'icon' => 'bi-pc-display', 'color' => 'blue', 'count' => '156', 'route' => 'stock', 'param' => ['type' => 'equipment']],
            ['title' => 'Services', 'desc' => 'الخدمات المتاحة', 'icon' => 'bi-tools', 'color' => 'green', 'count' => '12', 'route' => 'stock', 'param' => ['type' => 'services']],
        ];

        return view('pages.data', compact('sections'));
    }

    public function settings()
    {
        $users = [
            ['name' => 'Admin', 'email' => 'admin@eventpuls.sa', 'role' => 'Administrator', 'status' => 'Active'],
            ['name' => 'سارة المطيري', 'email' => 'sara@eventpuls.sa', 'role' => 'Manager', 'status' => 'Active'],
            ['name' => 'خالد الحربي', 'email' => 'khaled@eventpuls.sa', 'role' => 'Operator', 'status' => 'Active'],
            ['name' => 'منى العتيبي', 'email' => 'mona@eventpuls.sa', 'role' => 'Accountant', 'status' => 'Upcoming'],
        ];

        $roles = ['Administrator', 'Manager', 'Operator', 'Accountant', 'Viewer'];

        return view('pages.settings', compact('users', 'roles'));
    }

    /**
     * Reports — analytical summaries across the system. Placeholder data for
     * the frontend phase: KPI overview + a catalog of available reports.
     */
    public function reports()
    {
        $summary = [
            ['key' => 'Total Revenue',  'value' => '324,500 ر.س', 'icon' => 'bi-cash-stack',     'color' => 'green', 'change' => '+18%', 'dir' => 'up'],
            ['key' => 'Exhibitions',    'value' => '48',          'icon' => 'bi-easel2',         'color' => 'brand', 'change' => '+12%', 'dir' => 'up'],
            ['key' => 'Net Profit',     'value' => '226,300 ر.س', 'icon' => 'bi-graph-up-arrow', 'color' => 'blue',  'change' => '+9%',  'dir' => 'up'],
            ['key' => 'Pending Payments', 'value' => '45,000 ر.س', 'icon' => 'bi-hourglass-split', 'color' => 'amber', 'change' => '-2%', 'dir' => 'down'],
        ];

        $reports = [
            ['title' => 'Financial Report',   'desc' => 'الإيرادات والمصروفات وصافي الربح', 'icon' => 'bi-wallet2',           'color' => 'green', 'route' => 'finance'],
            ['title' => 'Exhibitions Report', 'desc' => 'أداء المعارض والإشغال',            'icon' => 'bi-easel2',            'color' => 'brand', 'route' => 'exhibitions'],
            ['title' => 'Contacts Report',    'desc' => 'العملاء والجهات والموردين',         'icon' => 'bi-person-rolodex',    'color' => 'blue',  'route' => 'contacts'],
            ['title' => 'Inventory Report',   'desc' => 'حركة المخزون والمعدات',            'icon' => 'bi-box-seam',          'color' => 'amber', 'route' => 'stock'],
            ['title' => 'Contracts Report',   'desc' => 'العقود والفواتير المصدرة',          'icon' => 'bi-file-earmark-text', 'color' => 'red',   'route' => 'contracts'],
            ['title' => 'Tasks Report',       'desc' => 'إنجاز المهام والأداء التشغيلي',     'icon' => 'bi-list-check',        'color' => 'gray',  'route' => 'tasks'],
        ];

        $recent = [
            ['title' => 'تقرير مالي — مايو 2025', 'type' => 'Financial Report', 'date' => '2025-06-01', 'format' => 'PDF'],
            ['title' => 'تقرير المعارض — الربع الثاني', 'type' => 'Exhibitions Report', 'date' => '2025-05-28', 'format' => 'XLSX'],
            ['title' => 'تقرير المخزون — مايو 2025', 'type' => 'Inventory Report', 'date' => '2025-05-20', 'format' => 'PDF'],
            ['title' => 'تقرير العملاء — أبريل 2025', 'type' => 'Contacts Report', 'date' => '2025-05-02', 'format' => 'CSV'],
        ];

        return view('pages.reports', compact('summary', 'reports', 'recent'));
    }

    /**
     * Sample exhibitions used across the dashboard and listing page.
     */
    private function sampleExhibitions(int $count): array
    {
        $data = [
            ['title' => 'معرض الرياض للكتاب', 'location' => 'الرياض', 'start' => '2025-06-10', 'end' => '2025-06-20', 'status' => 'Active', 'tag' => 'High demand', 'tagColor' => 'blue'],
            ['title' => 'تك إكسبو السعودية', 'location' => 'جدة', 'start' => '2025-07-01', 'end' => '2025-07-05', 'status' => 'Upcoming', 'tag' => 'Featured', 'tagColor' => 'green'],
            ['title' => 'معرض الأثاث الدولي', 'location' => 'الدمام', 'start' => '2025-05-15', 'end' => '2025-05-22', 'status' => 'Completed', 'tag' => 'Sold out', 'tagColor' => 'gray'],
            ['title' => 'بينالي الفنون', 'location' => 'الرياض', 'start' => '2025-08-12', 'end' => '2025-08-30', 'status' => 'Upcoming', 'tag' => 'Featured', 'tagColor' => 'green'],
            ['title' => 'معرض السيارات', 'location' => 'جدة', 'start' => '2025-06-05', 'end' => '2025-06-09', 'status' => 'Active', 'tag' => 'High demand', 'tagColor' => 'blue'],
            ['title' => 'إكسبو العقار', 'location' => 'الرياض', 'start' => '2025-04-01', 'end' => '2025-04-04', 'status' => 'Cancelled', 'tag' => 'Needs review', 'tagColor' => 'red'],
            ['title' => 'معرض الأغذية والمشروبات', 'location' => 'الخبر', 'start' => '2025-09-10', 'end' => '2025-09-14', 'status' => 'Upcoming', 'tag' => 'New', 'tagColor' => 'amber'],
            ['title' => 'ملتقى ريادة الأعمال', 'location' => 'الرياض', 'start' => '2025-05-28', 'end' => '2025-05-30', 'status' => 'Completed', 'tag' => 'Sold out', 'tagColor' => 'gray'],
            ['title' => 'معرض الصحة العالمي', 'location' => 'جدة', 'start' => '2025-06-18', 'end' => '2025-06-25', 'status' => 'Active', 'tag' => 'High demand', 'tagColor' => 'blue'],
            ['title' => 'معرض التعليم الدولي', 'location' => 'مكة', 'start' => '2025-10-01', 'end' => '2025-10-06', 'status' => 'Upcoming', 'tag' => 'New', 'tagColor' => 'amber'],
            ['title' => 'معرض السياحة', 'location' => 'العلا', 'start' => '2025-03-12', 'end' => '2025-03-18', 'status' => 'Completed', 'tag' => 'Sold out', 'tagColor' => 'gray'],
            ['title' => 'معرض التقنية المالية', 'location' => 'الرياض', 'start' => '2025-11-02', 'end' => '2025-11-05', 'status' => 'Upcoming', 'tag' => 'Featured', 'tagColor' => 'green'],
        ];

        return array_slice($data, 0, $count);
    }
}
