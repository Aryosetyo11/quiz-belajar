<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\SubjectMaterial;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $teacherCount = User::query()->where('role', User::ROLE_TEACHER)->count();
        $studentCount = User::query()->where('role', User::ROLE_STUDENT)->count();
        $adminCount = User::query()->whereIn('role', [User::ROLE_ADMIN_REMOTE, User::ROLE_ADMIN_FOUNDATION])->count();
        $remoteAdminCount = User::query()->where('role', User::ROLE_ADMIN_REMOTE)->count();
        $foundationAdminCount = User::query()->where('role', User::ROLE_ADMIN_FOUNDATION)->count();
        $subjectCount = Subject::query()->count();
        $materialCount = SubjectMaterial::query()->count();
        $enrollmentCount = DB::table('subject_student')->count();
        $totalAccounts = $teacherCount + $studentCount + $adminCount;

        $chartData = collect([
            ['label' => 'Siswa', 'count' => $studentCount, 'color' => '#f97316'],
            ['label' => 'Guru', 'count' => $teacherCount, 'color' => '#0f766e'],
            ['label' => 'Admin All Remote', 'count' => $remoteAdminCount, 'color' => '#7c3aed'],
            ['label' => 'Admin Yayasan', 'count' => $foundationAdminCount, 'color' => '#2563eb'],
        ])->map(function (array $item) use ($totalAccounts): array {
            $item['percentage'] = $totalAccounts > 0 ? (int) round(($item['count'] / $totalAccounts) * 100) : 0;

            return $item;
        });

        $donutSegments = [];
        $start = 0;

        foreach ($chartData as $item) {
            if ($item['count'] === 0) {
                continue;
            }

            $end = $start + ($item['count'] / $totalAccounts * 100);
            $donutSegments[] = "{$item['color']} {$start}% {$end}%";
            $start = $end;
        }

        $isFoundationAdmin = auth()->user()->role === User::ROLE_ADMIN_FOUNDATION;

        return view('portal.admin-dashboard', [
            'teacherCount' => $teacherCount,
            'studentCount' => $studentCount,
            'adminCount' => $adminCount,
            'remoteAdminCount' => $remoteAdminCount,
            'foundationAdminCount' => $foundationAdminCount,
            'subjectCount' => $subjectCount,
            'materialCount' => $materialCount,
            'enrollmentCount' => $enrollmentCount,
            'totalAccounts' => $totalAccounts,
            'chartData' => $chartData,
            'donutBackground' => $donutSegments === []
                ? '#e2e8f0'
                : 'conic-gradient('.implode(', ', $donutSegments).')',
            'latestUsers' => $isFoundationAdmin
                ? collect()
                : User::query()->latest('created_at')->limit(6)->get(['id', 'name', 'email', 'nisn', 'role', 'created_at']),
            'isFoundationAdmin' => $isFoundationAdmin,
        ]);
    }
}
