<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketPriority;
use App\Models\User;
use App\Support\Roles;
use App\Support\TicketStatus;
use Carbon\CarbonImmutable;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_only_users_with_global_triage_permission_can_view_aggregate_reports(): void
    {
        $helpdesk = $this->userWithRole(Roles::HELPDESK_OFFICER, 'Synthetic Helpdesk Officer');
        $requester = $this->userWithRole(Roles::REQUESTER, 'Synthetic Requester');
        $technician = $this->userWithRole(Roles::TECHNICIAN, 'Synthetic Technician');
        $administrator = $this->userWithRole(Roles::SYSTEM_ADMINISTRATOR, 'Synthetic Administrator');

        $this->actingAs($requester)->get(route('reports.tickets.index'))->assertForbidden();
        $this->actingAs($technician)->get(route('reports.tickets.index'))->assertForbidden();
        $this->actingAs($administrator)->get(route('reports.tickets.index'))->assertForbidden();
        $this->get(route('dashboard'))
            ->assertSee('Reports')
            ->assertSee('aria-disabled="true">Ticket Reports</span>', false)
            ->assertDontSee(route('reports.tickets.index'));
        $this->actingAs($helpdesk)->get(route('reports.tickets.index'))->assertOk()->assertSee('Ticket analytics');

        $this->get(route('dashboard'))->assertSee('Reports');
    }

    public function test_report_returns_aggregates_for_inclusive_created_date_range_without_ticket_or_identity_details(): void
    {
        $helpdesk = $this->userWithRole(Roles::HELPDESK_OFFICER, 'Synthetic Helpdesk Officer');
        $requester = $this->userWithRole(Roles::REQUESTER, 'Confidential Requester Name');
        $category = TicketCategory::query()->create(['name' => 'Approved Hardware', 'slug' => 'approved-hardware', 'sort_order' => 1, 'is_active' => true]);
        $priority = TicketPriority::query()->create(['name' => 'Normal Priority', 'slug' => 'normal-priority', 'sort_order' => 1, 'is_active' => true]);

        $this->ticket($requester, 'Sensitive ticket title alpha', 'Private issue body alpha.', TicketStatus::OPEN, '2026-03-01', $category, $priority);
        $this->ticket($requester, 'Sensitive ticket title beta', 'Private issue body beta.', TicketStatus::RESOLVED, '2026-03-02', $category, $priority);
        $this->ticket($requester, 'Sensitive ticket title gamma', 'Private issue body gamma.', TicketStatus::OPEN, '2026-03-02');
        $this->ticket($requester, 'Outside date range', 'This record must not be counted.', TicketStatus::CLOSED, '2026-02-28');
        $this->ticket($requester, 'After range', 'This record must also be excluded.', TicketStatus::CLOSED, '2026-03-03');

        $this->actingAs($helpdesk)->get(route('reports.tickets.index', [
            'from' => '2026-03-01', 'through' => '2026-03-02',
        ]))
            ->assertOk()
            ->assertSee('Tickets created during this period')
            ->assertSee('Approved Hardware')
            ->assertSee('Normal Priority')
            ->assertSee('Uncategorized')
            ->assertSee('2026-03-01')
            ->assertSee('2026-03-02')
            ->assertSee('2')
            ->assertDontSee('Sensitive ticket title alpha')
            ->assertDontSee('Private issue body alpha.')
            ->assertDontSee('Confidential Requester Name')
            ->assertDontSee($requester->email);
    }

    public function test_empty_report_renders_zeroes_and_a_default_bounded_period(): void
    {
        $helpdesk = $this->userWithRole(Roles::HELPDESK_OFFICER, 'Synthetic Helpdesk Officer');

        $this->actingAs($helpdesk)->get(route('reports.tickets.index'))
            ->assertOk()
            ->assertSee('Tickets created during this period')
            ->assertSee('No tickets were created in this period.');
    }

    public function test_report_date_bounds_must_be_paired_ordered_and_at_most_366_inclusive_days(): void
    {
        $helpdesk = $this->userWithRole(Roles::HELPDESK_OFFICER, 'Synthetic Helpdesk Officer');
        $this->actingAs($helpdesk)->get(route('reports.tickets.index', ['from' => '2026-01-01']))
            ->assertSessionHasErrors('through');
        $this->get(route('reports.tickets.index', ['from' => '2026-03-02', 'through' => '2026-03-01']))
            ->assertSessionHasErrors('through');
        $this->get(route('reports.tickets.index', ['from' => '2025-01-01', 'through' => '2026-01-02']))
            ->assertSessionHasErrors('through');
        $this->get(route('reports.tickets.index', ['from' => '2025-01-01', 'through' => '2026-01-01']))
            ->assertOk();
        $this->get(route('reports.tickets.index', ['from' => ['2025-01-01'], 'through' => ['2026-01-01']]))
            ->assertSessionHasErrors(['from', 'through']);
    }

    private function userWithRole(string $roleSlug, string $name): User
    {
        $user = User::factory()->create(['name' => $name]);
        $role = Role::query()->where('slug', $roleSlug)->firstOrFail();
        $user->roles()->attach($role);

        return $user;
    }

    private function ticket(
        User $requester,
        string $subject,
        string $description,
        string $status,
        string $createdAt,
        ?TicketCategory $category = null,
        ?TicketPriority $priority = null
    ): Ticket {
        $ticket = new Ticket(['subject' => $subject, 'description' => $description]);
        $ticket->requester()->associate($requester);
        $ticket->status = $status;
        if ($category !== null) {
            $ticket->category()->associate($category);
        }
        if ($priority !== null) {
            $ticket->priority()->associate($priority);
        }
        $ticket->save();
        $ticket->created_at = CarbonImmutable::createFromFormat('!Y-m-d', $createdAt, (string) config('app.timezone'));
        $ticket->save();

        return $ticket;
    }
}
