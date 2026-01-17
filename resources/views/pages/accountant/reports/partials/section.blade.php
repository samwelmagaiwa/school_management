@php use Illuminate\Support\Str; @endphp
<div class="card mb-3" id="{{ $tableId ?? Str::slug($title ?? 'section') }}-section">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">{{ $title ?? 'Section' }}</h6>
        <div class="header-elements">
            <div class="d-flex align-items-center">
                @isset($tableId)
                    <div class="btn-group btn-group-sm mr-2">
                        <button type="button" class="btn btn-light js-export-trigger" data-target="#{{ $tableId }}" data-action="copy">Copy</button>
                        <button type="button" class="btn btn-light js-export-trigger" data-target="#{{ $tableId }}" data-action="excel">Excel</button>
                        <button type="button" class="btn btn-light js-export-trigger" data-target="#{{ $tableId }}" data-action="pdf">PDF</button>
                        <button type="button" class="btn btn-primary js-export-trigger" data-target="#{{ $tableId }}" data-action="colvis">
                            <i class="icon-menu7 mr-1"></i> Visibility
                        </button>
                    </div>
                @endisset

                @isset($csv)
                    <a href="{{ $csv }}" class="btn btn-sm btn-light border mr-2">
                        <i class="icon-file-spreadsheet mr-1"></i> CSV
                    </a>
                @endisset

                {!! Qs::getPanelOptions() !!}
            </div>
        </div>
    </div>
    <div class="card-body">
        {{ $slot }}
    </div>
</div>
