<?php

namespace App\Repositories\Eloquent;

use App\Models\Schedule;
use Illuminate\Support\Collection;
use App\Exceptions\Repository\RecordNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Contracts\Repository\ScheduleRepositoryInterface;

class ScheduleRepository extends EloquentRepository implements ScheduleRepositoryInterface
{
    /**
     * Return the model backing this repository.
     */
    public function model(): string
    {
        return Schedule::class;
    }

    /**
     * Return all the schedules for a given server.
     */
    public function findServerSchedules(int $server): Collection
    {
        return $this->getBuilder()->withCount('tasks')->where('server_id', '=', $server)->get($this->getColumns());
    }

    /**
     * Return a schedule model with all the associated tasks as a relationship.
     *
     * @throws RecordNotFoundException
     */
    public function getScheduleWithTasks(int $schedule): Schedule
    {
        try {
            return $this->getBuilder()->with('tasks')->findOrFail($schedule, $this->getColumns());
        } catch (ModelNotFoundException) {
            throw new RecordNotFoundException();
        }
    }
}
