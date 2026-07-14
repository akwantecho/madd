<?php

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TaskOperationalTest extends TestCase
{
    use RefreshDatabase;

    public function test_flag_toggle_persists_via_update(): void
    {
        $task = Task::create(['title' => 'A', 'status' => 'Upcoming', 'priority' => 'Medium']);

        $this->putJson('/tasks/'.$task->id, ['flagged' => true])
            ->assertOk()
            ->assertJsonPath('flagged', true);

        $this->assertTrue($task->fresh()->flagged);

        $this->putJson('/tasks/'.$task->id, ['flagged' => false])->assertJsonPath('flagged', false);
        $this->assertFalse($task->fresh()->flagged);
    }

    public function test_completing_a_task_from_the_list_persists(): void
    {
        $task = Task::create(['title' => 'A', 'status' => 'Upcoming', 'priority' => 'Medium']);

        // This is exactly what the list checkbox now sends.
        $this->putJson('/tasks/'.$task->id, ['status' => 'Completed'])
            ->assertOk()
            ->assertJsonPath('state', 'done');

        $this->assertSame('Completed', $task->fresh()->status);
    }

    public function test_description_is_stored_and_returned(): void
    {
        $res = $this->postJson('/tasks', ['title' => 'A', 'description' => 'Some notes']);
        $res->assertOk()->assertJsonPath('description', 'Some notes');

        $task = Task::first();
        $this->assertSame('Some notes', $task->description);

        $this->putJson('/tasks/'.$task->id, ['description' => 'Updated notes'])
            ->assertJsonPath('description', 'Updated notes');
        $this->assertSame('Updated notes', $task->fresh()->description);
    }

    public function test_due_state_is_overdue_today_and_none_for_done(): void
    {
        $overdue = Task::create(['title' => 'O', 'status' => 'Active', 'due_date' => Carbon::yesterday()]);
        $today = Task::create(['title' => 'T', 'status' => 'Active', 'due_date' => Carbon::today()]);
        $doneButPast = Task::create(['title' => 'D', 'status' => 'Completed', 'due_date' => Carbon::yesterday()]);

        $payload = $this->get('/tasks')->viewData('tasks');
        $byId = collect($payload)->keyBy('id');

        $this->assertSame('overdue', $byId[$overdue->id]['due_state']);
        $this->assertSame('today', $byId[$today->id]['due_state']);
        // A completed task is never flagged as overdue.
        $this->assertSame('none', $byId[$doneButPast->id]['due_state']);
    }

    public function test_form_submit_creates_task_and_redirects(): void
    {
        // A normal (non-JSON) form post should persist and redirect back to the list.
        $this->post('/tasks', [
            'title' => 'From form',
            'description' => 'notes',
            'priority' => 'High',
            'status' => 'Active',
            'assignee' => 'Sara',
            'flagged' => '1',
        ])->assertRedirect(route('tasks'));

        $task = Task::where('title', 'From form')->first();
        $this->assertNotNull($task);
        $this->assertSame('High', $task->priority);
        $this->assertTrue($task->flagged);
    }

    public function test_form_delete_redirects(): void
    {
        $task = Task::create(['title' => 'X', 'status' => 'Active']);

        $this->delete('/tasks/'.$task->id)->assertRedirect(route('tasks'));

        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    public function test_tasks_are_sorted_by_due_date_with_done_last(): void
    {
        $later = Task::create(['title' => 'later', 'status' => 'Active', 'due_date' => Carbon::today()->addDays(10)]);
        $soon = Task::create(['title' => 'soon', 'status' => 'Active', 'due_date' => Carbon::today()->addDay()]);
        $done = Task::create(['title' => 'done', 'status' => 'Completed', 'due_date' => Carbon::today()->addDay()]);
        $noDate = Task::create(['title' => 'nodate', 'status' => 'Active']);

        $order = collect($this->get('/tasks')->viewData('tasks'))->pluck('title')->all();

        // Soonest active first, then later, then no-date, then completed last.
        $this->assertSame(['soon', 'later', 'nodate', 'done'], $order);
    }
}
