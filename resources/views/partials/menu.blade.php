@php
    use Illuminate\Support\Str;
@endphp

<div class="sidebar sidebar-dark sidebar-main sidebar-expand-md">

    <!-- Sidebar mobile toggler -->
    <div class="sidebar-mobile-toggler text-center">
        <a href="#" class="sidebar-mobile-main-toggle">
            <i class="icon-arrow-left8"></i>
        </a>
        Navigation
        <a href="#" class="sidebar-mobile-expand">
            <i class="icon-screen-full"></i>
            <i class="icon-screen-normal"></i>
        </a>
    </div>
    <!-- /sidebar mobile toggler -->

    <!-- Sidebar content -->
    <div class="sidebar-content sidebar-content-flex">

        <!-- User menu -->
        <div class="sidebar-section sidebar-section-header">
            <div class="sidebar-user">
                <div class="card-body">
                    <div class="media">
                        <div class="mr-3">
                            <a href="{{ route('my_account') }}"><img src="{{ Auth::user()->photo }}" width="38" height="38" class="rounded-circle" alt="photo"></a>
                        </div>

                        <div class="media-body">
                            <div class="media-title font-weight-semibold">{{ Auth::user()->name }}</div>
                            <div class="font-size-xs opacity-50">
                                <i class="icon-user font-size-sm"></i> &nbsp;{{ ucwords(str_replace('_', ' ', Auth::user()->user_type)) }}
                            </div>
                        </div>

                        <div class="ml-3 align-self-center">
                            <a href="{{ route('my_account') }}" class="text-white"><i class="icon-cog3"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /user menu -->

        <!-- Main navigation -->
        <div class="sidebar-section sidebar-section-scroll">
            <div class="card card-sidebar-mobile sidebar-menu-card">
                <div class="card-body sidebar-nav-scroll">
                    <ul class="nav nav-sidebar" data-nav-type="accordion">

                <!-- Main -->
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ (Route::is('dashboard')) ? 'active' : '' }}">
                        <i class="icon-home4"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                {{-- Administrative & Setup --}}
                @if(Qs::userIsTeamSA() || Auth::user()->hasPermission('user.view') || Auth::user()->hasPermission('dept.manage') || Auth::user()->hasPermission('dorm.manage') || Auth::user()->hasPermission('class.manage') || Auth::user()->hasPermission('section.manage') || Auth::user()->hasPermission('subject.manage'))
                    <li class="nav-item-header"><div class="text-uppercase font-size-xs line-height-normal">Administrative & Setup</div> <i class="icon-menu" title="Administrative & Setup"></i></li>
                    
                    {{--Manage Users--}}
                    @if(Qs::userIsTeamSA() || Auth::user()->hasPermission('user.view'))
                    <li class="nav-item">
                        <a href="{{ route('users.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['users.index', 'users.show', 'users.edit']) ? 'active' : '' }}"><i class="icon-users4"></i> <span> Users</span></a>
                    </li>
                    @endif

                    {{--Departments--}}
                    @if(Qs::userIsTeamSA() || Auth::user()->hasPermission('dept.manage'))
                    <li class="nav-item">
                        <a href="{{ route('departments.index') }}" class="nav-link {{ Route::is('departments.index') ? 'active' : '' }}"><i class="icon-tree7"></i> <span>Departments</span></a>
                    </li>
                    @endif

                    {{--Manage Dorms--}}
                    @if(Qs::userIsTeamSA() || Auth::user()->hasPermission('dorm.manage'))
                    <li class="nav-item">
                        <a href="{{ route('dorms.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['dorms.index','dorms.edit']) ? 'active' : '' }}"><i class="icon-home9"></i> <span> Dormitories</span></a>
                    </li>
                    @endif

                    {{--Manage Classes--}}
                    @if(Qs::userIsTeamSA() || Auth::user()->hasPermission('class.manage'))
                    <li class="nav-item">
                        <a href="{{ route('classes.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['classes.index','classes.edit']) ? 'active' : '' }}"><i class="icon-windows2"></i> <span> Classes</span></a>
                    </li>
                    @endif

                    {{--Manage Sections--}}
                    @if(Qs::userIsTeamSA() || Auth::user()->hasPermission('section.manage'))
                    <li class="nav-item">
                        <a href="{{ route('sections.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['sections.index','sections.edit',]) ? 'active' : '' }}"><i class="icon-fence"></i> <span>Sections</span></a>
                    </li>
                    @endif

                    {{--Manage Subjects--}}
                    @if(Qs::userIsTeamSA() || Auth::user()->hasPermission('subject.manage'))
                    <li class="nav-item">
                        <a href="{{ route('subjects.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['subjects.index','subjects.edit',]) ? 'active' : '' }}"><i class="icon-pin"></i> <span>Subjects</span></a>
                    </li>
                    @endif
                @endif

                {{--Manage Students--}}
                @if(Qs::userIsTeamSAT() || Auth::user()->hasPermission('student.view'))
                    <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['students.create', 'students.list', 'students.edit', 'students.show', 'students.promotion', 'students.promotion_manage', 'students.graduated']) ? 'nav-item-expanded nav-item-open' : '' }} ">
                        <a href="#" class="nav-link"><i class="icon-users"></i> <span> Students</span></a>

                        <ul class="nav nav-group-sub" data-submenu-title="Manage Students">
                            {{--Admit Student--}}
                            @if(Qs::userIsTeamSA() || Auth::user()->hasPermission('student.admit'))
                                <li class="nav-item">
                                    <a href="{{ route('students.create') }}"
                                       class="nav-link {{ (Route::is('students.create')) ? 'active' : '' }}">Admit Student</a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('students.import') }}"
                                       class="nav-link {{ (Route::is('students.import')) ? 'active' : '' }}">Import Students</a>
                                </li>
                            @endif

                            {{--Student Information--}}
                            @if(Qs::userIsTeamSAT() || Auth::user()->hasPermission('student.view'))
                            <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['students.list', 'students.edit', 'students.show']) ? 'nav-item-expanded' : '' }}">
                                <a href="#" class="nav-link {{ in_array(Route::currentRouteName(), ['students.list', 'students.edit', 'students.show']) ? 'active' : '' }}">Student Information</a>
                                <ul class="nav nav-group-sub">
                                    @foreach(App\Models\MyClass::orderBy('name')->get() as $c)
                                        <li class="nav-item"><a href="{{ route('students.list', $c->id) }}" class="nav-link ">{{ $c->name }}</a></li>
                                    @endforeach
                                </ul>
                            </li>
                            @endif

                            {{--Student Promotion--}}
                            @if(Qs::userIsTeamSA() || Auth::user()->hasPermission('student.promote'))
                            <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['students.promotion', 'students.promotion_manage']) ? 'nav-item-expanded' : '' }}">
                                <a href="#" class="nav-link {{ in_array(Route::currentRouteName(), ['students.promotion', 'students.promotion_manage' ]) ? 'active' : '' }}">Student Promotion</a>
                            <ul class="nav nav-group-sub">
                                <li class="nav-item"><a href="{{ route('students.promotion') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['students.promotion']) ? 'active' : '' }}">Promote Students</a></li>
                                <li class="nav-item"><a href="{{ route('students.promotion_manage') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['students.promotion_manage']) ? 'active' : '' }}">Manage Promotions</a></li>
                            </ul>
                            </li>
                            @endif

                            {{--Student Graduated--}}
                            @if(Qs::userIsTeamSA() || Auth::user()->hasPermission('student.graduate'))
                            <li class="nav-item"><a href="{{ route('students.graduated') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['students.graduated' ]) ? 'active' : '' }}">Students Graduated</a></li>
                            @endif

                        </ul>
                    </li>
                @endif

                {{--Academics--}}
                @if(Qs::userIsAcademic() || Auth::user()->hasPermission('academic.manage'))
                    <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['tt.index', 'ttr.edit', 'ttr.show', 'ttr.manage']) ? 'nav-item-expanded nav-item-open' : '' }} ">
                        <a href="#" class="nav-link"><i class="icon-graduation2"></i> <span> Academics</span></a>

                        <ul class="nav nav-group-sub" data-submenu-title="Manage Academics">

                        {{--Timetables--}}
                            <li class="nav-item"><a href="{{ route('tt.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['tt.index']) ? 'active' : '' }}">Timetables</a></li>

                        {{--Attendance Sessions--}}
                            <li class="nav-item">
                                <a href="{{ route('attendance.sessions.index') }}"
                                   class="nav-link {{ Str::startsWith(Route::currentRouteName(), 'attendance.sessions.') ? 'active' : '' }}">
                                    Attendance
                                </a>
                            </li>

                        {{--Attendance Reports--}}
                            <li class="nav-item">
                                <a href="{{ route('attendance.reports.index') }}"
                                   class="nav-link {{ Str::startsWith(Route::currentRouteName(), 'attendance.reports.') ? 'active' : '' }}">
                                    Attendance Reports
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                {{--Exam--}}
                @if(Qs::userIsTeamSAT() || Auth::user()->hasPermission('exam.manage') || Auth::user()->hasPermission('marks.manage'))
                <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['exams.index', 'exams.edit', 'grades.index', 'grades.edit', 'marks.index', 'marks.manage', 'marks.bulk', 'marks.tabulation', 'marks.show', 'marks.batch_fix',]) ? 'nav-item-expanded nav-item-open' : '' }} ">
                    <a href="#" class="nav-link"><i class="icon-books"></i> <span> Exams</span></a>

                    <ul class="nav nav-group-sub" data-submenu-title="Manage Exams">
                        @if(Qs::userIsTeamSA() || Auth::user()->hasPermission('exam.manage'))

                        {{--Exam list--}}
                            <li class="nav-item">
                                <a href="{{ route('exams.index') }}"
                                   class="nav-link {{ (Route::is('exams.index')) ? 'active' : '' }}">Exam List</a>
                            </li>

                            {{--Grades list--}}
                            <li class="nav-item">
                                    <a href="{{ route('grades.index') }}"
                                       class="nav-link {{ in_array(Route::currentRouteName(), ['grades.index', 'grades.edit']) ? 'active' : '' }}">Grades</a>
                            </li>

                            {{--Tabulation Sheet--}}
                            <li class="nav-item">
                                <a href="{{ route('marks.tabulation') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['marks.tabulation']) ? 'active' : '' }}">Tabulation Sheet</a>
                            </li>

                            {{--Marks Batch Fix--}}
                            <li class="nav-item">
                                <a href="{{ route('marks.batch_fix') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['marks.batch_fix']) ? 'active' : '' }}">Batch Fix</a>
                            </li>
                        @endif

                        @if(Qs::userIsTeamSAT() || Auth::user()->hasPermission('marks.manage'))
                            {{--Marks Manage--}}
                            <li class="nav-item">
                                <a href="{{ route('marks.index') }}"
                                   class="nav-link {{ in_array(Route::currentRouteName(), ['marks.index']) ? 'active' : '' }}">Marks</a>
                            </li>

                            {{--Marksheet--}}
                            <li class="nav-item">
                                <a href="{{ route('marks.bulk') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['marks.bulk', 'marks.show']) ? 'active' : '' }}">Marksheet</a>
                            </li>

                            @endif

                    </ul>
                </li>
                @endif

                {{--Payments--}}
                @if(Qs::userIsAdministrative() || Auth::user()->hasPermission('payment.view'))
                    <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['payments.index', 'payments.create', 'payments.invoice', 'payments.receipts', 'payments.edit', 'payments.manage', 'payments.show',]) ? 'nav-item-expanded nav-item-open' : '' }} ">
                        <a href="#" class="nav-link"><i class="icon-office"></i> <span> Payments</span></a>

                        <ul class="nav nav-group-sub" data-submenu-title="Payments">

                            {{--Payments--}}
                            @if(Qs::userIsTeamAccount() || Auth::user()->hasPermission('payment.view'))
                            <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['payments.index', 'payments.create', 'payments.edit', 'payments.manage', 'payments.show', 'payments.invoice']) ? 'nav-item-expanded' : '' }}">

                                <a href="#" class="nav-link {{ in_array(Route::currentRouteName(), ['payments.index', 'payments.edit', 'payments.create', 'payments.manage', 'payments.show', 'payments.invoice']) ? 'active' : '' }}">Administrative</a>

                                <ul class="nav nav-group-sub">
                                    @if(Qs::userIsTeamAccount() || Auth::user()->hasPermission('payment.record'))
                                    <li class="nav-item"><a href="{{ route('payments.create') }}" class="nav-link {{ Route::is('payments.create') ? 'active' : '' }}">Create Payment</a></li>
                                    @endif
                                    <li class="nav-item"><a href="{{ route('payments.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['payments.index', 'payments.edit', 'payments.show']) ? 'active' : '' }}">Manage Payments</a></li>
                                    <li class="nav-item"><a href="{{ route('payments.manage') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['payments.manage', 'payments.invoice', 'payments.receipts']) ? 'active' : '' }}">Student Payments</a></li>

                                </ul>

                            </li>
                            @endif
                        </ul>
                    </li>
                @endif


                {{--End Exam--}}

                {{--Inventory & Warehouse--}}
                @if(Qs::userIsTeamInventory())
                    <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['inventory.index', 'inventory.categories', 'inventory.warehouses.index', 'inventory.warehouses.show', 'inventory.requisitions.index', 'inventory.stocks.create']) ? 'nav-item-expanded nav-item-open' : '' }} ">
                        <a href="#" class="nav-link"><i class="icon-stack"></i> <span> Inventory & Store</span></a>

                        <ul class="nav nav-group-sub" data-submenu-title="Inventory">
                            <li class="nav-item">
                                <a href="{{ route('inventory.categories') }}" class="nav-link {{ Route::is('inventory.categories') ? 'active' : '' }}">Categories</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('inventory.warehouses.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['inventory.warehouses.index', 'inventory.warehouses.show']) ? 'active' : '' }}">Warehouses</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('inventory.index') }}" class="nav-link {{ Route::is('inventory.index') ? 'active' : '' }}">Items Manager</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('inventory.stocks.create') }}" class="nav-link {{ Route::is('inventory.stocks.create') ? 'active' : '' }}">Receive Stock</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('inventory.requisitions.index') }}" class="nav-link {{ Route::is('inventory.requisitions.index') ? 'active' : '' }}">Requisitions</a>
                            </li>

                        </ul>
                    </li>
                @endif

                {{--Transport--}}
                @if(Qs::userIsTeamTransport())
                    <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['transport.index']) ? 'nav-item-expanded nav-item-open' : '' }} ">
                        <a href="#" class="nav-link"><i class="icon-truck"></i> <span> Transport</span></a>

                        <ul class="nav nav-group-sub" data-submenu-title="Transport">
                            <li class="nav-item">
                                <a href="{{ route('transport.index') }}" class="nav-link {{ Route::is('transport.index') ? 'active' : '' }}">Vehicles & Trips</a>
                            </li>
                        </ul>
                    </li>
                @endif

                {{-- Role Specific Menu --}}
                @php $role_menu = 'pages.'.Qs::getUserType().'.menu'; @endphp
                @if(view()->exists($role_menu))
                    @include($role_menu)
                @endif

                {{--Manage Account--}}
                <li class="nav-item">
                    <a href="{{ route('my_account') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['my_account']) ? 'active' : '' }}"><i class="icon-user"></i> <span>My Account</span></a>
                </li>

                    </ul>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="sidebar-section sidebar-section-footer text-center">
            <small class="sidebar-footer-text">&copy; {{ now()->year }} {{ config('app.name') }}</small>
        </div>
    </div>
</div>
