<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Campus;
use App\Models\OperationalZone;
use App\Models\Role;
use App\Models\Team;
use App\Models\TechnicianDeployment;
use App\Models\TechnicianTeamMembership;
use App\Models\Ticket;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TeamsAndDeploymentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_phase_three_catalogues_start_empty_and_only_system_administrators_can_manage_them(): void
    {
        $administrator = $this->userWithRole(Roles::SYSTEM_ADMINISTRATOR, 'System Administrator');
        $technician = $this->userWithRole(Roles::TECHNICIAN, 'Synthetic Technician');
        $requester = $this->userWithRole(Roles::REQUESTER, 'Synthetic Requester');

        $this->assertTrue($administrator->hasPermissionTo(Permissions::TEAMS_MANAGE));
        $this->assertFalse($technician->hasPermissionTo(Permissions::TEAMS_MANAGE));
        $this->assertFalse($requester->hasPermissionTo(Permissions::TEAMS_MANAGE));
        $this->assertDatabaseCount('teams', 0);
        $this->assertDatabaseCount('technician_team_memberships', 0);
        $this->assertDatabaseCount('technician_deployments', 0);

        $this->actingAs($requester)->get(route('admin.teams.index'))->assertForbidden();
        $this->get(route('admin.team-memberships.index'))->assertForbidden();
        $this->get(route('admin.technician-deployments.index'))->assertForbidden();
        $this->post(route('admin.teams.store'), ['name' => 'Unauthorized Team'])->assertForbidden();
        $this->post(route('admin.team-memberships.store'), [])->assertForbidden();
        $this->post(route('admin.technician-deployments.store'), [])->assertForbidden();
    }

    public function test_administrator_can_create_generic_teams_without_zone_mapping_or_seeded_names(): void
    {
        $administrator = $this->userWithRole(Roles::SYSTEM_ADMINISTRATOR, 'System Administrator');

        $this->actingAs($administrator)->post(route('admin.teams.store'), [
            'name' => 'Synthetic Field Support',
            'description' => 'Test-only team record.',
        ])->assertRedirect(route('admin.teams.index'));

        $team = Team::query()->firstOrFail();
        $this->assertSame('synthetic-field-support', $team->slug);
        $this->assertTrue($team->is_active);
        $this->assertFalse(Schema::hasColumn('teams', 'operational_zone_id'));
        $this->get(route('admin.teams.index'))->assertOk()->assertSee('Synthetic Field Support');
        $this->get(route('admin.teams.edit', $team))->assertOk();
        $this->get(route('admin.team-memberships.index'))->assertOk();
        $this->get(route('admin.team-memberships.create'))->assertOk();
        $this->get(route('admin.technician-deployments.index'))->assertOk();
        $this->get(route('admin.technician-deployments.create'))->assertOk();
        $this->put(route('admin.teams.update', $team), [
            'name' => 'Synthetic Field Support', 'slug' => 'synthetic-field-support', 'is_active' => '0',
        ])->assertRedirect(route('admin.teams.index'));
        $this->assertFalse($team->fresh()->is_active);
    }

    public function test_membership_periods_are_inclusive_open_ended_and_may_overlap_across_different_teams(): void
    {
        $administrator = $this->userWithRole(Roles::SYSTEM_ADMINISTRATOR, 'System Administrator');
        $technician = $this->userWithRole(Roles::TECHNICIAN, 'Membership Technician');
        $teamA = Team::query()->create(['name' => 'Team A', 'slug' => 'team-a', 'is_active' => true]);
        $teamB = Team::query()->create(['name' => 'Team B', 'slug' => 'team-b', 'is_active' => true]);

        $this->actingAs($administrator)->post(route('admin.team-memberships.store'), [
            'technician_id' => $technician->id, 'team_id' => $teamA->id,
            'starts_on' => '2026-06-01', 'ends_on' => '2026-06-30',
        ])->assertRedirect();

        $this->post(route('admin.team-memberships.store'), [
            'technician_id' => $technician->id, 'team_id' => $teamB->id,
            'starts_on' => '2026-06-15', 'ends_on' => '2026-07-15',
        ])->assertRedirect();

        $this->post(route('admin.team-memberships.store'), [
            'technician_id' => $technician->id, 'team_id' => $teamA->id,
            'starts_on' => '2026-06-30', 'ends_on' => '2026-07-31',
        ])->assertSessionHasErrors('starts_on');

        $this->post(route('admin.team-memberships.store'), [
            'technician_id' => $technician->id, 'team_id' => $teamA->id,
            'starts_on' => '2026-07-01',
        ])->assertRedirect();

        $this->post(route('admin.team-memberships.store'), [
            'technician_id' => $technician->id, 'team_id' => $teamA->id,
            'starts_on' => '2026-08-01', 'ends_on' => '2026-08-31',
        ])->assertSessionHasErrors('starts_on');

        $this->post(route('admin.team-memberships.store'), [
            'technician_id' => $technician->id, 'team_id' => $teamA->id,
            'starts_on' => '2026-07-01', 'ends_on' => '2026-09-01',
        ])->assertSessionHasErrors('starts_on');

        $this->post(route('admin.team-memberships.store'), [
            'technician_id' => $technician->id, 'team_id' => $teamA->id,
            'starts_on' => '2026-10-01', 'ends_on' => '2026-09-30',
        ])->assertSessionHasErrors('ends_on');

        $memberships = TechnicianTeamMembership::query()->orderBy('team_id')->get();
        $this->assertCount(3, $memberships);
        $ongoingMembership = $technician->teamMemberships()->where('team_id', $teamA->id)->where('starts_on', '2026-07-01')->firstOrFail();
        $this->assertNull($ongoingMembership->ends_on);
        $this->assertDatabaseHas('audit_events', ['action' => 'team_membership.created']);
    }

    public function test_database_unique_index_prevents_duplicate_membership_start_for_same_technician_and_team(): void
    {
        $technician = $this->userWithRole(Roles::TECHNICIAN, 'Unique Technician');
        $team = Team::query()->create(['name' => 'Unique Team', 'slug' => 'unique-team', 'is_active' => true]);
        TechnicianTeamMembership::query()->create([
            'technician_id' => $technician->id, 'team_id' => $team->id,
            'starts_on' => '2026-01-01', 'ends_on' => null,
        ]);

        try {
            DB::table('technician_team_memberships')->insert([
                'technician_id' => $technician->id,
                'team_id' => $team->id,
                'starts_on' => '2026-01-01',
                'ends_on' => '2026-02-01',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->fail('The database unique index must reject a duplicate technician/team start date.');
        } catch (QueryException) {
            $this->assertDatabaseCount('technician_team_memberships', 1);
        }
    }

    public function test_membership_requires_a_technician_role_and_existing_team(): void
    {
        $administrator = $this->userWithRole(Roles::SYSTEM_ADMINISTRATOR, 'System Administrator');
        $requester = $this->userWithRole(Roles::REQUESTER, 'Not a Technician');
        $this->actingAs($administrator)->post(route('admin.team-memberships.store'), [
            'technician_id' => $requester->id, 'team_id' => 999999,
            'starts_on' => '2026-01-01', 'ends_on' => '',
        ])->assertSessionHasErrors(['technician_id', 'team_id']);
        $this->assertDatabaseCount('technician_team_memberships', 0);
    }

    public function test_deployments_validate_dates_and_references_but_allow_overlapping_periods(): void
    {
        $administrator = $this->userWithRole(Roles::SYSTEM_ADMINISTRATOR, 'System Administrator');
        $technician = $this->userWithRole(Roles::TECHNICIAN, 'Deployment Technician');
        $campus = Campus::query()->create(['name' => 'Synthetic Campus', 'code' => 'SYNTH', 'is_active' => true]);
        $zone = OperationalZone::query()->create([
            'campus_id' => $campus->id, 'name' => 'Synthetic Zone', 'code' => 'ZONE-1', 'is_active' => true,
        ]);

        $payload = [
            'technician_id' => $technician->id,
            'operational_zone_id' => $zone->id,
            'starts_on' => '2026-05-01',
            'ends_on' => '2026-05-31',
        ];
        $this->actingAs($administrator)->post(route('admin.technician-deployments.store'), $payload)->assertRedirect();
        $this->post(route('admin.technician-deployments.store'), [
            ...$payload, 'starts_on' => '2026-05-15', 'ends_on' => '2026-06-15',
        ])->assertRedirect();
        $this->post(route('admin.technician-deployments.store'), [
            ...$payload, 'starts_on' => '2026-05-01', 'ends_on' => '2026-05-31',
        ])->assertRedirect();
        $this->post(route('admin.technician-deployments.store'), [
            ...$payload, 'operational_zone_id' => 999999,
        ])->assertSessionHasErrors('operational_zone_id');
        $this->post(route('admin.technician-deployments.store'), [
            'technician_id' => [$technician->id],
            'operational_zone_id' => [$zone->id],
            'starts_on' => ['2026-05-01'],
            'ends_on' => ['2026-05-31'],
        ])->assertSessionHasErrors(['technician_id', 'operational_zone_id', 'starts_on', 'ends_on']);
        $this->post(route('admin.technician-deployments.store'), [
            ...$payload, 'starts_on' => '2026-07-01', 'ends_on' => '2026-06-30',
        ])->assertSessionHasErrors('ends_on');

        $firstDeployment = TechnicianDeployment::query()->orderBy('id')->firstOrFail();
        $this->put(route('admin.technician-deployments.update', $firstDeployment), [
            ...$payload, 'ends_on' => '2026-05-20',
        ])->assertRedirect(route('admin.technician-deployments.index'));

        $deployments = TechnicianDeployment::query()->with('operationalZone.campus')->get();
        $this->assertCount(3, $deployments);
        $this->assertSame('2026-05-20', $deployments->first()->ends_on->format('Y-m-d'));
        $this->assertSame($zone->id, $deployments->first()->operationalZone->id);
        $this->assertSame($campus->id, $deployments->first()->operationalZone->campus->id);
        $this->assertDatabaseHas('audit_events', ['action' => 'technician_deployment.created']);
        $this->assertDatabaseHas('audit_events', ['action' => 'technician_deployment.updated']);
    }

    public function test_membership_update_rechecks_same_team_overlap_and_allows_nonconflicting_period_changes(): void
    {
        $administrator = $this->userWithRole(Roles::SYSTEM_ADMINISTRATOR, 'System Administrator');
        $technician = $this->userWithRole(Roles::TECHNICIAN, 'Updating Technician');
        $team = Team::query()->create(['name' => 'Update Team', 'slug' => 'update-team', 'is_active' => true]);
        $first = TechnicianTeamMembership::query()->create([
            'technician_id' => $technician->id, 'team_id' => $team->id,
            'starts_on' => '2026-01-01', 'ends_on' => '2026-01-31',
        ]);
        $second = TechnicianTeamMembership::query()->create([
            'technician_id' => $technician->id, 'team_id' => $team->id,
            'starts_on' => '2026-03-01', 'ends_on' => '2026-03-31',
        ]);

        $this->actingAs($administrator)->put(route('admin.team-memberships.update', $first), [
            'technician_id' => $technician->id, 'team_id' => $team->id,
            'starts_on' => '2026-01-01', 'ends_on' => '2026-02-28',
        ])->assertRedirect();
        $this->assertSame('2026-02-28', $first->fresh()->ends_on->format('Y-m-d'));

        $this->put(route('admin.team-memberships.update', $first), [
            'technician_id' => $technician->id, 'team_id' => $team->id,
            'starts_on' => '2026-01-01', 'ends_on' => '2026-03-01',
        ])->assertSessionHasErrors('starts_on');
        $this->assertSame('2026-03-01', $second->fresh()->starts_on->format('Y-m-d'));
        $this->assertDatabaseHas('audit_events', ['action' => 'team_membership.updated']);
    }

    public function test_memberships_and_deployments_do_not_grant_ticket_access(): void
    {
        $administrator = $this->userWithRole(Roles::SYSTEM_ADMINISTRATOR, 'System Administrator');
        $technician = $this->userWithRole(Roles::TECHNICIAN, 'Metadata-only Technician');
        $requester = $this->userWithRole(Roles::REQUESTER, 'Ticket Requester');
        $team = Team::query()->create(['name' => 'Metadata Team', 'slug' => 'metadata-team', 'is_active' => true]);
        $campus = Campus::query()->create(['name' => 'Metadata Campus', 'code' => 'META', 'is_active' => true]);
        $zone = OperationalZone::query()->create([
            'campus_id' => $campus->id, 'name' => 'Metadata Zone', 'code' => 'META-1', 'is_active' => true,
        ]);
        $this->actingAs($administrator)->post(route('admin.team-memberships.store'), [
            'technician_id' => $technician->id, 'team_id' => $team->id,
            'starts_on' => '2026-01-01', 'ends_on' => '',
        ])->assertRedirect();
        $this->post(route('admin.technician-deployments.store'), [
            'technician_id' => $technician->id, 'operational_zone_id' => $zone->id,
            'starts_on' => '2026-01-01', 'ends_on' => '2026-12-31',
        ])->assertRedirect();

        $ticket = new Ticket(['subject' => 'Unassigned sensitive issue', 'description' => 'Synthetic test record.']);
        $ticket->requester()->associate($requester);
        $ticket->save();

        $this->actingAs($technician)->get(route('tickets.show', $ticket))->assertNotFound();
        $this->get(route('tickets.index'))->assertOk()->assertDontSee('Unassigned sensitive issue');
        $this->assertNull($ticket->fresh()->assigned_technician_id);
        $this->assertFalse(Schema::hasColumn('tickets', 'team_id'));
        $this->assertFalse(Schema::hasColumn('tickets', 'operational_zone_id'));
    }

    public function test_foreign_keys_preserve_team_and_zone_references(): void
    {
        $technician = $this->userWithRole(Roles::TECHNICIAN, 'Referenced Technician');
        $team = Team::query()->create(['name' => 'Referenced Team', 'slug' => 'referenced-team', 'is_active' => true]);
        $campus = Campus::query()->create(['name' => 'Referenced Campus', 'code' => 'REF', 'is_active' => true]);
        $zone = OperationalZone::query()->create([
            'campus_id' => $campus->id, 'name' => 'Referenced Zone', 'code' => 'REF-1', 'is_active' => true,
        ]);
        TechnicianTeamMembership::query()->create([
            'technician_id' => $technician->id, 'team_id' => $team->id,
            'starts_on' => '2026-01-01', 'ends_on' => null,
        ]);
        TechnicianDeployment::query()->create([
            'technician_id' => $technician->id, 'operational_zone_id' => $zone->id,
            'starts_on' => '2026-01-01', 'ends_on' => '2026-01-31',
        ]);

        try {
            DB::table('teams')->where('id', $team->id)->delete();
            $this->fail('The team foreign key should preserve referenced membership history.');
        } catch (QueryException) {
            $this->assertDatabaseHas('teams', ['id' => $team->id]);
        }

        try {
            DB::table('operational_zones')->where('id', $zone->id)->delete();
            $this->fail('The zone foreign key should preserve referenced deployment history.');
        } catch (QueryException) {
            $this->assertDatabaseHas('operational_zones', ['id' => $zone->id]);
        }
    }

    private function userWithRole(string $roleSlug, string $name): User
    {
        $user = User::factory()->create(['name' => $name]);
        $role = Role::query()->where('slug', $roleSlug)->firstOrFail();
        $user->roles()->attach($role);

        return $user;
    }
}
