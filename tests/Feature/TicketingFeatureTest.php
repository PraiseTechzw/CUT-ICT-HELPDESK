<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AuditEvent;
use App\Models\Building;
use App\Models\Campus;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Room;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketPriority;
use App\Models\User;
use App\Support\Roles;
use App\Support\Permissions;
use App\Support\TicketStatus;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketingFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_requester_can_create_and_view_only_own_tickets(): void
    {
        $requester = $this->userWithRole(Roles::REQUESTER, 'Requester One');
        $otherRequester = $this->userWithRole(Roles::REQUESTER, 'Requester Two');
        $otherTicket = $this->ticket($otherRequester, "Other person's confidential issue");

        $response = $this->actingAs($requester)->post(route('tickets.store'), [
            'subject' => 'My laptop will not start',
            'description' => 'The screen remains blank after the power button is pressed.',
        ]);

        $ticket = Ticket::query()->where('requester_id', $requester->id)->firstOrFail();
        $response->assertRedirect(route('tickets.show', $ticket));
        $this->assertSame(TicketStatus::OPEN, $ticket->status);
        $this->assertDatabaseHas('ticket_status_histories', [
            'ticket_id' => $ticket->id,
            'from_status' => null,
            'to_status' => TicketStatus::OPEN,
            'actor_id' => $requester->id,
        ]);

        $this->get(route('tickets.show', $ticket))->assertOk()->assertSee('My laptop will not start');
        $this->get(route('tickets.show', $otherTicket))->assertNotFound();
        $this->get(route('tickets.index'))->assertOk()->assertSee('My laptop will not start')->assertDontSee("Other person's confidential issue");
    }

    public function test_ticket_search_matches_subject_description_and_ticket_number_without_widening_visibility(): void
    {
        $requester = $this->userWithRole(Roles::REQUESTER, 'Search Requester');
        $otherRequester = $this->userWithRole(Roles::REQUESTER, 'Other Search Requester');
        $subjectMatch = $this->ticket($requester, 'Printer offline');
        $descriptionMatch = $this->ticket($requester, 'Workstation support');
        $descriptionMatch->update(['description' => 'The docking station disconnects every hour.']);
        $privateMatch = $this->ticket($otherRequester, 'Private printer outage');

        $this->actingAs($requester)->get(route('tickets.index', ['search' => 'printer']))
            ->assertOk()
            ->assertSee('Printer offline')
            ->assertDontSee('Private printer outage')
            ->assertSee('value="printer"', false);

        $this->get(route('tickets.index', ['search' => 'docking station']))
            ->assertOk()
            ->assertSee('Workstation support')
            ->assertDontSee('Printer offline');

        $this->get(route('tickets.index', ['search' => (string) $subjectMatch->id]))
            ->assertOk()
            ->assertSee('Printer offline')
            ->assertDontSee('Workstation support');
        $this->assertDatabaseHas('tickets', ['id' => $privateMatch->id]);
    }

    public function test_all_tickets_page_shows_status_totals_and_compact_queue_columns(): void
    {
        $helpdesk = $this->userWithRole(Roles::HELPDESK_OFFICER, 'All Tickets Officer');
        $this->ticket($helpdesk, 'Open network issue', TicketStatus::OPEN);
        $this->ticket($helpdesk, 'Assigned device issue', TicketStatus::ASSIGNED);
        $this->ticket($helpdesk, 'In progress software issue', TicketStatus::IN_PROGRESS);
        $this->ticket($helpdesk, 'Resolved access issue', TicketStatus::RESOLVED);
        $this->ticket($helpdesk, 'Closed account issue', TicketStatus::CLOSED);

        $this->actingAs($helpdesk)->get(route('tickets.index'))
            ->assertOk()
            ->assertSee('Total Tickets')
            ->assertSee('In Progress')
            ->assertSee('Ticket ID')
            ->assertSee('Open network issue')
            ->assertSee('Assigned device issue')
            ->assertSee('Resolved access issue')
            ->assertSee('Closed account issue')
            ->assertSee('>5<', false)
            ->assertSee('>2<', false);
    }

    public function test_my_tickets_page_is_limited_to_the_requester_or_assignee(): void
    {
        $requester = $this->userWithRole(Roles::REQUESTER, 'My Queue Requester');
        $otherRequester = $this->userWithRole(Roles::REQUESTER, 'Other Queue Requester');
        $this->ticket($requester, 'My private queue ticket');
        $this->ticket($otherRequester, 'Another requester ticket');

        $this->actingAs($requester)->get(route('tickets.mine'))
            ->assertOk()
            ->assertSee('My Tickets')
            ->assertSee('My private queue ticket')
            ->assertDontSee('Another requester ticket');

        $administrator = $this->userWithRole(Roles::SYSTEM_ADMINISTRATOR, 'Full Access Administrator');
        $this->actingAs($administrator)->get(route('tickets.mine'))->assertOk()->assertSee('My Tickets');
        $this->get(route('tickets.index'))->assertOk()->assertSee('All Tickets');
    }

    public function test_my_ticket_status_filters_group_open_and_in_progress_workflow_states(): void
    {
        $requester = $this->userWithRole(Roles::REQUESTER, 'Status Tabs Requester');
        $this->ticket($requester, 'Open tab ticket', TicketStatus::OPEN);
        $this->ticket($requester, 'Assigned tab ticket', TicketStatus::ASSIGNED);
        $this->ticket($requester, 'In progress tab ticket', TicketStatus::IN_PROGRESS);
        $this->ticket($requester, 'Waiting tab ticket', TicketStatus::WAITING_FOR_REQUESTER);
        $this->ticket($requester, 'Resolved tab ticket', TicketStatus::RESOLVED);

        $this->actingAs($requester)->get(route('tickets.mine', ['status' => TicketStatus::OPEN]))
            ->assertOk()
            ->assertSee('Open tab ticket')
            ->assertSee('Assigned tab ticket')
            ->assertDontSee('In progress tab ticket')
            ->assertDontSee('Waiting tab ticket');

        $this->get(route('tickets.mine', ['status' => TicketStatus::IN_PROGRESS]))
            ->assertOk()
            ->assertSee('In progress tab ticket')
            ->assertSee('Waiting tab ticket')
            ->assertDontSee('Open tab ticket')
            ->assertDontSee('Resolved tab ticket');
    }

    public function test_requester_can_select_a_room_and_location_is_shown_on_the_ticket(): void
    {
        $requester = $this->userWithRole(Roles::REQUESTER, 'Location Requester');
        $campus = Campus::query()->create(['name' => 'Main Campus', 'code' => 'MAIN']);
        $building = Building::query()->create(['campus_id' => $campus->id, 'name' => 'Library', 'code' => 'LIB']);
        $room = Room::query()->create(['building_id' => $building->id, 'name' => 'Helpdesk Lab', 'code' => 'LAB-1']);

        $this->actingAs($requester)->get(route('tickets.create'))->assertOk()->assertSee('Additional Information')->assertSee('Helpdesk Lab');
        $this->post(route('tickets.store'), [
            'subject' => 'Campus workstation issue',
            'description' => 'The workstation cannot connect to the campus network.',
            'room_id' => $room->id,
        ])->assertRedirect();

        $ticket = Ticket::query()->where('requester_id', $requester->id)->firstOrFail();
        $this->assertSame($room->id, $ticket->room_id);
        $this->get(route('tickets.show', $ticket))->assertOk()->assertSee('Main Campus')->assertSee('Library')->assertSee('Helpdesk Lab');
    }

    public function test_overdue_queue_uses_assigned_deadlines_and_excludes_completed_tickets(): void
    {
        $helpdesk = $this->userWithRole(Roles::HELPDESK_OFFICER, 'Overdue Queue Officer');
        $requester = $this->userWithRole(Roles::REQUESTER, 'Overdue Queue Requester');
        $overdue = $this->ticket($requester, 'Past due support request');
        $overdue->update(['due_at' => now()->subDay()]);
        $completed = $this->ticket($requester, 'Completed past due request', TicketStatus::RESOLVED);
        $completed->update(['due_at' => now()->subDay()]);
        $future = $this->ticket($requester, 'Future due request');
        $future->update(['due_at' => now()->addDay()]);

        $this->actingAs($helpdesk)->get(route('tickets.overdue'))
            ->assertOk()
            ->assertSee('Past due support request')
            ->assertSee('You have 1 overdue ticket that requires attention.')
            ->assertSee('Days Overdue')
            ->assertDontSee('Completed past due request')
            ->assertDontSee('Future due request');
    }

    public function test_authorized_assignment_screen_sets_due_date_and_writes_deadline_audit_event(): void
    {
        $helpdesk = $this->userWithRole(Roles::HELPDESK_OFFICER, 'Deadline Officer');
        $requester = $this->userWithRole(Roles::REQUESTER, 'Deadline Requester');
        $technician = $this->userWithRole(Roles::TECHNICIAN, 'Deadline Technician');
        $ticket = $this->ticket($requester, 'Deadline assignment ticket');
        $deadline = now()->addDay()->startOfMinute();

        $this->actingAs($helpdesk)->get(route('tickets.assignment.edit', $ticket))
            ->assertOk()
            ->assertSee('Available Technicians')
            ->assertSee('Due date and time');
        $this->patch(route('tickets.assignment.update', $ticket), [
            'technician_id' => $technician->id,
            'due_at' => $deadline->format('Y-m-d H:i:s'),
        ])->assertRedirect(route('tickets.show', $ticket));

        $this->assertSame($deadline->timestamp, $ticket->fresh()->due_at?->timestamp);
        $this->assertDatabaseHas('audit_events', ['action' => 'ticket.deadline_updated', 'subject_id' => $ticket->id]);
    }

    public function test_category_priority_and_status_screens_require_catalogue_management(): void
    {
        $administrator = $this->userWithRole(Roles::SYSTEM_ADMINISTRATOR, 'Catalogue Screen Administrator');
        $requester = $this->userWithRole(Roles::REQUESTER, 'Catalogue Screen Requester');

        $this->actingAs($administrator)->get(route('admin.ticket-catalogues.categories.index'))->assertOk()->assertSee('Ticket Categories');
        $this->get(route('admin.ticket-catalogues.priorities.index'))->assertOk()->assertSee('Ticket Priorities');
        $this->get(route('admin.ticket-catalogues.statuses.index'))->assertOk()->assertSee('Workflow Statuses')->assertSee('Waiting on Requester');

        $this->actingAs($requester)->get(route('admin.ticket-catalogues.categories.index'))->assertForbidden();
        $this->get(route('admin.ticket-catalogues.priorities.index'))->assertForbidden();
        $this->get(route('admin.ticket-catalogues.statuses.index'))->assertForbidden();
    }

    public function test_requester_never_sees_internal_notes_or_technician_identity(): void
    {
        $requester = $this->userWithRole(Roles::REQUESTER, 'Synthetic Requester');
        $technician = $this->userWithRole(Roles::TECHNICIAN, 'Private Technician Name');
        $ticket = $this->ticket($requester, 'Public issue title', TicketStatus::IN_PROGRESS, $technician);
        $ticket->comments()->create(['author_id' => $technician->id, 'visibility' => 'public', 'body' => 'We are investigating.']);
        $ticket->comments()->create(['author_id' => $technician->id, 'visibility' => 'internal', 'body' => 'Internal diagnostic: private token abc123.']);

        $this->actingAs($requester)->get(route('tickets.show', $ticket))
            ->assertOk()
            ->assertSee('We are investigating.')
            ->assertDontSee('Internal diagnostic: private token abc123.')
            ->assertDontSee('Private Technician Name');

        $this->post(route('tickets.comments.store', $ticket), [
            'visibility' => 'internal',
            'body' => 'Requester must not add internal notes.',
        ])->assertForbidden();

        $this->post(route('tickets.comments.store', $ticket), [
            'visibility' => 'public',
            'body' => 'Additional public details.',
        ])->assertRedirect();
        $this->assertDatabaseHas('ticket_comments', ['ticket_id' => $ticket->id, 'body' => 'Additional public details.', 'visibility' => 'public']);
    }

    public function test_helpdesk_can_view_all_tickets_assign_an_active_technician_and_advance_status(): void
    {
        $helpdesk = $this->userWithRole(Roles::HELPDESK_OFFICER, 'Triage Officer');
        $requester = $this->userWithRole(Roles::REQUESTER, 'Synthetic Requester');
        $technician = $this->userWithRole(Roles::TECHNICIAN, 'Synthetic Technician');
        $ticket = $this->ticket($requester, 'Shared queue issue');

        $this->actingAs($helpdesk)->get(route('tickets.index'))->assertOk()->assertSee('Shared queue issue');
        $this->patch(route('tickets.assignment.update', $ticket), ['technician_id' => $technician->id])->assertRedirect();
        $ticket->refresh();
        $this->assertSame($technician->id, $ticket->assigned_technician_id);
        $this->assertSame(TicketStatus::ASSIGNED, $ticket->status);
        $this->assertDatabaseHas('ticket_assignment_histories', [
            'ticket_id' => $ticket->id,
            'from_technician_id' => null,
            'to_technician_id' => $technician->id,
            'actor_id' => $helpdesk->id,
        ]);

        $this->patch(route('tickets.status.update', $ticket), ['status' => TicketStatus::IN_PROGRESS])->assertRedirect();
        $this->assertSame(TicketStatus::IN_PROGRESS, $ticket->fresh()->status);
        $this->assertDatabaseHas('ticket_status_histories', [
            'ticket_id' => $ticket->id,
            'from_status' => TicketStatus::ASSIGNED,
            'to_status' => TicketStatus::IN_PROGRESS,
            'actor_id' => $helpdesk->id,
        ]);
    }

    public function test_technician_sees_and_updates_only_individually_assigned_tickets(): void
    {
        $technician = $this->userWithRole(Roles::TECHNICIAN, 'Assigned Technician');
        $otherTechnician = $this->userWithRole(Roles::TECHNICIAN, 'Other Technician');
        $requester = $this->userWithRole(Roles::REQUESTER, 'Synthetic Requester');
        $assigned = $this->ticket($requester, 'Assigned repair', TicketStatus::ASSIGNED, $technician);
        $notAssigned = $this->ticket($requester, 'Different technician issue', TicketStatus::ASSIGNED, $otherTechnician);

        $this->actingAs($technician)->get(route('tickets.index'))
            ->assertOk()->assertSee('Assigned repair')->assertDontSee('Different technician issue');
        $this->get(route('tickets.show', $assigned))->assertOk();
        $this->get(route('tickets.show', $notAssigned))->assertNotFound();

        $this->patch(route('tickets.status.update', $assigned), ['status' => TicketStatus::IN_PROGRESS])->assertRedirect();
        $this->assertSame(TicketStatus::IN_PROGRESS, $assigned->fresh()->status);
        $this->patch(route('tickets.status.update', $notAssigned), ['status' => TicketStatus::IN_PROGRESS])->assertNotFound();
        $this->patch(route('tickets.assignment.update', $assigned), ['technician_id' => $otherTechnician->id])->assertForbidden();
    }

    public function test_technician_can_add_internal_work_notes_but_cannot_close_tickets(): void
    {
        $technician = $this->userWithRole(Roles::TECHNICIAN, 'Assigned Technician');
        $requester = $this->userWithRole(Roles::REQUESTER, 'Synthetic Requester');
        $ticket = $this->ticket($requester, 'Work note issue', TicketStatus::RESOLVED, $technician);

        $this->actingAs($technician)->post(route('tickets.comments.store', $ticket), [
            'visibility' => 'internal',
            'body' => 'Diagnostic work completed; awaiting helpdesk close.',
        ])->assertRedirect();
        $this->assertDatabaseHas('ticket_comments', ['ticket_id' => $ticket->id, 'visibility' => 'internal']);
        $this->get(route('tickets.show', $ticket))->assertOk()->assertSee('Diagnostic work completed; awaiting helpdesk close.');

        $this->patch(route('tickets.status.update', $ticket), ['status' => TicketStatus::CLOSED])->assertSessionHasErrors('status');
        $this->assertSame(TicketStatus::RESOLVED, $ticket->fresh()->status);
    }

    public function test_requester_reopens_only_own_resolved_or_closed_ticket_and_clears_assignment(): void
    {
        $requester = $this->userWithRole(Roles::REQUESTER, 'Ticket Owner');
        $otherRequester = $this->userWithRole(Roles::REQUESTER, 'Other Owner');
        $technician = $this->userWithRole(Roles::TECHNICIAN, 'Assigned Technician');
        $closed = $this->ticket($requester, 'Closed issue', TicketStatus::CLOSED, $technician);
        $other = $this->ticket($otherRequester, 'Another closed issue', TicketStatus::CLOSED);

        $this->actingAs($requester)->post(route('tickets.reopen', $other))->assertNotFound();
        $this->post(route('tickets.reopen', $closed))->assertRedirect();

        $this->assertSame(TicketStatus::OPEN, $closed->fresh()->status);
        $this->assertNull($closed->fresh()->assigned_technician_id);
        $this->assertDatabaseHas('ticket_assignment_histories', [
            'ticket_id' => $closed->id,
            'from_technician_id' => $technician->id,
            'to_technician_id' => null,
            'actor_id' => $requester->id,
        ]);
        $this->assertDatabaseHas('ticket_status_histories', [
            'ticket_id' => $closed->id,
            'from_status' => TicketStatus::CLOSED,
            'to_status' => TicketStatus::OPEN,
        ]);
    }

    public function test_system_administrator_has_full_ticket_access_and_can_manage_empty_catalogues(): void
    {
        $administrator = $this->userWithRole(Roles::SYSTEM_ADMINISTRATOR, 'System Administrator');
        $this->assertDatabaseCount('ticket_categories', 0);
        $this->assertDatabaseCount('ticket_priorities', 0);

        $this->actingAs($administrator)->get(route('admin.ticket-catalogues.index'))->assertOk()->assertSee('No categories configured.');
        $this->get(route('tickets.index'))->assertOk()->assertSee('All Tickets');
        $this->post(route('admin.ticket-catalogues.categories.store'), [
            'name' => 'Approved Example Category', 'sort_order' => 10, 'is_active' => '1',
        ])->assertRedirect();
        $category = TicketCategory::query()->firstOrFail();
        $this->assertSame('approved-example-category', $category->slug);

        $priority = TicketPriority::query()->create([
            'name' => 'Approved Example Priority', 'slug' => 'approved-example-priority', 'sort_order' => 1, 'is_active' => true,
        ]);
        $requester = $this->userWithRole(Roles::REQUESTER, 'Synthetic Requester');
        $this->actingAs($requester)->post(route('tickets.store'), [
            'subject' => 'Catalogued issue', 'description' => 'An approved catalogue selection.',
            'ticket_category_id' => $category->id, 'ticket_priority_id' => $priority->id,
        ])->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'subject' => 'Catalogued issue', 'ticket_category_id' => $category->id, 'ticket_priority_id' => $priority->id,
        ]);

        $this->actingAs($requester)->get(route('admin.ticket-catalogues.index'))->assertForbidden();
    }

    public function test_system_administrator_receives_every_defined_permission(): void
    {
        $administrator = $this->userWithRole(Roles::SYSTEM_ADMINISTRATOR, 'Full Permission Administrator');
        $assignedPermissions = $administrator->roles()->with('permissions')->get()
            ->flatMap(fn (Role $role) => $role->permissions)
            ->pluck('slug')
            ->unique()
            ->all();

        $this->assertEqualsCanonicalizing(
            array_keys(Permissions::definitions()),
            $assignedPermissions,
        );
    }

    public function test_global_ticket_view_permission_alone_does_not_reveal_internal_notes(): void
    {
        $requester = $this->userWithRole(Roles::REQUESTER, 'Synthetic Requester');
        $viewer = User::factory()->create(['name' => 'Limited Ticket Viewer']);
        $role = Role::query()->create(['name' => 'Limited Ticket Viewer', 'slug' => 'limited-ticket-viewer']);
        $role->permissions()->attach(Permission::query()->where('slug', 'tickets.view_all')->value('id'));
        $viewer->roles()->attach($role);
        $ticket = $this->ticket($requester, 'Visible to limited viewer');
        $ticket->comments()->create(['author_id' => $requester->id, 'visibility' => 'public', 'body' => 'Public response.']);
        $ticket->comments()->create(['author_id' => $requester->id, 'visibility' => 'internal', 'body' => 'Restricted internal diagnosis.']);

        $this->actingAs($viewer)->get(route('tickets.show', $ticket))
            ->assertOk()
            ->assertSee('Public response.')
            ->assertDontSee('Restricted internal diagnosis.');
    }

    public function test_ticket_and_comment_audit_events_never_store_their_text_content(): void
    {
        $requester = $this->userWithRole(Roles::REQUESTER, 'Synthetic Requester');
        $helpdesk = $this->userWithRole(Roles::HELPDESK_OFFICER, 'Triage Officer');
        $this->actingAs($requester)->post(route('tickets.store'), [
            'subject' => 'Sensitive title example', 'description' => 'Sensitive description body example.',
        ])->assertRedirect();
        $ticket = Ticket::query()->firstOrFail();

        $this->actingAs($helpdesk)->post(route('tickets.comments.store', $ticket), [
            'visibility' => 'internal', 'body' => 'Sensitive internal note example.',
        ])->assertRedirect();

        $auditJson = json_encode(AuditEvent::query()->get()->toArray(), JSON_THROW_ON_ERROR);
        $this->assertStringNotContainsString('Sensitive title example', $auditJson);
        $this->assertStringNotContainsString('Sensitive description body example.', $auditJson);
        $this->assertStringNotContainsString('Sensitive internal note example.', $auditJson);
        $this->assertStringContainsString('ticket.created', $auditJson);
        $this->assertStringContainsString('ticket.comment_added', $auditJson);
    }

    private function userWithRole(string $roleSlug, string $name): User
    {
        $user = User::factory()->create(['name' => $name]);
        $role = Role::query()->where('slug', $roleSlug)->firstOrFail();
        $user->roles()->attach($role);

        return $user;
    }

    private function ticket(User $requester, string $subject, string $status = TicketStatus::OPEN, ?User $technician = null): Ticket
    {
        $ticket = new Ticket(['subject' => $subject, 'description' => 'Synthetic test description.']);
        $ticket->requester()->associate($requester);
        if ($technician !== null) {
            $ticket->assignedTechnician()->associate($technician);
        }
        $ticket->status = $status;
        $ticket->save();
        $ticket->statusHistory()->create([
            'from_status' => null,
            'to_status' => $status,
            'actor_id' => $requester->id,
        ]);

        return $ticket;
    }
}
