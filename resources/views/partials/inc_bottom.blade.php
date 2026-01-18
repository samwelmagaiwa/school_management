<!-- Theme JS files -->
<script src="{{ asset('global_assets/js/plugins/extensions/jquery_ui/interactions.min.js') }} "></script>
<script src="{{ asset('global_assets/js/plugins/forms/selects/select2.min.js') }} "></script>

{{--Forms--}}
<script src="{{ asset('global_assets/js/plugins/forms/wizards/steps.min.js') }}"></script>
<script src="{{ asset('global_assets/js/plugins/forms/styling/uniform.min.js') }}"></script>
<script src="{{ asset('global_assets/js/plugins/forms/inputs/inputmask.js') }}"></script>
<script src="{{ asset('global_assets/js/plugins/forms/validation/validate.min.js') }}"></script>
<script src="{{ asset('global_assets/js/plugins/extensions/cookie.js') }}"></script>

{{--Notify--}}
<script type="text/javascript" src="{{ asset('global_assets/js/plugins/notifications/sweet_alert2.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('global_assets/js/plugins/notifications/pnotify.min.js') }}"></script>

{{--DataTables--}}
<script src="{{ asset('global_assets/js/plugins/tables/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('global_assets/js/plugins/tables/datatables/extensions/jszip/jszip.min.js') }}"></script>
<script src="{{ asset('global_assets/js/plugins/tables/datatables/extensions/pdfmake/pdfmake.min.js') }}"></script>
<script src="{{ asset('global_assets/js/plugins/tables/datatables/extensions/pdfmake/vfs_fonts.min.js') }}"></script>
<script src="{{ asset('global_assets/js/plugins/tables/datatables/extensions/buttons.min.js') }}"></script>

{{--Date Pickers--}}
<script src="{{ asset('global_assets/js/plugins/ui/moment/moment.min.js') }}"></script>
<script src="{{ asset('global_assets/js/plugins/pickers/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('global_assets/js/plugins/pickers/pickadate/legacy.js') }}"></script>

{{--Uploaders--}}
<script src="{{ asset('global_assets/js/plugins/uploaders/fileinput/fileinput.min.js') }}"></script>

{{--Calendar--}}
<script src="{{ asset('global_assets/js/plugins/ui/fullcalendar/fullcalendar.min.js') }}"></script>


<script src=" {{ asset('assets/js/app.js') }} "></script>
<script src="{{ asset('global_assets/js/demo_pages/form_wizard.js') }}"></script>
<script src="{{ asset('global_assets/js/demo_pages/form_select2.js') }}"></script>
<script src="{{ asset('global_assets/js/demo_pages/datatables_extension_buttons_html5.js') }}"></script>
<script src="{{ asset('global_assets/js/demo_pages/uploader_bootstrap.js') }}"></script>
<script src="{{ asset('global_assets/js/demo_pages/fullcalendar_basic.js') }}"></script>


<!-- /theme JS files -->

<script src=" {{ asset('assets/js/custom.js') }} "></script>

{{-- Sidebar Scroll Position Memory --}}
<script>
    $(document).ready(function() {
        var $sidebarScroll = $('.sidebar-nav-scroll');
        
        if ($sidebarScroll.length) {
            // Restore scroll position on page load
            var savedScrollPos = localStorage.getItem('sidebarScrollPosition');
            if (savedScrollPos !== null) {
                $sidebarScroll.scrollTop(parseInt(savedScrollPos));
            }
            
            // Save scroll position before navigating away
            $('.sidebar-nav-scroll .nav-link').on('click', function() {
                localStorage.setItem('sidebarScrollPosition', $sidebarScroll.scrollTop());
            });
            
            // Also save on scroll (debounced) in case user scrolls then clicks browser back
            var scrollTimeout;
            $sidebarScroll.on('scroll', function() {
                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(function() {
                    localStorage.setItem('sidebarScrollPosition', $sidebarScroll.scrollTop());
                }, 150);
            });
        }
    });
</script>

@include('partials.js.custom_js')