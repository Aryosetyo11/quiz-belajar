<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamAttempt;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentResultsController extends Controller
{
    public function index(Request $request): View
    {
        $student = $request->user();
        $attemptsQuery = ExamAttempt::query()
            ->where('student_id', $student->id)
            ->whereNotNull('submitted_at')
            ->whereHas('exam', fn ($query) => $query
                ->where('type', Exam::TYPE_SESSION)
                ->whereHas('subject', fn ($subjectQuery) => $subjectQuery
                    ->whereHas('students', fn ($studentQuery) => $studentQuery->where('users.id', $student->id))));

        $completedCount = (clone $attemptsQuery)->count();
        $gradedCount = (clone $attemptsQuery)->whereNotNull('score')->count();
        $pendingGradingCount = (clone $attemptsQuery)->whereNull('score')->count();
        $averageScore = (clone $attemptsQuery)->whereNotNull('score')->avg('score');

        $status = $request->query('status');
        $status = in_array($status, ['graded', 'pending'], true) ? $status : 'all';

        $attempts = (clone $attemptsQuery)
            ->with(['exam.subject'])
            ->when($status === 'graded', fn ($query) => $query->whereNotNull('score'))
            ->when($status === 'pending', fn ($query) => $query->whereNull('score'))
            ->orderByDesc('submitted_at')
            ->paginate(10)
            ->withQueryString();

        return view('portal.student-results', [
            'attempts' => $attempts,
            'averageScore' => $averageScore !== null ? round((float) $averageScore, 1) : null,
            'completedCount' => $completedCount,
            'gradedCount' => $gradedCount,
            'pendingGradingCount' => $pendingGradingCount,
            'status' => $status,
        ]);
    }
}
