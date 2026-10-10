<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Building;
use App\Models\Campus;
use App\Models\OperationalZone;
use App\Models\Role;
use App\Models\Room;
use App\Models\User;
use App\Support\Roles;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LocationAdministrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_requester_cannot_manage_campus_or_zone_data(): void
    {
        $requester = $this->userWithRole(Roles::REQUESTER);
        $campus = Campus::query()->create(['name' => 'Guarded Campus', 'code' => 'GUARDED', 'is_active' => true]);
        $zone = OperationalZone::query()->create([
            'campus_id' => $campus->id, 'name' => 'Guarded Zone', 'code' => 'ZONE-1', 'is_active' => true,
        ]);

        $this->actingAs($requester)->get('/admin/campuses')->assertOk();
        $this->actingAs($requester)->get('/admin/campuses/create')->assertForbidden();
        $this->actingAs($requester)->post('/admin/campuses', [])->assertForbidden();
        $this->actingAs($requester)->get(route('admin.campuses.edit', $campus))->assertForbidden();
        $this->actingAs($requester)->put(route('admin.campuses.update', $campus), [])->assertForbidden();
        $this->actingAs($requester)->post('/admin/zones', [])->assertForbidden();
        $this->actingAs($requester)->get(route('admin.zones.edit', $zone))->assertForbidden();
        $this->actingAs($requester)->put(route('admin.zones.update', $zone), [])->assertForbidden();
    }

    public function test_administrator_can_create_campus_zone_building_and_room_hierarchy(): void
    {
        $administrator = $this->userWithRole(Roles::SYSTEM_ADMINISTRATOR);

        $response = $this->actingAs($administrator)->post('/admin/campuses', [
            'name' => 'Synthetic Campus', 'code' => 'synth', 'description' => 'Test-only location', 'is_active' => '1',
        ]);
        $response->assertRedirect(route('admin.campuses.index'));
        $campus = Campus::query()->where('code', 'SYNTH')->firstOrFail();

        $response = $this->post('/admin/zones', [
            'campus_id' => $campus->id, 'name' => 'Synthetic Zone', 'code' => 'zone-01', 'is_active' => '1',
        ]);
        $response->assertRedirect(route('admin.zones.index'));
        $zone = OperationalZone::query()->where('campus_id', $campus->id)->firstOrFail();

        $response = $this->post('/admin/buildings', [
            'campus_id' => $campus->id, 'operational_zone_id' => $zone->id,
            'name' => 'Synthetic Building', 'code' => 'build-01', 'is_active' => '1',
        ]);
        $response->assertRedirect(route('admin.buildings.index'));
        $building = Building::query()->where('code', 'BUILD-01')->firstOrFail();

        $response = $this->post('/admin/rooms', [
            'building_id' => $building->id, 'name' => 'Synthetic Room', 'code' => 'room-01', 'is_active' => '1',
        ]);
        $response->assertRedirect(route('admin.rooms.index'));
        $room = Room::query()->where('code', 'ROOM-01')->firstOrFail();

        $this->assertSame($campus->id, $zone->campus_id);
        $this->assertSame($campus->id, $building->campus_id);
        $this->assertSame($zone->id, $building->operational_zone_id);
        $this->assertSame($building->id, $room->building_id);
        $this->assertDatabaseHas('audit_events', ['action' => 'room.created']);
    }

    public function test_zone_requires_a_valid_campus_and_codes_are_unique_within_that_campus(): void
    {
        $administrator = $this->userWithRole(Roles::SYSTEM_ADMINISTRATOR);
        $campus = Campus::query()->create(['name' => 'Campus One', 'code' => 'CAMPUS-1', 'is_active' => true]);

        $this->actingAs($administrator)->post('/admin/zones', [
            'campus_id' => 999999, 'name' => 'Invalid Zone', 'code' => 'ZONE-1',
        ])->assertSessionHasErrors('campus_id');

        $this->post('/admin/zones', [
            'campus_id' => $campus->id, 'name' => 'Zone One', 'code' => 'ZONE-1',
        ])->assertRedirect(route('admin.zones.index'));

        $this->post('/admin/zones', [
            'campus_id' => $campus->id, 'name' => 'Duplicate Zone', 'code' => 'ZONE-1',
        ])->assertSessionHasErrors('code');

        $secondCampus = Campus::query()->create(['name' => 'Campus Two', 'code' => 'CAMPUS-2', 'is_active' => true]);
        $this->post('/admin/zones', [
            'campus_id' => $secondCampus->id, 'name' => 'Same Code Other Campus', 'code' => 'ZONE-1',
        ])->assertRedirect(route('admin.zones.index'));
    }

    public function test_building_rejects_a_zone_from_another_campus(): void
    {
        $administrator = $this->userWithRole(Roles::SYSTEM_ADMINISTRATOR);
        $campusA = Campus::query()->create(['name' => 'Campus A', 'code' => 'A', 'is_active' => true]);
        $campusB = Campus::query()->create(['name' => 'Campus B', 'code' => 'B', 'is_active' => true]);
        $zoneA = OperationalZone::query()->create(['campus_id' => $campusA->id, 'name' => 'Zone A', 'code' => 'A1', 'is_active' => true]);

        $this->actingAs($administrator)->post('/admin/buildings', [
            'campus_id' => $campusB->id, 'operational_zone_id' => $zoneA->id,
            'name' => 'Invalid Building', 'code' => 'BLDG-1',
        ])->assertSessionHasErrors('operational_zone_id');

        $this->assertDatabaseCount('buildings', 0);
    }

    public function test_duplicate_location_codes_are_rejected_by_validation(): void
    {
        $administrator = $this->userWithRole(Roles::SYSTEM_ADMINISTRATOR);
        Campus::query()->create(['name' => 'Campus One', 'code' => 'DUPLICATE', 'is_active' => true]);

        $this->actingAs($administrator)->post('/admin/campuses', [
            'name' => 'Campus Duplicate', 'code' => 'duplicate',
        ])->assertSessionHasErrors('code');
    }

    public function test_referenced_campuses_are_protected_from_physical_deletion(): void
    {
        $campus = Campus::query()->create(['name' => 'Protected Campus', 'code' => 'PROTECTED', 'is_active' => true]);
        OperationalZone::query()->create(['campus_id' => $campus->id, 'name' => 'Protected Zone', 'code' => 'Z1', 'is_active' => true]);

        try {
            DB::table('campuses')->where('id', $campus->id)->delete();
            $this->fail('The campus foreign key should prevent deletion of a referenced record.');
        } catch (QueryException) {
            $this->assertDatabaseHas('campuses', ['id' => $campus->id]);
        }
    }

    public function test_location_admin_screens_render_for_authorized_users(): void
    {
        $administrator = $this->userWithRole(Roles::SYSTEM_ADMINISTRATOR);

        $this->actingAs($administrator)->get('/admin/campuses')->assertOk()->assertSee('Campuses');
        $this->get('/admin/zones')->assertOk()->assertSee('Operational zones');
        $this->get('/admin/buildings')->assertOk()->assertSee('Buildings');
        $this->get('/admin/rooms')->assertOk()->assertSee('Rooms');
        $this->get('/admin/campuses/create')->assertOk();
    }

    private function userWithRole(string $slug): User
    {
        $user = User::factory()->create();
        $role = Role::query()->where('slug', $slug)->firstOrFail();
        $user->roles()->attach($role);

        return $user;
    }
}
