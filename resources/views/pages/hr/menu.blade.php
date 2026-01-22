{{--Human Resource Menu--}}
<li class="nav-item nav-item-submenu {{ \Illuminate\Support\Str::startsWith(Route::currentRouteName(), 'hr.') && !Route::is('hr.reports.summary') ? 'nav-item-expanded nav-item-open' : '' }} ">
    <a href="#" class="nav-link"><i class="icon-man-woman"></i> <span> Human Resources</span></a>

    <ul class="nav nav-group-sub" data-submenu-title="Human Resources">
        @if(Auth::user()->hasPermission('staff.manage'))
        <li class="nav-item">
            <a href="{{ route('hr.staff.index') }}" class="nav-link {{ Route::is('hr.staff.*') ? 'active' : '' }}">Staff Directory</a>
        </li>
        @endif

        @if(Auth::user()->hasPermission('dept.manage'))
        <li class="nav-item">
            <a href="{{ route('hr.departments.index') }}" class="nav-link {{ Route::is('hr.departments.*') ? 'active' : '' }}">Departments & Designations</a>
        </li>
        @endif

        @if(Auth::user()->hasPermission('leave.manage'))
        <li class="nav-item">
            <a href="{{ route('hr.leaves.index') }}" class="nav-link {{ Route::is('hr.leaves.index') || Route::is('hr.leaves.update') ? 'active' : '' }}">Leave Requests</a>
        </li>
        @endif

        @if(Auth::user()->hasPermission('attendance.manage'))
        <li class="nav-item">
            <a href="{{ route('hr.attendance.index') }}" class="nav-link {{ Route::is('hr.attendance.index') ? 'active' : '' }}">Mark Attendance</a>
        </li>
        @endif

        @if(Auth::user()->hasPermission('payroll.manage'))
        <li class="nav-item">
            <a href="{{ route('hr.payroll.index') }}" class="nav-link {{ Route::is('hr.payroll.*') ? 'active' : '' }}">Payroll & Salary</a>
        </li>
        @endif
    </ul>
</li>
