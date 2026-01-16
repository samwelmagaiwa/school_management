{{--Marksheet--}}
<li class="nav-item">
    <a href="{{ route('marks.year_select', Qs::hash(Auth::user()->id)) }}" class="nav-link {{ in_array(Route::currentRouteName(), ['marks.show', 'marks.year_selector', 'pins.enter']) ? 'active' : '' }}"><i class="icon-book"></i> Marksheet</a>
</li>

{{--My Loans--}}
<li class="nav-item">
    <a href="{{ route('library.loans.my') }}" class="nav-link {{ Route::is('library.loans.my') ? 'active' : '' }}"><i class="icon-library2"></i> My Loans</a>
</li>

{{--My Borrow Requests--}}
<li class="nav-item">
    <a href="{{ route('library.requests.my') }}" class="nav-link {{ Route::is('library.requests.my') ? 'active' : '' }}"><i class="icon-file-text"></i> My Borrow Requests</a>
</li>