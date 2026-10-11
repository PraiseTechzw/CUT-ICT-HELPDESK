<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use App\Support\Roles;
use App\Support\TicketStatus;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_requester_dashboard_uses_live_counts_and_only_shows_tickets_the_requester_can_view(): void
    {
        $requester = $this->userWithRole(Roles::REQUESTER, 'Dashboard Requester');
        $otherRequester = $this->userWithRole(Roles::REQUESTER, 'Other Requester');
        $this->ticket($requester, 'Visible dashboard issue');
        $this->ticket($otherRequester, 'Private dashboard issue');

        $this->actingAs($requester)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Ticket Trends')
            ->assertSee('Recent Tickets')
            ->assertSee('Visible dashboard issue')
            ->assertDontSee('Private dashboard issue')
            ->assertSee('Total tickets')
            ->assertSee('1');
    }

    public function test_administrator_dashboard_does_not_expose_ticket_metrics_without_ticket_permission(): void
    {
        $administrator = $this->userWithRole(Roles::SYSTEM_ADMINISTRATOR, 'Dashboard Administrator');
        $requester = $this->userWithRole(Roles::REQUESTER, 'Ticket Owner');
        $this->ticket($requester, 'Restricted ticket subject');

        $this->actingAs($administrator)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Active users')
            ->assertSee('Current System Status')
            ->assertSee('Ticket Trends')
            ->assertSee('Recent Tickets')
            ->assertSee('Ticket data is restricted')
            ->assertSee('Ticket details are restricted for this account.')
            ->assertDontSee('Restricted ticket subject')
            ->assertDontSee('Total tickets');
    }

    private function userWithRole(string $roleSlug, string $name): User
    {
        $user = User::factory()->create(['name' => $name]);
        $user->roles()->attach(Role::query()->where('slug', $roleSlug)->firstOrFail());

        return $user;
    }

    private function ticket(User $requester, string $subject): Ticket
    {
        $ticket = new Ticket(['subject' => $subject, 'description' => 'Synthetic dashboard test issue.']);
        $ticket->requester()->associate($requester);
        $ticket->status = TicketStatus::OPEN;
        $ticket->save();
        $ticket->statusHistory()->create([
            'from_status' => null,
            'to_status' => TicketStatus::OPEN,
            'actor_id' => $requester->id,
        ]);

        return $ticket;
    }
}
