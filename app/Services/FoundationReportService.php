<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\Exam;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FoundationReportService
{
    /**
     * @return object{exam_sessions_count: int, submitted_attempts_count: int, participating_students_count: int, average_score: float|null}
     */
    public function metrics(?string $from, ?string $to, ?int $schoolId = null): object
    {
        return $this->attemptsQuery($from, $to, $schoolId)
            ->selectRaw('COUNT(DISTINCT exams.id) as exam_sessions_count')
            ->selectRaw('COUNT(exam_attempts.id) as submitted_attempts_count')
            ->selectRaw('COUNT(DISTINCT exam_attempts.student_id) as participating_students_count')
            ->selectRaw('AVG(exam_attempts.score) as average_score')
            ->first();
    }

    /**
     * @return Collection<int, object>
     */
    public function schoolOverview(?string $from, ?string $to): Collection
    {
        $schoolMetrics = $this->attemptsQuery($from, $to)
            ->select('subjects.school_id')
            ->selectRaw('COUNT(DISTINCT exams.id) as exam_sessions_count')
            ->selectRaw('COUNT(exam_attempts.id) as submitted_attempts_count')
            ->selectRaw('COUNT(DISTINCT exam_attempts.student_id) as participating_students_count')
            ->selectRaw('AVG(exam_attempts.score) as average_score')
            ->groupBy('subjects.school_id')
            ->get()
            ->keyBy('school_id');

        return School::query()
            ->withCount([
                'users as students_count' => fn ($query) => $query->where('role', User::ROLE_STUDENT),
                'users as teachers_count' => fn ($query) => $query->where('role', User::ROLE_TEACHER),
                'classrooms',
                'subjects',
            ])
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'is_active'])
            ->each(function (School $school) use ($schoolMetrics): void {
                $school->setAttribute('exam_report', $schoolMetrics->get($school->id));
            });
    }

    /**
     * @return Collection<int, Exam>
     */
    public function schoolExams(School $school, ?string $from, ?string $to): Collection
    {
        $examIds = $this->attemptsQuery($from, $to, $school->id)
            ->select('exams.id')
            ->distinct()
            ->pluck('exams.id');

        if ($examIds->isEmpty()) {
            return collect();
        }

        return Exam::query()
            ->whereIn('id', $examIds)
            ->with([
                'subject:id,name,teacher_id,school_id,classroom_id',
                'subject.teacher:id,name',
                'subject.classroom:id,name,academic_year',
            ])
            ->withCount(['attempts as submitted_attempts_count' => fn (EloquentBuilder $query) => $this->applyAttemptDateRange($query, $from, $to)])
            ->withAvg(['attempts as average_score' => fn (EloquentBuilder $query) => $this->applyAttemptDateRange($query, $from, $to)], 'score')
            ->latest('starts_at')
            ->get();
    }

    /**
     * @return Collection<int, Classroom>
     */
    public function schoolClassrooms(School $school): Collection
    {
        return Classroom::query()
            ->where('school_id', $school->id)
            ->withCount(['students', 'subjects'])
            ->with(['subjects.teacher:id,name'])
            ->orderBy('academic_year', 'desc')
            ->orderBy('name')
            ->get();
    }

    private function attemptsQuery(?string $from, ?string $to, ?int $schoolId = null): Builder
    {
        $query = DB::table('exam_attempts')
            ->join('exams', 'exams.id', '=', 'exam_attempts.exam_id')
            ->join('subjects', 'subjects.id', '=', 'exams.subject_id')
            ->where('exams.type', Exam::TYPE_SESSION)
            ->whereNotNull('exam_attempts.submitted_at');

        if ($schoolId !== null) {
            $query->where('subjects.school_id', $schoolId);
        }

        return $this->applyDateRange($query, $from, $to);
    }

    private function applyDateRange(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from !== null) {
            $query->whereDate('exam_attempts.submitted_at', '>=', $from);
        }

        if ($to !== null) {
            $query->whereDate('exam_attempts.submitted_at', '<=', $to);
        }

        return $query;
    }

    private function applyAttemptDateRange(EloquentBuilder $query, ?string $from, ?string $to): EloquentBuilder
    {
        $query->whereNotNull('submitted_at');

        if ($from !== null) {
            $query->whereDate('submitted_at', '>=', $from);
        }

        if ($to !== null) {
            $query->whereDate('submitted_at', '<=', $to);
        }

        return $query;
    }
}
