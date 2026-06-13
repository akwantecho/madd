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
            ['title' => 'Customers', 'metrics' => [
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

    public function exhibitions()
    {
        return view('pages.exhibitions', ['exhibitions' => $this->sampleExhibitions(12)]);
    }

    public function customers()
    {
        $customers = [
            ['name' => 'شركة الواحة التجارية', 'email' => 'info@alwaha.sa', 'phone' => '0551234567', 'company' => 'الواحة', 'bookings' => 8, 'joined' => '2025-01-12'],
            ['name' => 'مؤسسة النخبة', 'email' => 'sales@nukhba.com', 'phone' => '0507654321', 'company' => 'النخبة', 'bookings' => 3, 'joined' => '2025-02-04'],
            ['name' => 'أحمد الغامدي', 'email' => 'ahmad@example.com', 'phone' => '0533219876', 'company' => '—', 'bookings' => 1, 'joined' => '2025-03-21'],
            ['name' => 'Global Events Co.', 'email' => 'contact@globalevents.com', 'phone' => '0561112233', 'company' => 'Global', 'bookings' => 15, 'joined' => '2024-11-30'],
            ['name' => 'سارة المطيري', 'email' => 'sara.m@example.com', 'phone' => '0544455667', 'company' => '—', 'bookings' => 2, 'joined' => '2025-04-09'],
        ];

        return view('pages.customers', compact('customers'));
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

    public function settings()
    {
        return view('pages.settings');
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
