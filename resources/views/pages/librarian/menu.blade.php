{{--Library--}}
<li class="nav-item nav-item-submenu {{ \Illuminate\Support\Str::startsWith(Route::currentRouteName(), 'library.') ? 'nav-item-expanded nav-item-open' : '' }}">
    <a href="#" class="nav-link"><i class="icon-books"></i> <span>Library</span></a>
    <ul class="nav nav-group-sub" data-submenu-title="Library">
        <li class="nav-item"><a href="{{ route('library.books.index') }}" class="nav-link {{ Route::is('library.books.index') ? 'active' : '' }}">Books Catalog</a></li>
        <li class="nav-item"><a href="{{ route('library.categories.index') }}" class="nav-link {{ Route::is('library.categories.index') ? 'active' : '' }}">Manage Categories</a></li>
        <li class="nav-item"><a href="{{ route('library.loans.index') }}" class="nav-link {{ Route::is('library.loans.index') ? 'active' : '' }}">Active Loans</a></li>
        <li class="nav-item"><a href="{{ route('library.loans.overdue') }}" class="nav-link {{ Route::is('library.loans.overdue') ? 'active' : '' }}">Overdue Loans</a></li>
        <li class="nav-item"><a href="{{ route('library.requests.index') }}" class="nav-link {{ Route::is('library.requests.index') ? 'active' : '' }}">Borrow Requests</a></li>
    </ul>
</li>
