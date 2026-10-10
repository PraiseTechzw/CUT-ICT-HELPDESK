<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AuditEvent;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketActivityNotification;
use App\Support\Roles;
use App\Support\TicketStatus;
use Carbon\CarbonImmutable;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use LogicException;
use Tests\TestCase;

class SystemAuditAndNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_ticket_creation_notifies_active_helpdesk_in_app_without_ticket_content(): void
    {
        $requester = $this->userWithRole(Roles::REQUESTER, 'Ticket Requester');
        $helpdesk = $this->userWithRole(Roles::HELPDESK_OFFICER, 'Helpdesk Officer');
        $inactiveHelpdesk = $this->userWithRole(Roles::HELPDESK_OFFICER, 'Inactive Helpdesk', false);

        $this->actingAs($requester)->post(route('tickets.store'), [
            'subject' => 'Confidential equipment issue',
            'description' => 'Private request details must not be copied into a notification.',
        ])->assertRedirect();

        $notice = $helpdesk->notifications()->firstOrFail();
        $this->assertSame('ticket.created', $notice->data['event']);
        $this->assertSame('A new ticket is waiting for helpdesk triage.', $notice->data['message']);
        $this->assertArrayHasKey('ticket_id', $notice->data);
        $this->assertSame(['ticket_id', 'event', 'message'], array_keys($notice->data));
        $this->assertStringNotContainsString('Confidential equipment issue', json_encode($notice->data, JSON_THROW_ON_ERROR));
        $this->assertStringNotContainsString('Private request details', json_encode($notice->data, JSON_THROW_ON_ERROR));
        $this->assertSame(0, $inactiveHelpdesk->notifications()->count());
        $this->assertSame(0, $requester->notifications()->count());
        $this->assertSame(['database'], (new TicketActivityNotification(1, 'test.event', 'A generic message.'))->via($helpdesk));
    }

    public function test_public_comment_notification_excludes_comment_body_and_internal_note_never_notifies_requester(): void
    {
        $requester = $this->userWithRole(Roles::REQUESTER, 'Ticket Requester');
        $technician = $this->userWithRole(Roles::TECHNICIAN, 'Assigned Technician');
        $helpdesk = $this->userWithRole(Roles::HELPDESK_OFFICER, 'Helpdesk Officer');
        $ticket = $this->ticket($requester, 'Private ticket subject', TicketStatus::ASSIGNED, $technician);

        $this->actingAs($requester)->post(route('tickets.comments.store', $ticket), [
            'visibility' => 'public',
            'body' => 'Sensitive requester reply content.',
        ])->assertRedirect();

        $publicNotice = $technician->notifications()->firstOrFail();
        $this->assertSame('ticket.public_reply_added', $publicNotice->data['event']);
        $this->assertStringNotContainsString('Sensitive requester reply content.', json_encode($publicNotice->data, JSON_THROW_ON_ERROR));
        $this->assertSame(0, $requester->notifications()->count());

        $this->actingAs($technician)->post(route('tickets.comments.store', $ticket), [
            'visibility' => 'internal',
            'body' => 'Sensitive internal work note content.',
        ])->assertRedirect();

        $helpdeskNotice = $helpdesk->notifications()->latest()->firstOrFail();
        $this->assertSame('ticket.internal_note_added', $helpdeskNotice->data['event']);
        $this->assertStringNotContainsString('Sensitive internal work note content.', json_encode($helpdeskNotice->data, JSON_THROW_ON_ERROR));
        $this->assertSame(0, $requester->notifications()->count());
    }

    public function test_notification_inbox_is_private_and_opening_marks_only_the_owners_notice_read(): void
    {
        $requester = $this->userWithRole(Roles::REQUESTER, 'Ticket Requester');
        $otherRequester = $this->userWithRole(Roles::REQUESTER, 'Other Requester');
        $ticket = $this->ticket($requester, 'Requester-owned ticket');
        $ownNotice = new TicketActivityNotification((int) $ticket->id, 'ticket.status_changed', 'A generic status update.');
        $requester->notify($ownNotice);
        $otherNotice = new TicketActivityNotification((int) $ticket->id, 'ticket.status_changed', 'Private other-user notice.');
        $otherRequester->notify($otherNotice);

        $ownNotification = $requester->notifications()->firstOrFail();
        $otherNotification = $otherRequester->notifications()->firstOrFail();

        $this->actingAs($requester)->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('A generic status update.')
            ->assertSee('Open ticket')
            ->assertDontSee('Private other-user notice.');

        $this->post(route('notifications.open', $otherNotification->id))->assertNotFound();
        $this->assertNull($otherNotification->fresh()->read_at);
        $this->post(route('notifications.open', $ownNotification->id))
            ->assertRedirect(route('tickets.show', $ticket));
        $this->assertNotNull($ownNotification->fresh()->read_at);
    }

    public function test_audit_filters_are_server_authorized_and_include_both_local_calendar_date_boundaries(): void
    {
        $auditor = $this->userWithRole(Roles::AUDITOR, 'Audit Analyst');
        $otherActor = $this->userWithRole(Roles::HELPDESK_OFFICER, 'Triage Officer');
        $start = CarbonImmutable::now()->startOfDay()->subDay();
        $end = CarbonImmutable::now()->endOfDay();

        $this->insertAuditEvent($auditor, 'ticket.created', $start);
        $this->insertAuditEvent($auditor, 'ticket.status_changed', $end);
        $this->insertAuditEvent($otherActor, 'ticket.created', $start->subDay());

        $this->actingAs($auditor)->get(route('admin.audit.index', [
            'action' => 'ticket.',
            'actor' => 'Audit Analyst',
            'from' => $start->toDateString(),
            'through' => $end->toDateString(),
        ]))
            ->assertOk()
            ->assertSee('ticket.created')
            ->assertSee('ticket.status_changed')
            ->assertDontSee('Triage Officer');

        $this->get(route('admin.audit.index', ['from' => $end->toDateString()]))
            ->assertSessionHasErrors('from');

        $requester = $this->userWithRole(Roles::REQUESTER, 'Regular Requester');
        $this->actingAs($requester)->get(route('admin.audit.index'))->assertForbidden();
    }

    public function test_audit_event_model_rejects_instance_updates_and_deletes(): void
    {
        $auditor = $this->userWithRole(Roles::AUDITOR, 'Audit Analyst');
        $event = AuditEvent::query()->create([
            'actor_id' => $auditor->id,
            'action' => 'test.recorded',
            'subject_type' => User::class,
            'subject_id' => $auditor->id,
            'metadata' => ['field' => 'safe value'],
        ]);

        try {
            $event->action = 'test.edited';
            $event->save();
            $this->fail('Expected an append-only audit event to reject updates.');
        } catch (LogicException $exception) {
            $this->assertSame('Audit events are append-only.', $exception->getMessage());
        }

        try {
            $event->delete();
            $this->fail('Expected an append-only audit event to reject deletes.');
        } catch (LogicException $exception) {
            $this->assertSame('Audit events are append-only.', $exception->getMessage());
        }

        $this->assertDatabaseHas('audit_events', ['id' => $event->id, 'action' => 'test.recorded']);
    }

    private function insertAuditEvent(User $actor, string $action, CarbonImmutable $createdAt): void
    {
        DB::table('audit_events')->insert([
            'actor_id' => $actor->id,
            'action' => $action,
            'subject_type' => Ticket::class,
            'subject_id' => 1,
            'metadata' => json_encode(['safe' => true], JSON_THROW_ON_ERROR),
            'created_at' => $createdAt->utc()->format('Y-m-d H:i:s'),
        ]);
    }

    private function userWithRole(string $roleSlug, string $name, bool $active = true): User
    {
        $user = User::factory()->create(['name' => $name, 'is_active' => $active]);
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
