<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\User;
use Illuminate\View\View;

class PortalDashboardController extends Controller
{
    public function teacher(): View
    {
        $subjects = Subject::query()
            ->where('teacher_id', auth()->id())
            ->withCount('students')
            ->orderByDesc('students_count')
            ->orderBy('name')
            ->get();

        return view('portal.dashboard', [
            'role' => User::ROLE_TEACHER,
            'teacherSubjects' => $subjects,
            'subjectCount' => $subjects->count(),
            'studentEnrollmentCount' => $subjects->sum('students_count'),
            'largestSubjectEnrollment' => max(1, (int) $subjects->max('students_count')),
        ]);
    }

    public function student(): View
    {
        $subjects = auth()->user()
            ->enrolledSubjects()
            ->with('teacher:id,name')
            ->withCount('materials')
            ->orderBy('subjects.name')
            ->get();

        return view('portal.dashboard', [
            'role' => User::ROLE_STUDENT,
            'studentSubjects' => $subjects,
            'studentSubjectCount' => $subjects->count(),
        ]);
    }
}
