<?php

namespace App\Events;

use App\Models\Employee;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmployeeUpdatedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $employeeData;
    public int $companyId;

    /**
     * Create a new event instance.
     */
    public function __construct(Employee $employee)
    {
        $employee->load(['department', 'position', 'user.roles']);

        $this->companyId = $employee->company_id ?: 1;
        $this->employeeData = [
            'id' => $employee->id,
            'user_id' => $employee->user_id,
            'first_name' => $employee->first_name,
            'last_name' => $employee->last_name,
            'full_name' => $employee->full_name,
            'email' => $employee->email,
            'phone' => $employee->phone ?: $employee->mobile,
            'mobile' => $employee->mobile,
            'profile_image' => $employee->profile_image,
            'avatar_url' => $employee->avatar_url,
            'status' => $employee->status,
            'is_active' => $employee->is_active,
            'employment_status' => $employee->employment_status,
            'role_name' => $employee->role_name,
            'department' => $employee->department,
            'position' => $employee->position,
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('company-employees.' . $this->companyId),
            new Channel('public-employees-updates'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'EmployeeUpdatedEvent';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'employee' => $this->employeeData,
        ];
    }
}
