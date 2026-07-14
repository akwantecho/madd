<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Contract;
use App\Models\Exhibition;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractInvoiceCycleTest extends TestCase
{
    use RefreshDatabase;

    private function client(): Contact
    {
        return Contact::create(['type' => 'client', 'name' => 'Test Client']);
    }

    private function exhibition(): Exhibition
    {
        return Exhibition::create(['title' => 'Test Expo']);
    }

    public function test_invoice_is_created_with_line_items_and_skips_empty_rows(): void
    {
        $client = $this->client();

        $res = $this->post('/invoices', [
            'intent' => 'send',
            'number' => 'INV-100',
            'client_id' => $client->id,
            'currency' => 'ر.س',
            'issue_date' => '2026-06-23',
            'due_date' => '2026-07-08',
            'vat_rate' => 15,
            'discount' => 100,
            'items' => [
                ['description' => 'Item A', 'qty' => 2, 'price' => 5000],
                ['description' => 'Item B', 'qty' => 1, 'price' => 1500],
                ['description' => '', 'qty' => '', 'price' => ''], // empty → skipped
            ],
        ]);

        $res->assertRedirect('/invoices/INV-100');

        $inv = Invoice::where('number', 'INV-100')->with('items')->first();
        $this->assertNotNull($inv);
        $this->assertSame(2, $inv->items->count());
        $this->assertEquals(11500, $inv->subtotal);
        // (11500 - 100 discount) * 1.15 = 13110
        $this->assertEquals(13110, $inv->total);
        $this->assertSame('Unpaid', $inv->status);
    }

    public function test_invoice_update_replaces_items_and_draft_intent_sets_draft_status(): void
    {
        $client = $this->client();
        $invoice = Invoice::create(['number' => 'INV-200', 'client_id' => $client->id, 'vat_rate' => 15, 'status' => 'Unpaid']);
        $invoice->items()->create(['description' => 'old', 'qty' => 1, 'price' => 100, 'position' => 0]);

        $this->put('/invoices/'.$invoice->id, [
            'intent' => 'draft',
            'number' => 'INV-200',
            'client_id' => $client->id,
            'vat_rate' => 15,
            'discount' => 0,
            'items' => [
                ['description' => 'new', 'qty' => 3, 'price' => 5000],
            ],
        ])->assertRedirect('/invoices/INV-200');

        $invoice->refresh()->load('items');
        $this->assertSame(1, $invoice->items->count());
        $this->assertSame('new', $invoice->items->first()->description);
        $this->assertSame('Draft', $invoice->status);
    }

    public function test_duplicate_invoice_number_is_rejected(): void
    {
        Invoice::create(['number' => 'INV-300', 'vat_rate' => 15]);

        $this->post('/invoices', [
            'intent' => 'send',
            'number' => 'INV-300',
            'vat_rate' => 15,
            'items' => [['description' => 'x', 'qty' => 1, 'price' => 1]],
        ])->assertSessionHasErrors('number');

        $this->assertSame(1, Invoice::where('number', 'INV-300')->count());
    }

    public function test_contract_is_created_with_items_schedule_and_terms(): void
    {
        $client = $this->client();
        $exh = $this->exhibition();

        $this->post('/contracts', [
            'intent' => 'send',
            'number' => 'CT-100',
            'client_id' => $client->id,
            'exhibition_id' => $exh->id,
            'type' => 'عقد رعاية',
            'currency' => 'ر.س',
            'start_date' => '2026-06-23',
            'end_date' => '2026-09-23',
            'vat_rate' => 15,
            'items' => [['description' => 'Sponsorship', 'qty' => 1, 'price' => 80000]],
            'schedule' => [
                ['description' => 'Advance', 'percent' => 50, 'due_date' => '2026-06-23'],
                ['description' => 'Final', 'percent' => 50, 'due_date' => '2026-09-23'],
            ],
            'terms' => ['Term one', 'Term two', ''], // empty → skipped
        ])->assertRedirect('/contracts/CT-100');

        $ct = Contract::where('number', 'CT-100')->with(['items', 'schedules', 'terms'])->first();
        $this->assertNotNull($ct);
        $this->assertSame(1, $ct->items->count());
        $this->assertSame(2, $ct->schedules->count());
        $this->assertSame(2, $ct->terms->count());
        $this->assertEquals(92000, $ct->value); // 80000 * 1.15
        $this->assertSame('Active', $ct->status);
        $this->assertSame('عقد رعاية', $ct->type);
    }

    public function test_contract_update_replaces_children(): void
    {
        $client = $this->client();
        $contract = Contract::create(['number' => 'CT-200', 'client_id' => $client->id, 'vat_rate' => 15]);
        $contract->items()->create(['description' => 'old', 'qty' => 1, 'price' => 1, 'position' => 0]);
        $contract->schedules()->create(['description' => 'a', 'percent' => 50, 'position' => 0]);
        $contract->schedules()->create(['description' => 'b', 'percent' => 50, 'position' => 1]);
        $contract->terms()->create(['body' => 'old term', 'position' => 0]);

        $this->put('/contracts/'.$contract->id, [
            'intent' => 'send',
            'number' => 'CT-200',
            'client_id' => $client->id,
            'type' => 'عقد خدمات',
            'vat_rate' => 15,
            'items' => [['description' => 'Revised', 'qty' => 2, 'price' => 10000]],
            'schedule' => [['description' => 'One', 'percent' => 100, 'due_date' => '2026-07-01']],
            'terms' => ['Single term'],
        ])->assertRedirect('/contracts/CT-200');

        $contract->refresh()->load(['items', 'schedules', 'terms']);
        $this->assertSame(1, $contract->items->count());
        $this->assertSame(1, $contract->schedules->count());
        $this->assertSame(1, $contract->terms->count());
        $this->assertEquals(23000, $contract->value);
    }

    public function test_documents_can_be_deleted(): void
    {
        $invoice = Invoice::create(['number' => 'INV-400', 'vat_rate' => 15]);
        $contract = Contract::create(['number' => 'CT-400', 'vat_rate' => 15]);

        $this->delete('/invoices/'.$invoice->id)->assertRedirect();
        $this->delete('/contracts/'.$contract->id)->assertRedirect();

        $this->assertSoftDeleted('invoices', ['id' => $invoice->id]);
        $this->assertSoftDeleted('contracts', ['id' => $contract->id]);
    }

    public function test_editor_pages_render(): void
    {
        $client = $this->client();
        $invoice = Invoice::create(['number' => 'INV-500', 'client_id' => $client->id, 'vat_rate' => 15]);
        $contract = Contract::create(['number' => 'CT-500', 'client_id' => $client->id, 'vat_rate' => 15]);

        $this->get('/invoices/create')->assertOk();
        $this->get('/contracts/create')->assertOk();
        $this->get('/invoices/'.$invoice->id.'/edit')->assertOk();
        $this->get('/contracts/'.$contract->id.'/edit')->assertOk();
        $this->get('/contracts')->assertOk();
    }
}
