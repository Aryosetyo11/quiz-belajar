<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Exam;
use App\Models\School;
use App\Models\Subject;
use App\Models\User;
use App\Services\FoundationReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class FoundationDashboardController extends Controller
{
    public function index(FoundationReportService $reports): View
    {
        $schools = $reports->schoolOverview(null, null);
        $now = now();
        $activeSessions = Exam::query()
            ->where('type', Exam::TYPE_SESSION)
            ->where('is_published', true)
            ->where(fn (Builder $query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn (Builder $query) => $query->whereNull('ends_at')->orWhere('ends_at', '>', $now))
            ->count();

        return view('portal.foundation-dashboard', [
            'schoolCount' => School::query()->count(),
            'studentCount' => User::query()->where('role', User::ROLE_STUDENT)->count(),
            'teacherCount' => User::query()->where('role', User::ROLE_TEACHER)->count(),
            'classroomCount' => Classroom::query()->count(),
            'subjectCount' => Subject::query()->count(),
            'activeSessionCount' => $activeSessions,
            'topSchools' => $schools->sortByDesc('students_count')->take(5)->values(),
        ]);
    }
}
