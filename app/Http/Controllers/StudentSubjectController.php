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
            ->with('teacher:id,name')
            ->withCount('materials')
            ->orderBy('subjects.name')
            ->get();

        return view('portal.student-subjects', ['subjects' => $subjects]);
    }
}
