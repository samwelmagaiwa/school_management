<?php
namespace Database\Seeders;

use App\Models\Permission;
use App\Models\UserType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionsTableSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            ['name' => 'user.view', 'title' => 'View Users', 'description' => 'Can view list of users'],
            ['name' => 'user.create', 'title' => 'Create Users', 'description' => 'Can create new users'],
            ['name' => 'user.edit', 'title' => 'Edit Users', 'description' => 'Can edit user details'],
            ['name' => 'user.delete', 'title' => 'Delete Users', 'description' => 'Can delete users'],
            ['name' => 'user.reset_password', 'title' => 'Reset Password', 'description' => 'Can reset user passwords'],
            
            ['name' => 'role.view', 'title' => 'View Roles', 'description' => 'Can view roles'],
            ['name' => 'role.create', 'title' => 'Create Roles', 'description' => 'Can create new roles'],
            ['name' => 'role.edit', 'title' => 'Edit Roles', 'description' => 'Can edit roles'],
            ['name' => 'role.delete', 'title' => 'Delete Roles', 'description' => 'Can delete roles'],
            ['name' => 'role.assign', 'title' => 'Assign Permissions', 'description' => 'Can assign permissions to roles'],

            ['name' => 'dept.manage', 'title' => 'Manage Departments', 'description' => 'Can manage departments'],
            ['name' => 'dorm.manage', 'title' => 'Manage Dorms', 'description' => 'Can manage dormitories'],
            ['name' => 'class.manage', 'title' => 'Manage Classes', 'description' => 'Can manage classes'],
            ['name' => 'section.manage', 'title' => 'Manage Sections', 'description' => 'Can manage sections'],
            ['name' => 'subject.manage', 'title' => 'Manage Subjects', 'description' => 'Can manage subjects'],

            ['name' => 'student.view', 'title' => 'View Students', 'description' => 'Can view student list and profiles'],
            ['name' => 'student.admit', 'title' => 'Admit Students', 'description' => 'Can admit new students'],
            ['name' => 'student.promote', 'title' => 'Promote Students', 'description' => 'Can promote or manage promotions'],
            ['name' => 'student.graduate', 'title' => 'Graduate Students', 'description' => 'Can view/manage graduated students'],

            ['name' => 'academic.manage', 'title' => 'Manage Academics', 'description' => 'Can manage timetables and attendance'],

            ['name' => 'exam.manage', 'title' => 'Manage Exams', 'description' => 'Can manage exam settings'],
            ['name' => 'exam.view_stats', 'title' => 'View Exam Stats', 'description' => 'Can view exam statistics'],
            ['name' => 'marks.manage', 'title' => 'Manage Marks', 'description' => 'Can enter and manage marks'],
            ['name' => 'marks.bulk_report', 'title' => 'Bulk Report Cards', 'description' => 'Can generate bulk report cards'],

            ['name' => 'payment.view', 'title' => 'View Payments', 'description' => 'Can view payment records'],
            ['name' => 'payment.record', 'title' => 'Record Payment', 'description' => 'Can record new payments'],
            ['name' => 'settings.manage', 'title' => 'Manage Settings', 'description' => 'Can manage system settings'],
        ];

        foreach ($permissions as $p) {
            Permission::updateOrCreate(['name' => $p['name']], array_merge($p, ['slug' => Str::slug($p['name'])]));
        }

        // Assign Permissions to Roles
        $super_admin = UserType::where('title', 'super_admin')->first();
        $admin = UserType::where('title', 'admin')->first();
        $teacher = UserType::where('title', 'teacher')->first();
        $accountant = UserType::where('title', 'accountant')->first();

        $all_perms = Permission::all();

        if ($super_admin) {
            $super_admin->permissions()->sync($all_perms->pluck('id'));
        }

        if ($admin) {
            $admin_perms = $all_perms->filter(fn($p) => !in_array($p->name, ['user.delete', 'role.delete', 'settings.manage']));
            $admin->permissions()->sync($admin_perms->pluck('id'));
        }

        if ($teacher) {
            $teacher_perms = $all_perms->whereIn('name', ['exam.view_stats', 'marks.manage', 'marks.bulk_report']);
            $teacher->permissions()->sync($teacher_perms->pluck('id'));
        }

        if ($accountant) {
            $accountant_perms = $all_perms->whereIn('name', ['payment.view', 'payment.record']);
            $accountant->permissions()->sync($accountant_perms->pluck('id'));
        }
    }
}
