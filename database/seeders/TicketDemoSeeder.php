<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketPriority;
use App\Models\User;
use App\Support\Roles;
use App\Support\TicketStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class TicketDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Ticket demo data can only be seeded in local or testing environments.');
        }

        $this->call(RolePermissionSeeder::class);

        DB::transaction(function (): void {
            $categories = $this->seedCategories();
            $priorities = $this->seedPriorities();
            $requesters = [
                $this->seedUser('demo.requester.one@example.test', 'Demo Requester One', Roles::REQUESTER),
                $this->seedUser('demo.requester.two@example.test', 'Demo Requester Two', Roles::REQUESTER),
            ];
            $technicians = [
                $this->seedUser('demo.technician.one@example.test', 'Demo Technician One', Roles::TECHNICIAN),
                $this->seedUser('demo.technician.two@example.test', 'Demo Technician Two', Roles::TECHNICIAN),
            ];
            $helpdesk = $this->seedUser('demo.helpdesk@example.test', 'Demo Helpdesk Officer', Roles::HELPDESK_OFFICER);

            $examples = [
                ['Campus Wi-Fi disconnects in lecture rooms', 'Network', 'High', TicketStatus::OPEN, 0, null, -3],
                ['Laptop battery no longer charges', 'Hardware', 'Medium', TicketStatus::ASSIGNED, 1, 0, -2],
                ['Unable to open shared department drive', 'Network', 'High', TicketStatus::IN_PROGRESS, 0, 1, -1],
                ['Email account keeps requesting sign-in', 'Account', 'Low', TicketStatus::WAITING_FOR_REQUESTER, 1, 0, 4],
                ['Projector displays a blue screen', 'AV Equipment', 'Medium', TicketStatus::OPEN, 0, null, -2],
                ['Install approved statistical software', 'Software', 'Low', TicketStatus::RESOLVED, 1, 1, null],
                ['Student portal access needs restoring', 'Account', 'High', TicketStatus::IN_PROGRESS, 0, 0, -5],
                ['Printer queue is stuck on pending', 'Hardware', 'Medium', TicketStatus::CLOSED, 1, 1, null],
                ['Conference room display has no signal', 'AV Equipment', 'Critical', TicketStatus::ASSIGNED, 0, 0, -1],
                ['New staff mailbox setup request', 'Account', 'Low', TicketStatus::RESOLVED, 0, 1, null],
                ['Network port activation for lab desk', 'Network', 'Medium', TicketStatus::WAITING_FOR_REQUESTER, 1, 0, -2],
                ['Keyboard keys intermittently stop responding', 'Hardware', 'Low', TicketStatus::CLOSED, 0, 1, null],
            ];

            foreach ($examples as $index => [$subject, $category, $priority, $status, $requesterIndex, $technicianIndex, $dueInDays]) {
                $requester = $requesters[$requesterIndex];
                $technician = $technicianIndex === null ? null : $technicians[$technicianIndex];
                $createdAt = now()->subDays(11 - $index)->setTime(8 + ($index % 8), ($index * 7) % 60);
                $ticket = Ticket::query()
                    ->where('requester_id', $requester->id)
                    ->where('subject', $subject)
                    ->first() ?? new Ticket();
                $ticket->requester()->associate($requester);
                $ticket->assignedTechnician()->associate($technician);
                $ticket->category()->associate($categories[$category]);
                $ticket->priority()->associate($priorities[$priority]);
                $ticket->fill(['subject' => $subject, 'description' => $this->descriptionFor($subject)]);
                $ticket->status = $status;
                $ticket->due_at = $dueInDays === null ? null : now()->addDays($dueInDays);
                $ticket->save();
                $ticket->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt->copy()->addHours($index % 6)])->save();

                $this->seedStatusHistory($ticket, $status, $helpdesk, $technician, $createdAt);
                $this->seedComments($ticket, $requester, $helpdesk, $technician);
                if ($technician !== null) {
                    $ticket->assignmentHistory()->firstOrCreate(
                        ['from_technician_id' => null, 'to_technician_id' => $technician->id],
                        ['actor_id' => $helpdesk->id],
                    );
                }
            }
        });
    }

    /** @return array<string, TicketCategory> */
    private function seedCategories(): array
    {
        $definitions = [
            ['Network', 'network', 'Connectivity, Wi-Fi, and network access.'],
            ['Hardware', 'hardware', 'Computers, printers, and physical equipment.'],
            ['Software', 'software', 'Applications, installation, and software faults.'],
            ['Account', 'account', 'Email, sign-in, and account access.'],
            ['AV Equipment', 'av-equipment', 'Projectors, displays, and meeting-room equipment.'],
            ['Others', 'others', 'Other miscellaneous support requests.'],
        ];
        $categories = [];

        foreach ($definitions as $order => [$name, $slug, $description]) {
            $categories[$name] = TicketCategory::query()->updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'description' => $description, 'sort_order' => $order + 1, 'is_active' => true],
            );
        }

        return $categories;
    }

    /** @return array<string, TicketPriority> */
    private function seedPriorities(): array
    {
        $definitions = [
            ['Critical', 'critical', '#df4141', 'Urgent service interruption affecting multiple users.'],
            ['High', 'high', '#ff8a3d', 'Issue significantly interrupts work.'],
            ['Medium', 'medium', '#f3b638', 'Issue affects work but has a temporary workaround.'],
            ['Low', 'low', '#2f9e69', 'Minor issue with a reasonable workaround.'],
            ['Trivial', 'trivial', '#8292a2', 'Very low impact or informational request.'],
        ];
        $priorities = [];

        foreach ($definitions as $order => [$name, $slug, $color, $description]) {
            $priorities[$name] = TicketPriority::query()->updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'color' => $color, 'description' => $description, 'sort_order' => $order + 1, 'is_active' => true],
            );
        }

        return $priorities;
    }

    private function seedUser(string $email, string $name, string $roleSlug): User
    {
        $user = User::query()->updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => Hash::make('DemoTicket123!'), 'is_active' => true],
        );
        $role = Role::query()->where('slug', $roleSlug)->firstOrFail();
        $user->roles()->syncWithoutDetaching([$role->id]);

        return $user;
    }

    private function seedStatusHistory(Ticket $ticket, string $finalStatus, User $helpdesk, ?User $technician, \Illuminate\Support\Carbon $createdAt): void
    {
        $progression = [
            TicketStatus::OPEN => [TicketStatus::OPEN],
            TicketStatus::ASSIGNED => [TicketStatus::OPEN, TicketStatus::ASSIGNED],
            TicketStatus::IN_PROGRESS => [TicketStatus::OPEN, TicketStatus::ASSIGNED, TicketStatus::IN_PROGRESS],
            TicketStatus::WAITING_FOR_REQUESTER => [TicketStatus::OPEN, TicketStatus::ASSIGNED, TicketStatus::IN_PROGRESS, TicketStatus::WAITING_FOR_REQUESTER],
            TicketStatus::RESOLVED => [TicketStatus::OPEN, TicketStatus::ASSIGNED, TicketStatus::IN_PROGRESS, TicketStatus::RESOLVED],
            TicketStatus::CLOSED => [TicketStatus::OPEN, TicketStatus::ASSIGNED, TicketStatus::IN_PROGRESS, TicketStatus::RESOLVED, TicketStatus::CLOSED],
        ];
        $previous = null;

        foreach ($progression[$finalStatus] as $step => $nextStatus) {
            $event = $ticket->statusHistory()->firstOrCreate(
                ['from_status' => $previous, 'to_status' => $nextStatus],
                ['actor_id' => $step >= 2 && $technician !== null ? $technician->id : $helpdesk->id],
            );
            $eventTime = $createdAt->copy()->addMinutes($step * 12);
            $event->forceFill(['created_at' => $eventTime, 'updated_at' => $eventTime])->save();
            $previous = $nextStatus;
        }
    }

    private function seedComments(Ticket $ticket, User $requester, User $helpdesk, ?User $technician): void
    {
        $ticket->comments()->firstOrCreate(
            ['author_id' => $requester->id, 'visibility' => 'public', 'body' => 'Demo request: this issue is affecting my normal work.'],
        );

        if ($technician !== null) {
            $ticket->comments()->firstOrCreate(
                ['author_id' => $technician->id, 'visibility' => 'public', 'body' => 'Demo update: I am reviewing the request and will share the next steps here.'],
            );
            $ticket->comments()->firstOrCreate(
                ['author_id' => $technician->id, 'visibility' => 'internal', 'body' => 'Demo work note: initial checks recorded for interface preview.'],
            );
        } elseif ($ticket->status === TicketStatus::OPEN) {
            $ticket->comments()->firstOrCreate(
                ['author_id' => $helpdesk->id, 'visibility' => 'public', 'body' => 'Demo update: the helpdesk has received this request.'],
            );
        }
    }

    private function descriptionFor(string $subject): string
    {
        return "Demonstration ticket for the {$subject} scenario. This synthetic record is provided to preview the helpdesk queues, status workflow, and ticket detail screen. No real person or incident is represented.";
    }
}
