<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentSubjectMaterialController extends Controller
{
    public function show(Request $request, Subject $subject): View
    {
        $subject = $request->user()->enrolledSubjects()
            ->where('subjects.id', $subject->id)
            ->with('teacher:id,name')
            ->firstOrFail();

        return view('portal.student-materials', [
            'subject' => $subject,
            'materials' => $subject->materials()->latest()->get(),
        ]);
    }
}
