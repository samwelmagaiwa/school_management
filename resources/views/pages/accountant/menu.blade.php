@php use Illuminate\Support\Str; @endphp
@if(Qs::userIsTeamAccount())
    <li class="nav-item nav-item-submenu {{ Str::startsWith(Route::currentRouteName(), 'accounting.') ? 'nav-item-expanded nav-item-open' : '' }}">
        <a href="#" class="nav-link"><i class="icon-coins"></i> <span>Accounting Suite</span></a>
        <ul class="nav nav-group-sub" data-submenu-title="Accounting">
            <li class="nav-item">
                <a href="{{ route('accounting.fee-categories.index') }}"
                   class="nav-link {{ Route::is('accounting.fee-categories.*') ? 'active' : '' }}">Fee Categories</a>
            </li>
            @if(Qs::userIsTeamSA())
                <li class="nav-item">
                    <a href="{{ route('accounting.periods.index') }}"
                       class="nav-link {{ Route::is('accounting.periods.*') ? 'active' : '' }}">Academic Periods / Terms</a>
                </li>
            @endif
            <li class="nav-item">
                <a href="{{ route('accounting.fee-structures.index') }}"
                   class="nav-link {{ Route::is('accounting.fee-structures.*') ? 'active' : '' }}">Fee Structures</a>
            </li>
            <li class="nav-item">
                <a href="{{ route('accounting.invoices.index') }}"
                   class="nav-link {{ Route::is('accounting.invoices.*') ? 'active' : '' }}">Invoices & Billing</a>
            </li>
            <li class="nav-item">
                <a href="{{ route('accounting.payments.index') }}"
                   class="nav-link {{ Route::is('accounting.payments.*') ? 'active' : '' }}">Payments & Receipts</a>
            </li>
            <li class="nav-item">
                <a href="{{ route('accounting.expenses.index') }}"
                   class="nav-link {{ Route::is('accounting.expenses.*') ? 'active' : '' }}">Expenses & Vendors</a>
            </li>
            <li class="nav-item">
                <a href="{{ route('accounting.reports.index') }}"
                   class="nav-link {{ Route::is('accounting.reports.*') ? 'active' : '' }}">Financial Reports & Controls</a>
            </li>
        </ul>
    </li>
@endif
