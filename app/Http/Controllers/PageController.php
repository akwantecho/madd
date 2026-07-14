<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Contract;
use App\Models\Exhibition;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Setting;
use App\Models\StockItem;
use App\Models\Subtask;
use App\Models\Task;
use App\Models\TaskAssignee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PageController extends Controller
{
    /** Company issuing the documents (org profile — static for now). */
    private function company(): array
    {
        return [
            'logo' => 'MA',
            'name' => 'مؤسسة محمد عبدالله',
            'address' => 'شارع انس ابن مالك، الرياض',
            'country' => 'المملكة العربية السعودية',
            'vat' => '12014506901345',
            'phone' => '+966 11 456 7890',
            'email' => 'billing@mabdullah.sa',
        ];
    }

    /** Map an Exhibition model to the array shape the views consume. */
    private function exhibitionArray(Exhibition $e): array
    {
        return [
            'id' => $e->id,
            'title' => $e->title,
            'location' => $e->location,
            'start' => optional($e->start_date)->format('Y-m-d'),
            'end' => optional($e->end_date)->format('Y-m-d'),
            'status' => $e->status,
            'tag' => $e->tag,
            'tagColor' => $e->tag_color,
        ];
    }

    private function money($value, string $currency = 'ر.س'): string
    {
        return number_format((float) $value, 0).' '.$currency;
    }

    /**
     * Dashboard — summary stats, recent exhibitions and activity.
     */
    public function dashboard()
    {
        $cards = [
            ['title' => 'Exhibitions', 'metrics' => [
                ['label' => 'Total', 'value' => (string) Exhibition::count(), 'icon' => 'bi-easel2', 'change' => '+12%', 'dir' => 'up'],
                ['label' => 'Active', 'value' => (string) Exhibition::where('status', 'Active')->count(), 'icon' => 'bi-broadcast', 'change' => '+3%', 'dir' => 'up'],
            ]],
            ['title' => 'Contacts', 'metrics' => [
                ['label' => 'Total', 'value' => number_format(Contact::count()), 'icon' => 'bi-people', 'change' => '+8%', 'dir' => 'up'],
                ['label' => 'New', 'value' => (string) Contact::type('client')->count(), 'icon' => 'bi-person-plus', 'change' => '+5%', 'dir' => 'up'],
            ]],
            ['title' => 'Revenue', 'metrics' => [
                ['label' => 'Collected', 'value' => $this->money(Invoice::where('status', 'Paid')->get()->sum('total')), 'icon' => 'bi-cash-stack', 'change' => '+18%', 'dir' => 'up'],
                ['label' => 'Pending', 'value' => $this->money(Invoice::where('status', '!=', 'Paid')->get()->sum('amount_due')), 'icon' => 'bi-hourglass-split', 'change' => '-2%', 'dir' => 'down'],
            ]],
        ];

        $recent = Exhibition::latest('id')->take(8)->get()->map(fn ($e) => $this->exhibitionArray($e))->all();

        $activity = [];

        return view('pages.dashboard', compact('cards', 'recent', 'activity'));
    }

    /** Valid option lists for task fields. */
    private function taskStatuses(): array
    {
        return ['Active', 'Upcoming', 'Completed', 'Cancelled'];
    }

    private function taskPriorities(): array
    {
        return ['High', 'Medium', 'Low', 'Normal'];
    }

    /** Domain status -> board bucket used by the filter tabs. */
    private function taskState(?string $status): string
    {
        return ['Completed' => 'done', 'Active' => 'inprogress', 'Upcoming' => 'todo', 'Cancelled' => 'todo'][$status] ?? 'todo';
    }

    /**
     * Urgency bucket derived from the due date (ignored for done/cancelled tasks).
     * Drives the colour highlighting on the cards: overdue / today / soon.
     */
    private function dueState(Task $t): string
    {
        $due = $t->due_date;

        if (! $due || in_array($t->status, ['Completed', 'Cancelled'], true)) {
            return 'none';
        }

        $today = Carbon::today();

        if ($due->lt($today)) {
            return 'overdue';
        }
        if ($due->isSameDay($today)) {
            return 'today';
        }
        if ($due->lte($today->copy()->addDays(3))) {
            return 'soon';
        }

        return 'upcoming';
    }

    /** Task -> the array shape consumed by the cards / modal (and AJAX). */
    private function taskPayload(Task $t): array
    {
        return [
            'id' => $t->id,
            'title' => $t->title,
            'description' => $t->description,
            'exhibition' => $t->exhibition?->title,
            'exhibition_id' => $t->exhibition_id,
            'assignee' => $t->assignee,
            'due' => optional($t->due_date)->format('Y-m-d'),
            'due_state' => $this->dueState($t),
            'priority' => $t->priority,
            'flagged' => (bool) $t->flagged,
            'status' => $t->status,
            'state' => $this->taskState($t->status),
            'subtasks_total' => (int) ($t->subtasks_count ?? 0),
            'subtasks_done' => (int) ($t->subtasks_done ?? 0),
        ];
    }

    /**
     * Tasks — cross-system task list.
     */
    public function tasks()
    {
        $tasks = Task::with('exhibition')
            ->withCount(['subtasks', 'subtasks as subtasks_done' => fn ($q) => $q->where('done', true)])
            ->orderByRaw("CASE WHEN status IN ('Completed', 'Cancelled') THEN 1 ELSE 0 END")
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->orderByDesc('id')
            ->get()
            ->map(fn ($t) => $this->taskPayload($t))->all();
        $exhibitions = Exhibition::orderBy('title')->pluck('title', 'id')->all();
        $statuses = $this->taskStatuses();
        $priorities = $this->taskPriorities();
        $board = $this->taskBoard($tasks);

        return view('pages.tasks', compact('tasks', 'exhibitions', 'statuses', 'priorities', 'board'));
    }

    /**
     * Build the "Upcoming" board: an Overdue bucket + the next 7 day-columns,
     * each holding the open (not done/cancelled) tasks due that day.
     */
    private function taskBoard(array $tasks): array
    {
        Carbon::setLocale(app()->getLocale());
        $today = Carbon::today();
        $open = collect($tasks)->filter(fn ($t) => $t['due'] && ! in_array($t['status'], ['Completed', 'Cancelled'], true));

        $board = [
            'month' => $today->translatedFormat('F Y'),
            'overdue' => $open->filter(fn ($t) => Carbon::parse($t['due'])->lt($today))->values()->all(),
            'days' => [],
        ];

        for ($i = 0; $i < 7; $i++) {
            $d = $today->copy()->addDays($i);
            $key = $d->format('Y-m-d');
            $board['days'][] = [
                'date' => $key,
                'dnum' => $d->format('j'),
                'mon' => $d->translatedFormat('M'),
                'rel' => $i === 0 ? __('Today') : ($i === 1 ? __('Tomorrow') : $d->translatedFormat('l')),
                'tasks' => $open->filter(fn ($t) => $t['due'] === $key)->values()->all(),
            ];
        }

        return $board;
    }

    /** Create a task (AJAX). */
    public function taskStore(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|in:'.implode(',', $this->taskPriorities()),
            'status' => 'nullable|in:'.implode(',', $this->taskStatuses()),
            'assignee' => 'nullable|string|max:255',
            'exhibition_id' => 'nullable|exists:exhibitions,id',
            'flagged' => 'nullable|boolean',
        ]);

        $task = Task::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'priority' => $data['priority'] ?? 'Medium',
            'status' => $data['status'] ?? 'Upcoming',
            'assignee' => $data['assignee'] ?? null,
            'exhibition_id' => $data['exhibition_id'] ?? null,
            'flagged' => $data['flagged'] ?? false,
        ]);

        if ($request->expectsJson()) {
            return response()->json($this->taskPayload($task->load('exhibition')));
        }

        return redirect()->route('tasks')->with('status', __('Saved successfully.'));
    }

    /** Update a task (AJAX) — accepts partial payloads. */
    public function taskUpdate(Request $request, Task $task)
    {
        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'due_date' => 'sometimes|nullable|date',
            'priority' => 'sometimes|nullable|in:'.implode(',', $this->taskPriorities()),
            'status' => 'sometimes|nullable|in:'.implode(',', $this->taskStatuses()),
            'assignee' => 'sometimes|nullable|string|max:255',
            'exhibition_id' => 'sometimes|nullable|exists:exhibitions,id',
            'flagged' => 'sometimes|boolean',
        ]);

        $task->update($data);

        if ($request->expectsJson()) {
            return response()->json($this->taskPayload($task->load('exhibition')));
        }

        return redirect()->route('tasks')->with('status', __('Saved successfully.'));
    }

    /** Delete a task. */
    public function taskDestroy(Request $request, Task $task)
    {
        $task->delete();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->route('tasks')->with('status', __('Deleted successfully.'));
    }

    /** Assignees + sub-tasks for a task (AJAX). */
    public function taskDetails(Task $task)
    {
        return response()->json([
            'assignees' => $task->assignees()->get(['id', 'name']),
            'subtasks' => $task->subtasks()->get(['id', 'title', 'done']),
        ]);
    }

    public function subtaskStore(Request $request, Task $task)
    {
        $data = $request->validate(['title' => 'required|string|max:255']);
        $subtask = $task->subtasks()->create([
            'title' => $data['title'],
            'position' => (int) $task->subtasks()->max('position') + 1,
        ]);

        return response()->json($subtask->only('id', 'title', 'done'));
    }

    public function subtaskUpdate(Request $request, Subtask $subtask)
    {
        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'done' => 'sometimes|boolean',
        ]);
        $subtask->update($data);

        return response()->json($subtask->only('id', 'title', 'done'));
    }

    public function subtaskDestroy(Subtask $subtask)
    {
        $subtask->delete();

        return response()->json(['ok' => true]);
    }

    public function assigneeStore(Request $request, Task $task)
    {
        $data = $request->validate(['name' => 'required|string|max:255']);
        $assignee = $task->assignees()->create(['name' => $data['name']]);
        $task->syncPrimaryAssignee();

        return response()->json($assignee->only('id', 'name'));
    }

    public function assigneeDestroy(TaskAssignee $assignee)
    {
        $task = $assignee->task;
        $assignee->delete();
        $task?->syncPrimaryAssignee();

        return response()->json(['ok' => true]);
    }

    public function exhibitions()
    {
        $exhibitions = Exhibition::latest('id')->get()->map(fn ($e) => $this->exhibitionArray($e))->all();

        return view('pages.exhibitions', compact('exhibitions'));
    }

    private function exhibitionRules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|in:'.implode(',', $this->taskStatuses()),
            'tag' => 'nullable|string|max:100',
            'tag_color' => 'nullable|in:blue,green,amber,red,gray',
        ];
    }

    public function exhibitionStore(Request $request)
    {
        $data = $request->validate($this->exhibitionRules());
        $data['status'] = $data['status'] ?? 'Upcoming';
        Exhibition::create($data);

        return redirect()->route('exhibitions')->with('status', __('Saved successfully.'));
    }

    public function exhibitionUpdate(Request $request, Exhibition $exhibition)
    {
        $data = $request->validate($this->exhibitionRules());
        $exhibition->update($data);

        return redirect()->route('exhibitions')->with('status', __('Saved successfully.'));
    }

    public function exhibitionDestroy(Exhibition $exhibition)
    {
        $exhibition->delete();

        return redirect()->route('exhibitions')->with('status', __('Deleted successfully.'));
    }

    /**
     * Exhibition detail — tabbed view.
     */
    public function exhibitionShow(string $id)
    {
        $model = Exhibition::with(['documents', 'expenses', 'setupSteps', 'tasks'])->findOrFail($id);
        $exhibition = $this->exhibitionArray($model);

        $tabs = [
            'summary' => ['label' => 'Summary',   'icon' => 'bi-clipboard-data'],
            'documents' => ['label' => 'Documents', 'icon' => 'bi-folder2-open'],
            'stock' => ['label' => 'Stock',     'icon' => 'bi-box-seam'],
            'expenses' => ['label' => 'Expenses',  'icon' => 'bi-wallet2'],
            'setup' => ['label' => 'Setup',     'icon' => 'bi-tools'],
            'tasks' => ['label' => 'Tasks',     'icon' => 'bi-list-check'],
        ];

        $active = request('tab', 'summary');
        if (! array_key_exists($active, $tabs)) {
            $active = 'summary';
        }

        $revenue = $model->invoices()->get()->sum('total');
        $expenseTotal = $model->expenses->sum('amount');

        $summary = [
            ['key' => 'Revenue',    'value' => $this->money($revenue),                'icon' => 'bi-cash-stack',     'color' => 'green'],
            ['key' => 'Expenses',   'value' => $this->money($expenseTotal),           'icon' => 'bi-wallet2',        'color' => 'red'],
            ['key' => 'Net Profit', 'value' => $this->money($revenue - $expenseTotal), 'icon' => 'bi-graph-up-arrow', 'color' => 'brand'],
            ['key' => 'Tasks',      'value' => $model->tasks->where('status', 'Completed')->count().' / '.$model->tasks->count(), 'icon' => 'bi-list-check', 'color' => 'blue'],
        ];

        $documents = $model->documents->map(fn ($d) => [
            'title' => $d->title, 'type' => $d->type, 'size' => $d->size, 'date' => optional($d->doc_date)->format('Y-m-d'),
        ])->all();

        $stockItems = StockItem::latest('id')->take(3)->get()->map(fn ($s) => [
            'name' => $s->name, 'category' => $s->type === 'service' ? 'خدمات' : 'أجهزة', 'qty' => $s->quantity ?? 1, 'status' => $s->status,
        ])->all();

        $expenses = $model->expenses->map(fn ($e) => [
            'item' => $e->item, 'vendor' => $e->vendor, 'amount' => $this->money($e->amount), 'date' => optional($e->expense_date)->format('Y-m-d'),
        ])->all();

        $setup = $model->setupSteps->map(fn ($s) => [
            'step' => $s->step, 'owner' => $s->owner, 'date' => optional($s->step_date)->format('Y-m-d'), 'status' => $s->status,
        ])->all();

        $tasks = $model->tasks->map(fn ($t) => [
            'title' => $t->title, 'assignee' => $t->assignee, 'due' => optional($t->due_date)->format('Y-m-d'), 'priority' => $t->priority, 'status' => $t->status,
        ])->all();

        return view('pages.exhibition-show', compact(
            'exhibition', 'tabs', 'active', 'summary',
            'documents', 'stockItems', 'expenses', 'setup', 'tasks'
        ));
    }

    /**
     * Contacts — entities, clients, organizers and suppliers.
     */
    public function contacts()
    {
        $types = [
            'entities' => ['label' => 'Entities',   'icon' => 'bi-building'],
            'clients' => ['label' => 'Clients',    'icon' => 'bi-person-badge'],
            'organizers' => ['label' => 'Organizers', 'icon' => 'bi-people'],
            'suppliers' => ['label' => 'Suppliers',  'icon' => 'bi-truck'],
        ];

        $active = request('type', 'entities');
        if (! array_key_exists($active, $types)) {
            $active = 'entities';
        }

        $directories = [
            'entities' => [
                'add' => 'Add Entity',
                'columns' => ['Entity Name', 'Contact Numbers', 'Email', 'Representative', 'Persons'],
                'rows' => Contact::type('entity')->orderBy('name')->get()->map(fn ($c) => [
                    'id' => $c->id, 'name' => $c->name, 'phone' => $c->phone, 'email' => $c->email, 'rep' => $c->representative, 'persons' => $c->persons ?? 0, 'status' => $c->status,
                ])->all(),
            ],
            'clients' => [
                'add' => 'Add Client',
                'columns' => ['Client Name', 'Phone', 'Entity', 'Email', 'Bookings'],
                'rows' => Contact::type('client')->with('entity')->withCount('contracts')->orderBy('name')->get()->map(fn ($c) => [
                    'id' => $c->id, 'name' => $c->name, 'phone' => $c->phone, 'entity' => $c->entity?->name, 'entity_id' => $c->entity_id, 'email' => $c->email, 'rep' => $c->representative, 'vat_no' => $c->vat_no, 'address' => $c->address, 'bookings' => $c->contracts_count, 'status' => $c->status,
                ])->all(),
            ],
            'organizers' => [
                'add' => 'Add Organizer',
                'columns' => ['Organizer Name', 'Phone', 'Email', 'Events', 'Status'],
                'rows' => Contact::type('organizer')->orderBy('name')->get()->map(fn ($c) => [
                    'id' => $c->id, 'name' => $c->name, 'phone' => $c->phone, 'email' => $c->email, 'events' => $c->events ?? 0, 'status' => $c->status,
                ])->all(),
            ],
            'suppliers' => [
                'add' => 'Add Supplier',
                'columns' => ['Supplier Name', 'Phone', 'Email', 'Category', 'Status'],
                'rows' => Contact::type('supplier')->orderBy('name')->get()->map(fn ($c) => [
                    'id' => $c->id, 'name' => $c->name, 'phone' => $c->phone, 'email' => $c->email, 'category' => $c->category, 'status' => $c->status,
                ])->all(),
            ],
        ];

        $entities = Contact::type('entity')->orderBy('name')->pluck('name', 'id')->all();
        $contactStatuses = ['Active', 'Upcoming'];

        return view('pages.contacts', compact('types', 'active', 'directories', 'entities', 'contactStatuses'));
    }

    /** Map the contacts tab key -> the stored contact `type`. */
    private function contactType(string $tab): string
    {
        return ['entities' => 'entity', 'clients' => 'client', 'organizers' => 'organizer', 'suppliers' => 'supplier'][$tab] ?? 'entity';
    }

    private function contactRules(): array
    {
        return [
            'type' => 'required|in:entity,client,organizer,supplier',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'status' => 'nullable|in:Active,Upcoming,Completed,Cancelled',
            'representative' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'vat_no' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'entity_id' => 'nullable|exists:contacts,id',
            'persons' => 'nullable|integer|min:0',
            'events' => 'nullable|integer|min:0',
        ];
    }

    public function contactStore(Request $request)
    {
        $data = $request->validate($this->contactRules());
        $data['status'] = $data['status'] ?? 'Active';
        Contact::create($data);

        return redirect()->back()->with('status', __('Saved successfully.'));
    }

    public function contactUpdate(Request $request, Contact $contact)
    {
        $data = $request->validate($this->contactRules());
        $contact->update($data);

        return redirect()->back()->with('status', __('Saved successfully.'));
    }

    public function contactDestroy(Contact $contact)
    {
        $contact->delete();

        return redirect()->back()->with('status', __('Deleted successfully.'));
    }

    public function finance()
    {
        $paid = Invoice::where('status', 'Paid')->get()->sum('total');
        $pending = Invoice::where('status', '!=', 'Paid')->get()->sum('amount_due');
        $expenses = Expense::sum('amount');

        $summary = [
            ['key' => 'Total Revenue',     'value' => $this->money($paid + $pending),   'icon' => 'bi-cash-stack',     'color' => 'green'],
            ['key' => 'Expenses',          'value' => $this->money($expenses),          'icon' => 'bi-wallet2',        'color' => 'red'],
            ['key' => 'Net Profit',        'value' => $this->money($paid + $pending - $expenses), 'icon' => 'bi-graph-up-arrow', 'color' => 'brand'],
            ['key' => 'Pending Payments',  'value' => $this->money($pending),           'icon' => 'bi-hourglass-split', 'color' => 'amber'],
        ];

        $invoices = Invoice::with('client')->latest('id')->get()->map(fn ($i) => [
            'no' => $i->number, 'customer' => $i->client?->name, 'amount' => $this->money($i->total), 'date' => optional($i->issue_date)->format('Y-m-d'), 'status' => $i->status,
        ])->all();

        $accounts = Account::get()->map(fn ($a) => [
            'id' => $a->id, 'name' => $a->name, 'icon' => $a->icon,
            'book' => number_format($a->book_balance, 3), 'statement' => number_format($a->statement_balance, 3),
            'difference' => number_format($a->difference, 3), 'balanced' => $a->balanced,
        ])->all();

        return view('pages.finance', compact('summary', 'invoices', 'accounts'));
    }

    /**
     * Account detail — tabbed expenses / revenues.
     */
    public function accountShow(string $id)
    {
        $model = Account::with(['expenses', 'revenues'])->findOrFail($id);
        $account = [
            'id' => $model->id, 'name' => $model->name, 'icon' => $model->icon,
            'book' => number_format($model->book_balance, 3), 'statement' => number_format($model->statement_balance, 3),
            'difference' => number_format($model->difference, 3), 'balanced' => $model->balanced,
        ];

        $tabs = [
            'expenses' => ['label' => 'Expenses', 'icon' => 'bi-arrow-up-right-circle'],
            'revenues' => ['label' => 'Revenues', 'icon' => 'bi-arrow-down-left-circle'],
        ];
        $active = request('tab', 'expenses');
        if (! array_key_exists($active, $tabs)) {
            $active = 'expenses';
        }

        $expenses = $model->expenses->map(fn ($e) => [
            'date' => optional($e->expense_date)->format('Y-m-d'), 'desc' => $e->item, 'category' => $e->category, 'amount' => number_format($e->amount, 3), 'status' => $e->status,
        ])->all();

        $revenues = $model->revenues->map(fn ($r) => [
            'date' => optional($r->revenue_date)->format('Y-m-d'), 'source' => $r->source, 'ref' => $r->reference, 'amount' => number_format($r->amount, 3), 'status' => $r->status,
        ])->all();

        return view('pages.account-show', compact('account', 'tabs', 'active', 'expenses', 'revenues'));
    }

    /**
     * Contracts & Invoices — listings.
     */
    public function contracts()
    {
        $contracts = Contract::with(['client', 'exhibition', 'items'])->latest('id')->get()->map(fn ($c) => [
            'id' => $c->id, 'no' => $c->number, 'client' => $c->client?->name, 'exhibition' => $c->exhibition?->title,
            'value' => $this->money($c->value), 'date' => optional($c->start_date)->format('Y-m-d'), 'status' => $c->status,
        ])->all();

        $invoices = Invoice::with(['client', 'contract', 'items'])->latest('id')->get()->map(fn ($i) => [
            'id' => $i->id, 'no' => $i->number, 'client' => $i->client?->name, 'contract' => $i->contract?->number,
            'amount' => $this->money($i->total), 'date' => optional($i->issue_date)->format('Y-m-d'), 'status' => $i->status,
        ])->all();

        return view('pages.contracts', compact('contracts', 'invoices'));
    }

    /**
     * Stock — devices & equipment and services.
     */
    public function stock()
    {
        $types = [
            'equipment' => ['label' => 'Devices & Equipment', 'icon' => 'bi-pc-display'],
            'services' => ['label' => 'Services',            'icon' => 'bi-tools'],
        ];

        $active = request('type', 'equipment');
        if (! array_key_exists($active, $types)) {
            $active = 'equipment';
        }

        $equipment = StockItem::type('equipment')->latest('id')->get()->map(fn ($s) => [
            'id' => $s->id, 'name' => $s->name, 'sku' => $s->sku, 'qty' => $s->quantity, 'available' => $s->available, 'status' => $s->status,
        ])->all();

        $services = StockItem::type('service')->latest('id')->get()->map(fn ($s) => [
            'id' => $s->id, 'name' => $s->name, 'unit' => $s->unit, 'price' => $this->money($s->price), 'price_raw' => (float) $s->price, 'status' => $s->status,
        ])->all();

        $stockStatuses = $this->taskStatuses();

        return view('pages.stock', compact('types', 'active', 'equipment', 'services', 'stockStatuses'));
    }

    public function stockStore(Request $request)
    {
        $data = $this->validateStock($request);
        StockItem::create($data);

        return redirect()->back()->with('status', __('Saved successfully.'));
    }

    public function stockUpdate(Request $request, StockItem $stockItem)
    {
        $data = $this->validateStock($request);
        $stockItem->update($data);

        return redirect()->back()->with('status', __('Saved successfully.'));
    }

    public function stockDestroy(StockItem $stockItem)
    {
        $stockItem->delete();

        return redirect()->back()->with('status', __('Deleted successfully.'));
    }

    private function validateStock(Request $request): array
    {
        $data = $request->validate([
            'type' => 'required|in:equipment,service',
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'quantity' => 'nullable|integer|min:0',
            'available' => 'nullable|integer|min:0',
            'unit' => 'nullable|string|max:100',
            'price' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:'.implode(',', $this->taskStatuses()),
        ]);
        $data['status'] = $data['status'] ?? 'Active';

        return $data;
    }

    public function archive()
    {
        $items = [];

        return view('pages.archive', compact('items'));
    }

    /**
     * Data — master/reference data hub.
     */
    public function data()
    {
        $sections = [
            ['title' => 'Entities', 'desc' => 'الجهات والممثلين', 'icon' => 'bi-building', 'color' => 'blue', 'count' => (string) Contact::type('entity')->count(), 'route' => 'contacts', 'param' => ['type' => 'entities']],
            ['title' => 'Clients', 'desc' => 'العملاء وبيانات التواصل', 'icon' => 'bi-person-badge', 'color' => 'green', 'count' => (string) Contact::type('client')->count(), 'route' => 'contacts', 'param' => ['type' => 'clients']],
            ['title' => 'Organizers', 'desc' => 'فرق التنظيم', 'icon' => 'bi-people', 'color' => 'brand', 'count' => (string) Contact::type('organizer')->count(), 'route' => 'contacts', 'param' => ['type' => 'organizers']],
            ['title' => 'Suppliers', 'desc' => 'الموردين والفئات', 'icon' => 'bi-truck', 'color' => 'amber', 'count' => (string) Contact::type('supplier')->count(), 'route' => 'contacts', 'param' => ['type' => 'suppliers']],
            ['title' => 'Devices & Equipment', 'desc' => 'مخزون الأجهزة والمعدات', 'icon' => 'bi-pc-display', 'color' => 'blue', 'count' => (string) StockItem::type('equipment')->count(), 'route' => 'stock', 'param' => ['type' => 'equipment']],
            ['title' => 'Services', 'desc' => 'الخدمات المتاحة', 'icon' => 'bi-tools', 'color' => 'green', 'count' => (string) StockItem::type('service')->count(), 'route' => 'stock', 'param' => ['type' => 'services']],
        ];

        return view('pages.data', compact('sections'));
    }

    public function settings()
    {
        $roles = ['Administrator', 'Manager', 'Operator', 'Accountant', 'Viewer'];

        $users = User::orderBy('name')->get()->map(fn ($u) => [
            'id' => $u->id, 'name' => $u->name, 'email' => $u->email,
            'role' => $u->role ?? 'Operator', 'status' => $u->status ?? 'Active',
        ])->all();

        $settings = [
            'system_name' => Setting::get('system_name', config('app.name')),
            'currency' => Setting::get('currency', 'SAR — ر.س'),
            'timezone' => Setting::get('timezone', 'Asia/Riyadh'),
            'profile_name' => Setting::get('profile_name', 'Admin'),
            'profile_email' => Setting::get('profile_email', 'admin@eventpuls.sa'),
            'default_currency' => Setting::get('default_currency', 'SAR — ر.س'),
            'vat_rate' => Setting::get('vat_rate', '15'),
            'invoice_prefix' => Setting::get('invoice_prefix', 'INV-'),
        ];

        return view('pages.settings', compact('users', 'roles', 'settings'));
    }

    /** Persist whichever settings fields were submitted (general / profile / finance forms). */
    public function settingsSave(Request $request)
    {
        $allowed = ['system_name', 'currency', 'timezone', 'profile_name', 'profile_email', 'default_currency', 'vat_rate', 'invoice_prefix'];

        foreach ($allowed as $key) {
            if ($request->has($key)) {
                Setting::put($key, (string) $request->input($key));
            }
        }

        return redirect()->route('settings', ['tab' => request('tab', 'general')])->with('status', __('Saved successfully.'));
    }

    public function userStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string|max:50',
            'status' => 'nullable|in:Active,Cancelled',
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
            'status' => $data['status'] ?? 'Active',
        ]);

        return redirect()->route('settings', ['tab' => 'users'])->with('status', __('Saved successfully.'));
    }

    public function userUpdate(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:6',
            'role' => 'sometimes|required|string|max:50',
            'status' => 'sometimes|in:Active,Cancelled',
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('settings', ['tab' => 'users'])->with('status', __('Saved successfully.'));
    }

    public function userDestroy(User $user)
    {
        $user->delete();

        return redirect()->route('settings', ['tab' => 'users'])->with('status', __('Deleted successfully.'));
    }

    /**
     * Reports — analytical summaries (catalog is static; KPIs are live).
     */
    public function reports()
    {
        $paid = Invoice::where('status', 'Paid')->get()->sum('total');
        $pending = Invoice::where('status', '!=', 'Paid')->get()->sum('amount_due');
        $expenses = Expense::sum('amount');

        $summary = [
            ['key' => 'Total Revenue',    'value' => $this->money($paid + $pending),  'icon' => 'bi-cash-stack',     'color' => 'green', 'change' => '+18%', 'dir' => 'up'],
            ['key' => 'Exhibitions',      'value' => (string) Exhibition::count(),    'icon' => 'bi-easel2',         'color' => 'brand', 'change' => '+12%', 'dir' => 'up'],
            ['key' => 'Net Profit',       'value' => $this->money($paid + $pending - $expenses), 'icon' => 'bi-graph-up-arrow', 'color' => 'blue', 'change' => '+9%', 'dir' => 'up'],
            ['key' => 'Pending Payments', 'value' => $this->money($pending),          'icon' => 'bi-hourglass-split', 'color' => 'amber', 'change' => '-2%', 'dir' => 'down'],
        ];

        $reports = [
            ['title' => 'Financial Report',   'desc' => 'الإيرادات والمصروفات وصافي الربح', 'icon' => 'bi-wallet2',           'color' => 'green', 'route' => 'finance'],
            ['title' => 'Exhibitions Report', 'desc' => 'أداء المعارض والإشغال',            'icon' => 'bi-easel2',            'color' => 'brand', 'route' => 'exhibitions'],
            ['title' => 'Contacts Report',    'desc' => 'العملاء والجهات والموردين',         'icon' => 'bi-person-rolodex',    'color' => 'blue',  'route' => 'contacts'],
            ['title' => 'Inventory Report',   'desc' => 'حركة المخزون والمعدات',            'icon' => 'bi-box-seam',          'color' => 'amber', 'route' => 'stock'],
            ['title' => 'Contracts Report',   'desc' => 'العقود والفواتير المصدرة',          'icon' => 'bi-file-earmark-text', 'color' => 'red',   'route' => 'contracts'],
            ['title' => 'Tasks Report',       'desc' => 'إنجاز المهام والأداء التشغيلي',     'icon' => 'bi-list-check',        'color' => 'gray',  'route' => 'tasks'],
        ];

        $recent = [];

        return view('pages.reports', compact('summary', 'reports', 'recent'));
    }

    /**
     * Invoice / Receipt preview — A4 document + editable content.
     */
    public function invoiceShow(string $id)
    {
        $model = Invoice::with(['client', 'items'])
            ->where('number', $id)->orWhere('id', $id)->firstOrFail();

        $company = $this->company();

        $invoice = [
            'number' => $model->number,
            'status' => $model->status,
            'currency' => $model->currency,
            'date' => optional($model->issue_date)->format('Y-m-d'),
            'due' => optional($model->due_date)->format('Y-m-d'),
            'po' => $model->po,
            'vatRate' => (float) $model->vat_rate,
            'discount' => (float) $model->discount,
        ];

        $client = $model->client;
        $customer = [
            'name' => $client?->name ?? '',
            'address' => $client?->address ?? '',
            'country' => $client?->country ?? 'المملكة العربية السعودية',
            'vat' => $client?->vat_no ?? '',
            'contact' => $client?->representative ?? $client?->phone ?? '',
        ];

        $items = $model->items->map(fn ($i) => [
            'desc' => $i->description, 'qty' => (float) $i->qty, 'price' => (float) $i->price,
        ])->all();

        return view('pages.invoice-show', compact('company', 'invoice', 'customer', 'items'));
    }

    /**
     * Contract preview — A4 document + editable content.
     */
    public function contractShow(string $id)
    {
        $model = Contract::with(['client', 'exhibition', 'items', 'schedules', 'terms'])
            ->where('number', $id)->orWhere('id', $id)->firstOrFail();

        $company = $this->company();
        $client = $model->client;

        $contract = [
            'number' => $model->number,
            'status' => $model->status,
            'type' => $model->type,
            'client' => $client?->name ?? '',
            'clientVat' => $client?->vat_no ?? '',
            'clientRep' => $client?->representative ?? $client?->phone ?? '',
            'exhibition' => $model->exhibition?->title ?? '',
            'currency' => $model->currency,
            'start' => optional($model->start_date)->format('Y-m-d'),
            'end' => optional($model->end_date)->format('Y-m-d'),
            'vatRate' => (float) $model->vat_rate,
        ];

        $items = $model->items->map(fn ($i) => [
            'desc' => $i->description, 'qty' => (float) $i->qty, 'price' => (float) $i->price,
        ])->all();

        $schedule = $model->schedules->map(fn ($s) => [
            'desc' => $s->description, 'percent' => (float) $s->percent, 'due' => optional($s->due_date)->format('Y-m-d'),
        ])->all();

        $terms = $model->terms->pluck('body')->all();

        return view('pages.contract-show', compact('company', 'contract', 'items', 'schedule', 'terms'));
    }

    /* ===================================================================
     | Invoices — create / edit / persist
     * =================================================================== */

    /** Clients (contacts of type "client") available in the document selects. */
    private function clientOptions(): array
    {
        return Contact::type('client')->orderBy('name')->get(['id', 'name'])
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])->all();
    }

    /** Exhibitions available in the document selects. */
    private function exhibitionOptions(): array
    {
        return Exhibition::orderBy('title')->get(['id', 'title'])
            ->map(fn ($e) => ['id' => $e->id, 'name' => $e->title])->all();
    }

    private function nextInvoiceNumber(): string
    {
        return 'INV-'.str_pad((string) ((int) Invoice::max('id') + 1), 4, '0', STR_PAD_LEFT);
    }

    private function nextContractNumber(): string
    {
        return 'CT-'.str_pad((string) ((int) Contract::max('id') + 1), 4, '0', STR_PAD_LEFT);
    }

    /** New invoice editor. */
    public function invoiceCreate()
    {
        return $this->invoiceForm(new Invoice([
            'number' => $this->nextInvoiceNumber(),
            'currency' => 'ر.س',
            'issue_date' => Carbon::now(),
            'due_date' => Carbon::now()->addDays(15),
            'vat_rate' => 15,
            'status' => 'Draft',
        ]));
    }

    /** Edit an existing invoice in the same editor. */
    public function invoiceEdit(Invoice $invoice)
    {
        return $this->invoiceForm($invoice->load('items'));
    }

    /** Shared invoice editor view used by both create and edit. */
    private function invoiceForm(Invoice $model)
    {
        $invoice = [
            'number' => $model->number,
            'currency' => $model->currency ?? 'ر.س',
            'date' => optional($model->issue_date)->format('Y-m-d'),
            'due' => optional($model->due_date)->format('Y-m-d'),
            'po' => $model->po,
            'notes' => $model->notes,
            'vatRate' => (float) ($model->vat_rate ?? 15),
            'discount' => (float) ($model->discount ?? 0),
            'clientId' => $model->client_id,
            'exhibitionId' => $model->exhibition_id,
        ];

        $items = $model->exists
            ? $model->items->map(fn ($i) => ['desc' => $i->description, 'qty' => (float) $i->qty, 'price' => (float) $i->price])->all()
            : [];

        return view('pages.invoice-create', [
            'company' => $this->company(),
            'invoice' => $invoice,
            'items' => $items,
            'clients' => $this->clientOptions(),
            'exhibitions' => $this->exhibitionOptions(),
            'isEdit' => $model->exists,
            'action' => $model->exists ? route('invoices.update', $model) : route('invoices.store'),
        ]);
    }

    public function invoiceStore(Request $request)
    {
        $invoice = new Invoice;
        $this->saveInvoice($invoice, $request);

        return redirect()->route('invoices.show', $invoice->number)->with('status', __('Saved successfully.'));
    }

    public function invoiceUpdate(Request $request, Invoice $invoice)
    {
        $this->saveInvoice($invoice, $request);

        return redirect()->route('invoices.show', $invoice->number)->with('status', __('Saved successfully.'));
    }

    public function invoiceDestroy(Invoice $invoice)
    {
        $invoice->delete();

        return redirect()->route('contracts', ['tab' => 'invoices'])->with('status', __('Deleted successfully.'));
    }

    private function saveInvoice(Invoice $invoice, Request $request): void
    {
        $unique = 'unique:invoices,number'.($invoice->exists ? ','.$invoice->id : '');

        $data = $request->validate([
            'number' => 'required|string|max:255|'.$unique,
            'client_id' => 'nullable|exists:contacts,id',
            'exhibition_id' => 'nullable|exists:exhibitions,id',
            'currency' => 'nullable|string|max:20',
            'issue_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:issue_date',
            'po' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'vat_rate' => 'nullable|numeric|min:0|max:100',
            'discount' => 'nullable|numeric|min:0',
            'items' => 'array',
            'items.*.description' => 'nullable|string|max:500',
            'items.*.qty' => 'nullable|numeric|min:0',
            'items.*.price' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($invoice, $data, $request) {
            $invoice->fill([
                'number' => $data['number'],
                'client_id' => $data['client_id'] ?? null,
                'exhibition_id' => $data['exhibition_id'] ?? null,
                'currency' => $data['currency'] ?? 'ر.س',
                'issue_date' => $data['issue_date'] ?? null,
                'due_date' => $data['due_date'] ?? null,
                'po' => $data['po'] ?? null,
                'notes' => $data['notes'] ?? null,
                'vat_rate' => $data['vat_rate'] ?? 15,
                'discount' => $data['discount'] ?? 0,
                'status' => $this->documentStatus($request, $invoice->status, 'Unpaid'),
            ])->save();

            $this->syncLineItems($invoice->items(), $request->input('items', []));
        });
    }

    /* ===================================================================
     | Contracts — create / edit / persist
     * =================================================================== */

    /** New contract editor. */
    public function contractCreate()
    {
        return $this->contractForm(new Contract([
            'number' => $this->nextContractNumber(),
            'type' => 'عقد خدمات',
            'currency' => 'ر.س',
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addMonths(3),
            'vat_rate' => 15,
            'status' => 'Draft',
        ]));
    }

    /** Edit an existing contract in the same editor. */
    public function contractEdit(Contract $contract)
    {
        return $this->contractForm($contract->load(['items', 'schedules', 'terms']));
    }

    /** Shared contract editor view used by both create and edit. */
    private function contractForm(Contract $model)
    {
        $contract = [
            'number' => $model->number,
            'type' => $model->type ?? 'عقد خدمات',
            'currency' => $model->currency ?? 'ر.س',
            'start' => optional($model->start_date)->format('Y-m-d'),
            'end' => optional($model->end_date)->format('Y-m-d'),
            'vatRate' => (float) ($model->vat_rate ?? 15),
            'notes' => $model->notes,
            'clientId' => $model->client_id,
            'exhibitionId' => $model->exhibition_id,
        ];

        $items = $model->exists
            ? $model->items->map(fn ($i) => ['desc' => $i->description, 'qty' => (float) $i->qty, 'price' => (float) $i->price])->all()
            : [];

        $schedule = $model->exists
            ? $model->schedules->map(fn ($s) => ['desc' => $s->description, 'percent' => (float) $s->percent, 'due' => optional($s->due_date)->format('Y-m-d')])->all()
            : [];

        $terms = $model->exists ? $model->terms->pluck('body')->all() : [];

        return view('pages.contract-create', [
            'company' => $this->company(),
            'contract' => $contract,
            'items' => $items,
            'schedule' => $schedule,
            'terms' => $terms,
            'clients' => $this->clientOptions(),
            'exhibitions' => $this->exhibitionOptions(),
            'isEdit' => $model->exists,
            'action' => $model->exists ? route('contracts.update', $model) : route('contracts.store'),
        ]);
    }

    public function contractStore(Request $request)
    {
        $contract = new Contract;
        $this->saveContract($contract, $request);

        return redirect()->route('contracts.show', $contract->number)->with('status', __('Saved successfully.'));
    }

    public function contractUpdate(Request $request, Contract $contract)
    {
        $this->saveContract($contract, $request);

        return redirect()->route('contracts.show', $contract->number)->with('status', __('Saved successfully.'));
    }

    public function contractDestroy(Contract $contract)
    {
        $contract->delete();

        return redirect()->route('contracts')->with('status', __('Deleted successfully.'));
    }

    private function saveContract(Contract $contract, Request $request): void
    {
        $unique = 'unique:contracts,number'.($contract->exists ? ','.$contract->id : '');

        $data = $request->validate([
            'number' => 'required|string|max:255|'.$unique,
            'client_id' => 'nullable|exists:contacts,id',
            'exhibition_id' => 'nullable|exists:exhibitions,id',
            'type' => 'nullable|string|max:100',
            'currency' => 'nullable|string|max:20',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'vat_rate' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
            'items' => 'array',
            'items.*.description' => 'nullable|string|max:500',
            'items.*.qty' => 'nullable|numeric|min:0',
            'items.*.price' => 'nullable|numeric|min:0',
            'schedule' => 'array',
            'schedule.*.description' => 'nullable|string|max:500',
            'schedule.*.percent' => 'nullable|numeric|min:0|max:100',
            'schedule.*.due_date' => 'nullable|date',
            'terms' => 'array',
            'terms.*' => 'nullable|string|max:2000',
        ]);

        DB::transaction(function () use ($contract, $data, $request) {
            $contract->fill([
                'number' => $data['number'],
                'client_id' => $data['client_id'] ?? null,
                'exhibition_id' => $data['exhibition_id'] ?? null,
                'type' => $data['type'] ?? 'عقد خدمات',
                'currency' => $data['currency'] ?? 'ر.س',
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'vat_rate' => $data['vat_rate'] ?? 15,
                'notes' => $data['notes'] ?? null,
                'status' => $this->documentStatus($request, $contract->status, 'Active'),
            ])->save();

            $this->syncLineItems($contract->items(), $request->input('items', []));
            $this->syncSchedule($contract, $request->input('schedule', []));
            $this->syncTerms($contract, $request->input('terms', []));
        });
    }

    /* ===================================================================
     | Shared persistence helpers for document children
     * =================================================================== */

    /**
     * Resolve a document status from the submit intent.
     * "draft" → Draft; "send" → keep any non-draft status, else the default.
     */
    private function documentStatus(Request $request, ?string $current, string $sentDefault): string
    {
        if ($request->input('intent') === 'draft') {
            return 'Draft';
        }

        return $current && $current !== 'Draft' ? $current : $sentDefault;
    }

    /** Replace a document's line items (description/qty/price) from posted rows. */
    private function syncLineItems($relation, array $rows): void
    {
        $relation->delete();
        $position = 0;

        foreach ($rows as $row) {
            $desc = trim((string) ($row['description'] ?? ''));
            $qty = $row['qty'] ?? null;
            $price = $row['price'] ?? null;

            if ($desc === '' && $this->blank($qty) && $this->blank($price)) {
                continue;
            }

            $relation->create([
                'description' => $desc,
                'qty' => $this->blank($qty) ? 1 : $qty,
                'price' => $this->blank($price) ? 0 : $price,
                'position' => $position++,
            ]);
        }
    }

    private function syncSchedule(Contract $contract, array $rows): void
    {
        $contract->schedules()->delete();
        $position = 0;

        foreach ($rows as $row) {
            $desc = trim((string) ($row['description'] ?? ''));
            $percent = $row['percent'] ?? null;
            $due = $row['due_date'] ?? null;

            if ($desc === '' && $this->blank($percent) && $this->blank($due)) {
                continue;
            }

            $contract->schedules()->create([
                'description' => $desc,
                'percent' => $this->blank($percent) ? 0 : $percent,
                'due_date' => $this->blank($due) ? null : $due,
                'position' => $position++,
            ]);
        }
    }

    private function syncTerms(Contract $contract, array $rows): void
    {
        $contract->terms()->delete();
        $position = 0;

        foreach ($rows as $body) {
            $body = trim((string) ($body ?? ''));
            if ($body === '') {
                continue;
            }

            $contract->terms()->create(['body' => $body, 'position' => $position++]);
        }
    }

    private function blank($value): bool
    {
        return $value === null || $value === '';
    }
}
