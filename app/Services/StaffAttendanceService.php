<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StaffAttendanceService
{
    private ?bool $hasAttendanceTable = null;

    public function isWorking(int $branchId, string $date, string $staffId): bool
    {
        if ($this->usesDatabaseTable()) {
            $attendance = DB::table('staff_attendance')
                ->where('masseuse_id', (int) $staffId)
                ->whereDate('attendance_date', $date)
                ->value('is_working');

            if ($attendance === null) {
                return true;
            }

            return (bool) $attendance;
        }

        $attendanceMap = $this->getAttendanceMap($branchId, $date);

        if (!array_key_exists($staffId, $attendanceMap)) {
            return true;
        }

        return (bool) $attendanceMap[$staffId];
    }

    public function setAttendance(int $branchId, string $date, string $staffId, bool $isWorking): void
    {
        if ($this->usesDatabaseTable()) {
            $now = now();
            $query = DB::table('staff_attendance')
                ->where('masseuse_id', (int) $staffId)
                ->whereDate('attendance_date', $date);

            if ($query->exists()) {
                $query->update([
                    'is_working' => $isWorking ? 1 : 0,
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('staff_attendance')->insert([
                    'masseuse_id' => (int) $staffId,
                    'attendance_date' => $date,
                    'is_working' => $isWorking ? 1 : 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            return;
        }

        $attendanceMap = $this->getAttendanceMap($branchId, $date);
        $attendanceMap[$staffId] = $isWorking;

        Cache::forever($this->getCacheKey($branchId, $date), $attendanceMap);
    }

    public function getAttendanceMap(int $branchId, string $date): array
    {
        if ($this->usesDatabaseTable()) {
            return DB::table('staff_attendance as sa')
                ->join('masseuses as m', 'm.id', '=', 'sa.masseuse_id')
                ->where('m.branch_id', $branchId)
                ->whereDate('sa.attendance_date', $date)
                ->pluck('sa.is_working', 'sa.masseuse_id')
                ->map(static function ($value): bool {
                    return (bool) $value;
                })
                ->all();
        }

        $attendanceMap = Cache::get($this->getCacheKey($branchId, $date), []);

        if (!is_array($attendanceMap)) {
            return [];
        }

        $normalized = [];
        foreach ($attendanceMap as $staffId => $isWorking) {
            $normalized[(string) $staffId] = (bool) $isWorking;
        }

        return $normalized;
    }

    private function usesDatabaseTable(): bool
    {
        if ($this->hasAttendanceTable !== null) {
            return $this->hasAttendanceTable;
        }

        $this->hasAttendanceTable = Schema::hasTable('staff_attendance');

        return $this->hasAttendanceTable;
    }

    private function getCacheKey(int $branchId, string $date): string
    {
        return 'staff-attendance:' . $branchId . ':' . $date;
    }
}
