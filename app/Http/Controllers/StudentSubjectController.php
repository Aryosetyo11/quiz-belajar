<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentSubjectController extends Controller
{
    public function index(Request $request): View
    {
        $subjects = $request->user()
            ->enrolledSubjects()
            ->with(['teacher:id,name', 'classroom:id,name,academic_year', 'schedules'])
            ->withCount([
                'materials',
                'materials as documents_count' => fn ($query) => $query->where('type', 'file'),
                'materials as videos_count' => fn ($query) => $query->where('type', 'video'),
                'materials as links_count' => fn ($query) => $query->where('type', 'link'),
            ])
            ->orderBy('subjects.name')
            ->get();

        return view('portal.student-subjects', ['subjects' => $subjects]);
    }
}
