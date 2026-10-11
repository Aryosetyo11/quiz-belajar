<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\SubjectMaterial;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubjectMaterialDownloadController extends Controller
{
    public function __invoke(Request $request, Subject $subject, SubjectMaterial $material): StreamedResponse
    {
        abort_unless((int) $material->subject_id === $subject->id, 404);

        $user = $request->user();
        $isTeacher = $user->role === User::ROLE_TEACHER
            && (int) $subject->teacher_id === $user->id;
        $isEnrolledStudent = $user->role === User::ROLE_STUDENT
            && $user->enrolledSubjects()->where('subjects.id', $subject->id)->exists();

        abort_unless($isTeacher || $isEnrolledStudent, 404);
        abort_unless(Storage::disk('local')->exists($material->file_path), 404);

        return Storage::disk('local')->download($material->file_path, $material->original_file_name);
    }
}
