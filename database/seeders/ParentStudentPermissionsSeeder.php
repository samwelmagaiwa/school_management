<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserType;
use App\Models\Permission;

class ParentStudentPermissionsSeeder extends Seeder
{
    public function run()
    {
        $all_perms = Permission::all();

        // Ensure Student role exists
        UserType::updateOrCreate(['title' => 'student'], ['name' => 'Student', 'level' => 3]);

        $parent = UserType::where('title', 'parent')->first();
        if ($parent) {
            $parent_perms = $all_perms->whereIn('name', ['payment.view', 'student.view', 'exam.view_stats']);
            $parent->permissions()->sync($parent_perms->pluck('id'));
        }

        $student = UserType::where('title', 'student')->first();
        if ($student) {
            $student_perms = $all_perms->whereIn('name', ['exam.view_stats', 'payment.view']);
            $student->permissions()->sync($student_perms->pluck('id'));
        }
    }
}
