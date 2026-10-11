<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use App\Support\Roles;
use App\Support\TicketStatus;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\TicketDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TicketDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_ticket_demo_seeder_populates_ticket_workflows_and_is_safe_to_rerun(): void
    {
        $this->seed(TicketDemoSeeder::class);
        $this->seed(TicketDemoSeeder::class);

        $this->assertDatabaseCount('ticket_categories', 6);
        $this->assertDatabaseCount('ticket_priorities', 5);
        $this->assertDatabaseCount('tickets', 12);
        $this->assertSame(6, Ticket::query()->distinct()->count('status'));
        $this->assertSame(7, Ticket::query()->whereNotNull('due_at')->where('due_at', '<', now())->whereNotIn('status', [TicketStatus::RESOLVED, TicketStatus::CLOSED])->count());
        $this->assertGreaterThan(0, Ticket::query()->has('comments')->count());
        $this->assertGreaterThan(0, Ticket::query()->has('statusHistory')->count());
        $this->assertGreaterThan(0, Ticket::query()->has('assignmentHistory')->count());
        $this->assertTrue(Hash::check('DemoTicket123!', User::query()->where('email', 'demo.requester.one@example.test')->firstOrFail()->password));

        $helpdesk = User::query()->where('email', 'demo.helpdesk@example.test')->firstOrFail();
        $this->actingAs($helpdesk)->get(route('tickets.index'))
            ->assertOk()
            ->assertSee('Campus Wi-Fi disconnects in lecture rooms')
            ->assertSee('Closed');
        $administrator = User::factory()->create(['name' => 'Demo Catalogue Administrator']);
        $administrator->roles()->attach(Role::query()->where('slug', Roles::SYSTEM_ADMINISTRATOR)->firstOrFail());
        $this->actingAs($administrator)->get(route('admin.ticket-catalogues.categories.index'))
            ->assertOk()
            ->assertSee('Category Title')
            ->assertSee('Total Tickets')
            ->assertSee('Network')
            ->assertSee('Others')
            ->assertSee('Save Category');
        $this->get(route('admin.ticket-catalogues.priorities.index'))
            ->assertOk()
            ->assertSee('Priority Name')
            ->assertSee('Color')
            ->assertSee('Critical')
            ->assertSee('Trivial');
        $this->get(route('tickets.overdue'))
            ->assertOk()
            ->assertSee('You have 7 overdue tickets that require attention.')
            ->assertSee('Days Overdue')
            ->assertSee('Conference room display has no signal');

        $requester = User::query()->where('email', 'demo.requester.one@example.test')->firstOrFail();
        $this->actingAs($requester)->get(route('tickets.mine'))
            ->assertOk()
            ->assertSee('My Tickets')
            ->assertSee('View and track tickets you requested or are assigned to you.');
    }
}
