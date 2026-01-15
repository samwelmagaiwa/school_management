<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta id="csrf-token" name="csrf-token" content="{{ csrf_token() }}">
    <meta name="author" content="samTECH">

    <title> @yield('page_title') | {{ config('app.name') }} </title>

    @include('partials.inc_top')
    <style>
        body { padding-top: 3.5rem; }
        .navbar.fixed-top { transition: all 0.3s ease-in-out; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .page-header { 
            position: sticky; 
            top: 3.5rem; 
            z-index: 900; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.05); 
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(5px);
            border-bottom: 1px solid #ddd;
        }
        @media (min-width: 768px) {
            .sidebar { 
                top: 3.5rem; 
                height: calc(100vh - 3.5rem); 
                position: sticky; 
                z-index: 950;
            }
        }
    </style>
</head>

<body class="{{ in_array(Route::currentRouteName(), ['payments.invoice', 'marks.tabulation', 'marks.show', 'ttr.manage', 'ttr.show']) ? 'sidebar-xs' : '' }}">

@include('partials.top_menu')
<div class="page-content">
    @include('partials.menu')
    <div class="content-wrapper">
        @include('partials.header')

        <div class="content">
            {{--Error Alert Area--}}
            @if($errors->any())
                <div class="alert alert-danger border-0 alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>

                        @foreach($errors->all() as $er)
                            <span><i class="icon-arrow-right5"></i> {{ $er }}</span> <br>
                        @endforeach

                </div>
            @endif
            <div id="ajax-alert" style="display: none"></div>

            @yield('content')
        </div>


    </div>
</div>

@include('partials.inc_bottom')
@yield('scripts')
</body>
</html>
