@extends('layouts.master')
@section('page_title', 'Expenses & Vendors')
@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Expenses & Vendors</h6>
        {!! Qs::getPanelOptions() !!}
    </div>
    <div class="card-body">
        <table class="table datatable-button-html5-columns">
            <thead>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Vendor</th>
                <th>Category</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            @foreach($expenses as $expense)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $expense->title }}</td>
                    <td>{{ optional($expense->vendor)->name }}</td>
                    <td>{{ $expense->category }}</td>
                    <td>{{ Qs::formatCurrency($expense->amount) }}</td>
                    <td>{{ optional($expense->expense_date)->format('Y-m-d') }}</td>
                    <td>{{ ucfirst($expense->status) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
