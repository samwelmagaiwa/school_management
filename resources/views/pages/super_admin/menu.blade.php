@php
    use Illuminate\Support\Str;
@endphp

{{--Manage Settings--}}
<li class="nav-item">
    <a href="{{ route('settings') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['settings',]) ? 'active' : '' }}"><i class="icon-gear"></i> <span>Settings</span></a>
</li>

{{--Library--}}
<li class="nav-item nav-item-submenu {{ Str::startsWith(Route::currentRouteName(), 'library.') ? 'nav-item-expanded nav-item-open' : '' }}">
    <a href="#" class="nav-link"><i class="icon-books"></i> <span>Library</span></a>
    <ul class="nav nav-group-sub" data-submenu-title="Library">
        <li class="nav-item"><a href="{{ route('library.books.index') }}" class="nav-link {{ Route::is('library.books.index') ? 'active' : '' }}">Books Catalog</a></li>
        <li class="nav-item"><a href="{{ route('library.categories.index') }}" class="nav-link {{ Route::is('library.categories.index') ? 'active' : '' }}">Manage Categories</a></li>
        <li class="nav-item"><a href="{{ route('library.loans.index') }}" class="nav-link {{ Route::is('library.loans.index') ? 'active' : '' }}">Active Loans</a></li>
        <li class="nav-item"><a href="{{ route('library.loans.overdue') }}" class="nav-link {{ Route::is('library.loans.overdue') ? 'active' : '' }}">Overdue Loans</a></li>
        <li class="nav-item"><a href="{{ route('library.requests.index') }}" class="nav-link {{ Route::is('library.requests.index') ? 'active' : '' }}">Borrow Requests</a></li>
    </ul>
</li>

{{--Activity Logs--}}
<li class="nav-item">
    <a href="{{ route('activity.logs.index') }}" class="nav-link {{ Route::is('activity.logs.index') ? 'active' : '' }}">
        <i class="icon-history"></i> <span>Activity Logs</span>
    </a>
</li>

{{--Pins--}}
<li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['pins.create', 'pins.index']) ? 'nav-item-expanded nav-item-open' : '' }} ">
    <a href="#" class="nav-link"><i class="icon-lock2"></i> <span> Pins</span></a>

    <ul class="nav nav-group-sub" data-submenu-title="Manage Pins">
        {{--Generate Pins--}}
            <li class="nav-item">
                <a href="{{ route('pins.create') }}"
                   class="nav-link {{ (Route::is('pins.create')) ? 'active' : '' }}">Generate Pins</a>
            </li>

        {{--    Valid/Invalid Pins  --}}
        <li class="nav-item">
            <a href="{{ route('pins.index') }}"
               class="nav-link {{ (Route::is('pins.index')) ? 'active' : '' }}">View Pins</a>
        </li>
    </ul>
</li>