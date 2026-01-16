{{--My Children--}}
<li class="nav-item">
    <a href="{{ route('my_children') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['my_children']) ? 'active' : '' }}"><i class="icon-users4"></i> My Children</a>
</li>

{{--Children Loans--}}
<li class="nav-item">
    <a href="{{ route('library.loans.children') }}" class="nav-link {{ Route::is('library.loans.children') ? 'active' : '' }}">
        <i class="icon-library2"></i> Children Loans
    </a>
</li>
