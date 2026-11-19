<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Models\UserGroup;
use App\Support\Roles;
use App\Support\TicketStatus;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AuditRemediationCoverageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_attachment_upload_is_rejected_instead_of_silently_discarded(): void
    {
        $requester = $this->userWithRole(Roles::REQUESTER);

        $response = $this->actingAs($requester)->post(route('tickets.store'), [
            'subject' => 'Ticket with attachment',
            'description' => 'Synthetic description long enough to be valid.',
            'attachments' => [UploadedFile::fake()->create('notes.pdf', 32, 'application/pdf')],
        ]);

        $response->assertSessionHasErrors('attachments');
        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_requester_cannot_update_a_ticket_category(): void
    {
        $category = TicketCategory::query()->create(['name' => 'Synthetic', 'slug' => 'synthetic', 'is_active' => true]);
        $requester = $this->userWithRole(Roles::REQUESTER);

        $this->actingAs($requester)
            ->patch(route('admin.ticket-catalogues.categories.update', $category), ['name' => 'Renamed'])
            ->assertForbidden();

        $this->assertSame('Synthetic', $category->fresh()->name);
    }

    public function test_administrator_can_update_a_ticket_category(): void
    {
        $category = TicketCategory::query()->create(['name' => 'Synthetic', 'slug' => 'synthetic', 'is_active' => true]);
        $administrator = $this->userWithRole(Roles::SYSTEM_ADMINISTRATOR);

        $this->actingAs($administrator)
            ->patch(route('admin.ticket-catalogues.categories.update', $category), [
                'name' => 'Renamed category',
                'sort_order' => 1,
                'is_active' => true,
            ])->assertRedirect();

        $this->assertSame('Renamed category', $category->fresh()->name);
    }

    public function test_administrator_can_rename_a_user_group(): void
    {
        $group = UserGroup::query()->create(['name' => 'Original', 'slug' => 'original', 'is_active' => true]);
        $administrator = $this->userWithRole(Roles::SYSTEM_ADMINISTRATOR);

        $this->actingAs($administrator)
            ->patch(route('admin.user-groups.update', $group), [
                'name' => 'Renamed group',
                'is_active' => true,
            ])->assertRedirect();

        $this->assertSame('Renamed group', $group->fresh()->name);
    }

    public function test_requester_cannot_open_the_assignment_form(): void
    {
        $requester = $this->userWithRole(Roles::REQUESTER);
        $ticket = $this->ticket($requester);

        $this->actingAs($requester)
            ->get(route('tickets.assignment.edit', $ticket))
            ->assertForbidden();
    }

    public function test_open_ticket_cannot_be_reopened(): void
    {
        $requester = $this->userWithRole(Roles::REQUESTER);
        $officer = $this->userWithRole(Roles::HELPDESK_OFFICER);
        $ticket = $this->ticket($requester, TicketStatus::OPEN);

        $this->actingAs($officer)
            ->post(route('tickets.reopen', $ticket))
            ->assertForbidden();

        $this->assertSame(TicketStatus::OPEN, $ticket->fresh()->status);
    }

    private function userWithRole(string $slug): User
    {
        $user = User::factory()->create();
        $role = Role::query()->where('slug', $slug)->firstOrFail();
        $user->roles()->attach($role);

        return $user;
    }

    private function ticket(User $requester, string $status = TicketStatus::OPEN): Ticket
    {
        $ticket = new Ticket(['subject' => 'Synthetic ticket', 'description' => 'Synthetic description.']);
        $ticket->requester()->associate($requester);
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
