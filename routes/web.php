<?php

Auth::routes();

//Route::get('/test', 'TestController@index')->name('test');
Route::get('/privacy-policy', 'HomeController@privacy_policy')->name('privacy_policy');
Route::get('/terms-of-use', 'HomeController@terms_of_use')->name('terms_of_use');


Route::group(['middleware' => 'auth'], function () {

    Route::get('/', 'HomeController@dashboard')->name('home');
    Route::get('/home', 'HomeController@dashboard')->name('home');
    Route::get('/dashboard', 'HomeController@dashboard')->name('dashboard');

    Route::group(['prefix' => 'my_account'], function() {
        Route::get('/', 'MyAccountController@edit_profile')->name('my_account');
        Route::put('/', 'MyAccountController@update_profile')->name('my_account.update');
        Route::put('/change_password', 'MyAccountController@change_pass')->name('my_account.change_pass');
    });

    /*************** Support Team *****************/
    // Student self-service routes
    Route::group(['namespace' => 'Student'], function () {
        Route::get('/my_attendance', 'AttendanceController@index')->name('student.attendance');
    });

    Route::group(['namespace' => 'SupportTeam',], function(){

        /*************** Students *****************/
        Route::group(['prefix' => 'students'], function(){
            Route::get('reset_pass/{st_id}', 'StudentRecordController@reset_pass')->name('st.reset_pass');
            Route::get('graduated', 'StudentRecordController@graduated')->name('students.graduated');
            Route::put('not_graduated/{id}', 'StudentRecordController@not_graduated')->name('st.not_graduated');
            Route::get('list/{class_id}', 'StudentRecordController@listByClass')->name('students.list')->middleware('teamSAT');

            /* Promotions */
            Route::post('promote_selector', 'PromotionController@selector')->name('students.promote_selector');
            Route::get('promotion/manage', 'PromotionController@manage')->name('students.promotion_manage');
            Route::delete('promotion/reset/{pid}', 'PromotionController@reset')->name('students.promotion_reset');
            Route::delete('promotion/reset_all', 'PromotionController@reset_all')->name('students.promotion_reset_all');
            Route::get('promotion/{fc?}/{fs?}/{tc?}/{ts?}', 'PromotionController@promotion')->name('students.promotion');
            Route::post('promote/{fc}/{fs}/{tc}/{ts}', 'PromotionController@promote')->name('students.promote');

        });

        /*************** Users *****************/
        Route::group(['prefix' => 'users'], function(){
            Route::get('reset_pass/{id}', 'UserController@reset_pass')->name('users.reset_pass');
        });

        /*************** TimeTables *****************/
        Route::group(['prefix' => 'timetables'], function(){
            Route::get('/', 'TimeTableController@index')->name('tt.index');

            Route::group(['middleware' => 'teamSA'], function() {
                Route::post('/', 'TimeTableController@store')->name('tt.store');
                Route::put('/{tt}', 'TimeTableController@update')->name('tt.update');
                Route::delete('/{tt}', 'TimeTableController@delete')->name('tt.delete');
            });

            /*************** TimeTable Records *****************/
            Route::group(['prefix' => 'records'], function(){

                Route::group(['middleware' => 'teamSA'], function(){
                    Route::get('manage/{ttr}', 'TimeTableController@manage')->name('ttr.manage');
                    Route::post('/', 'TimeTableController@store_record')->name('ttr.store');
                    Route::get('edit/{ttr}', 'TimeTableController@edit_record')->name('ttr.edit');
                    Route::put('/{ttr}', 'TimeTableController@update_record')->name('ttr.update');
                });

                Route::get('show/{ttr}', 'TimeTableController@show_record')->name('ttr.show');
                Route::get('print/{ttr}', 'TimeTableController@print_record')->name('ttr.print');
                Route::delete('/{ttr}', 'TimeTableController@delete_record')->name('ttr.destroy');

            });

            /*************** Time Slots *****************/
            Route::group(['prefix' => 'time_slots', 'middleware' => 'teamSA'], function(){
                Route::post('/', 'TimeTableController@store_time_slot')->name('ts.store');
                Route::post('/use/{ttr}', 'TimeTableController@use_time_slot')->name('ts.use');
                Route::get('edit/{ts}', 'TimeTableController@edit_time_slot')->name('ts.edit');
                Route::delete('/{ts}', 'TimeTableController@delete_time_slot')->name('ts.destroy');
                Route::put('/{ts}', 'TimeTableController@update_time_slot')->name('ts.update');
            });

        });

        /*************** Payments *****************/
        Route::group(['prefix' => 'payments'], function(){

            Route::get('manage/{class_id?}', 'PaymentController@manage')->name('payments.manage');
            Route::get('invoice/{id}/{year?}', 'PaymentController@invoice')->name('payments.invoice');
            Route::get('receipts/{id}', 'PaymentController@receipts')->name('payments.receipts');
            Route::get('pdf_receipts/{id}', 'PaymentController@pdf_receipts')->name('payments.pdf_receipts');
            Route::post('select_year', 'PaymentController@select_year')->name('payments.select_year');
            Route::post('select_class', 'PaymentController@select_class')->name('payments.select_class');
            Route::delete('reset_record/{id}', 'PaymentController@reset_record')->name('payments.reset_record');
            Route::post('pay_now/{id}', 'PaymentController@pay_now')->name('payments.pay_now');
        });

        /*************** Pins *****************/
        Route::group(['prefix' => 'pins'], function(){
            Route::get('create', 'PinController@create')->name('pins.create');
            Route::get('/', 'PinController@index')->name('pins.index');
            Route::post('/', 'PinController@store')->name('pins.store');
            Route::get('enter/{id}', 'PinController@enter_pin')->name('pins.enter');
            Route::post('verify/{id}', 'PinController@verify')->name('pins.verify');
            Route::delete('/', 'PinController@destroy')->name('pins.destroy');
        });

        /*************** Activity Logs *****************/
        Route::get('activity/logs', 'ActivityLogController@index')->name('activity.logs.index')->middleware('teamSA');

        /*************** Library *****************/
        Route::group(['prefix' => 'library', 'as' => 'library.'], function () {

            Route::resource('books', 'BookController');
            Route::post('books/{book}/copies', 'BookController@storeCopies')->name('books.copies.store');
            Route::put('copies/{copy}/status', 'BookController@updateCopyStatus')->name('copies.status.update');
            Route::delete('copies/{copy}', 'BookController@destroyCopy')->name('copies.destroy');

            // Book categories management
            Route::get('categories', 'BookCategoryController@index')->name('categories.index');
            Route::post('categories', 'BookCategoryController@store')->name('categories.store');
            Route::put('categories/{category}', 'BookCategoryController@update')->name('categories.update');
            Route::delete('categories/{category}', 'BookCategoryController@destroy')->name('categories.destroy');

            Route::group(['prefix' => 'loans', 'as' => 'loans.'], function () {
                Route::get('/', 'BookLoanController@index')->name('index')->middleware('libraryManager');
                Route::get('overdue', 'BookLoanController@overdue')->name('overdue')->middleware('libraryManager');
                Route::get('my', 'BookLoanController@myLoans')->name('my');
                Route::get('children', 'BookLoanController@childrenLoans')->name('children');
                Route::get('{loan}', 'BookLoanController@show')->name('show')->middleware('libraryManager');
                Route::post('/', 'BookLoanController@store')->name('store')->middleware('libraryManager');
                Route::post('{loan}/return', 'BookLoanController@return')->name('return')->middleware('libraryManager');
                Route::post('{loan}/waive', 'BookLoanController@waiveFine')->name('waive')->middleware('libraryManager');
                Route::post('{loan}/force-close', 'BookLoanController@forceClose')->name('force_close')->middleware('libraryManager');
                Route::post('{loan}/reverse', 'BookLoanController@reverse')->name('reverse')->middleware('libraryManager');
            });

            Route::group(['prefix' => 'requests', 'as' => 'requests.'], function () {
                Route::get('/', 'BookRequestController@index')->name('index')->middleware('libraryManager');
                Route::get('my', 'BookRequestController@myRequests')->name('my');
                Route::post('/', 'BookRequestController@store')->name('store');
                Route::post('{bookRequest}/approve', 'BookRequestController@approve')->name('approve')->middleware('libraryManager');
                Route::post('{bookRequest}/reject', 'BookRequestController@reject')->name('reject')->middleware('libraryManager');
                Route::post('{bookRequest}/cancel', 'BookRequestController@cancel')->name('cancel');
            });
        });

        /*************** Attendance *****************/
        Route::group(['prefix' => 'attendance', 'as' => 'attendance.'], function () {

            // Session lifecycle
            Route::get('sessions', 'AttendanceSessionController@index')->name('sessions.index');
            Route::post('sessions', 'AttendanceSessionController@store')->name('sessions.store');
            Route::get('sessions/{session}', 'AttendanceSessionController@show')->name('sessions.show');

            // Marking & submission
            Route::get('sessions/{session}/mark', 'AttendanceMarkController@mark')->name('sessions.mark');
            Route::post('sessions/{session}/records', 'AttendanceMarkController@storeRecords')->name('sessions.records.store');
            Route::post('sessions/{session}/submit', 'AttendanceMarkController@submit')->name('sessions.submit');

            // Admin overrides
            Route::post('sessions/{session}/unlock', 'AttendanceAdminController@unlock')->name('sessions.unlock');
            Route::put('records/{record}/override', 'AttendanceAdminController@overrideRecord')->name('records.override');

            // Reports
            // Reports (HTML + JSON)
            Route::get('reports', 'AttendanceReportController@reportsIndex')->name('reports.index');
            Route::get('reports/student', 'AttendanceReportController@studentReportPage')->name('reports.student_page');
            Route::get('reports/class', 'AttendanceReportController@classReportPage')->name('reports.class_page');

            // JSON API endpoints (kept for programmatic use)
            Route::get('reports/student/{student}', 'AttendanceReportController@studentReport')->name('reports.student');
            Route::get('reports/class/{class}/{section}', 'AttendanceReportController@classReport')->name('reports.class');
            Route::get('reports/teacher-compliance', 'AttendanceReportController@teacherCompliance')->name('reports.teacher_compliance');
        });

        /*************** Marks *****************/
        // Departments (HOD / admin management)
        Route::group(['prefix' => 'departments', 'middleware' => 'teamSA'], function () {
            Route::get('/', 'DepartmentController@index')->name('departments.index');
            Route::post('/', 'DepartmentController@store')->name('departments.store');
            Route::put('{department}', 'DepartmentController@update')->name('departments.update');
            Route::delete('{department}', 'DepartmentController@destroy')->name('departments.destroy');
        });

        /*************** Marks *****************/
        Route::group(['prefix' => 'marks'], function(){

           // FOR teamSA
            Route::group(['middleware' => 'teamSA'], function(){
                Route::get('batch_fix', 'MarkController@batch_fix')->name('marks.batch_fix');
                Route::put('batch_update', 'MarkController@batch_update')->name('marks.batch_update');
                Route::get('tabulation/{exam?}/{class?}/{sec_id?}', 'MarkController@tabulation')->name('marks.tabulation');
                Route::post('tabulation', 'MarkController@tabulation_select')->name('marks.tabulation_select');
                Route::get('tabulation/print/{exam}/{class}/{sec_id}', 'MarkController@print_tabulation')->name('marks.print_tabulation');
            });

            // FOR teamSAT
            Route::group(['middleware' => 'teamSAT'], function(){
                Route::get('/', 'MarkController@index')->name('marks.index');
                Route::get('manage/{exam}/{class}/{section}/{subject}', 'MarkController@manage')->name('marks.manage');
                Route::put('update/{exam}/{class}/{section}/{subject}', 'MarkController@update')->name('marks.update');
                Route::put('comment_update/{exr_id}', 'MarkController@comment_update')->name('marks.comment_update');
                Route::put('skills_update/{skill}/{exr_id}', 'MarkController@skills_update')->name('marks.skills_update');
                Route::post('selector', 'MarkController@selector')->name('marks.selector');
                Route::get('bulk/{class?}/{section?}', 'MarkController@bulk')->name('marks.bulk');
                Route::post('bulk', 'MarkController@bulk_select')->name('marks.bulk_select');
            });

            Route::get('select_year/{id}', 'MarkController@year_selector')->name('marks.year_selector');
            Route::post('select_year/{id}', 'MarkController@year_selected')->name('marks.year_select');
            Route::get('show/{id}/{year}', 'MarkController@show')->name('marks.show');
            Route::get('print/{id}/{exam_id}/{year}', 'MarkController@print_view')->name('marks.print');

        });

        Route::resource('students', 'StudentRecordController');
        Route::resource('users', 'UserController');
        Route::resource('classes', 'MyClassController');
        Route::resource('sections', 'SectionController');
        Route::resource('subjects', 'SubjectController');
        Route::resource('grades', 'GradeController');
        Route::resource('exams', 'ExamController');
        Route::resource('dorms', 'DormController');
        Route::group(['prefix' => 'dorms/{dorm}', 'as' => 'dorms.', 'middleware' => 'custom.hostel'], function () {
            Route::post('rooms', 'DormRoomController@store')->name('rooms.store');
            Route::put('rooms/{room}', 'DormRoomController@update')->name('rooms.update');
            Route::delete('rooms/{room}', 'DormRoomController@destroy')->name('rooms.destroy');

            Route::post('rooms/{room}/beds', 'DormBedController@store')->name('rooms.beds.store');
            Route::put('rooms/{room}/beds/{bed}', 'DormBedController@update')->name('rooms.beds.update');
            Route::delete('rooms/{room}/beds/{bed}', 'DormBedController@destroy')->name('rooms.beds.destroy');
        });

        Route::post('students/{student}/allocation', 'DormAllocationController@store')->name('students.allocation.store')->middleware('custom.hostel');
        Route::post('students/{student}/allocation/vacate', 'DormAllocationController@vacate')->name('students.allocation.vacate')->middleware('custom.hostel');
        Route::resource('payments', 'PaymentController');
 
     });

    Route::group(['prefix' => 'accounting', 'middleware' => 'teamAccount'], function () {
        Route::get('fee-categories', [\App\Http\Controllers\Accounting\FeeCategoryController::class, 'index'])->name('accounting.fee-categories.index');
        Route::post('fee-categories', [\App\Http\Controllers\Accounting\FeeCategoryController::class, 'store'])->name('accounting.fee-categories.store');
        Route::put('fee-categories/{fee_category}', [\App\Http\Controllers\Accounting\FeeCategoryController::class, 'update'])->name('accounting.fee-categories.update');

        // Fee structures overview
        Route::get('fee-structures', [\App\Http\Controllers\Accounting\FeeStructureController::class, 'index'])->name('accounting.fee-structures.index');

        // Fee structure management, installment plans & bulk billing (Admin / Super Admin only)
        Route::group(['middleware' => 'teamSA'], function () {
            Route::post('fee-structures', [\App\Http\Controllers\Accounting\FeeStructureController::class, 'store'])
                ->name('accounting.fee-structures.store');
            Route::put('fee-structures/{structure}', [\App\Http\Controllers\Accounting\FeeStructureController::class, 'update'])
                ->name('accounting.fee-structures.update');
            Route::delete('fee-structures/{structure}', [\App\Http\Controllers\Accounting\FeeStructureController::class, 'destroy'])
                ->name('accounting.fee-structures.destroy');
            Route::get('fee-structures/{structure}/installments', [\App\Http\Controllers\Accounting\FeeInstallmentPlanController::class, 'index'])
                ->name('accounting.installments.index');
            Route::post('fee-structures/{structure}/installments', [\App\Http\Controllers\Accounting\FeeInstallmentPlanController::class, 'storePlan'])
                ->name('accounting.installments.plan.store');
            Route::post('fee-structures/{structure}/installments/{plan}/rows', [\App\Http\Controllers\Accounting\FeeInstallmentPlanController::class, 'storeInstallment'])
                ->name('accounting.installments.rows.store');
            Route::put('installments/{installment}', [\App\Http\Controllers\Accounting\FeeInstallmentPlanController::class, 'updateInstallment'])
                ->name('accounting.installments.rows.update');
            Route::delete('installments/{installment}', [\App\Http\Controllers\Accounting\FeeInstallmentPlanController::class, 'destroyInstallment'])
                ->name('accounting.installments.rows.destroy');

            Route::post('fee-structures/{structure}/billing/generate', [\App\Http\Controllers\Accounting\StudentBillingController::class, 'generateForStructure'])
                ->name('accounting.fee-structures.billing.generate');
        });

        // Invoices & payments
        Route::get('invoices', [\App\Http\Controllers\Accounting\InvoiceController::class, 'index'])->name('accounting.invoices.index');
        Route::get('invoices/{invoice}', [\App\Http\Controllers\Accounting\InvoiceController::class, 'show'])->name('accounting.invoices.show');
        Route::post('invoices', [\App\Http\Controllers\Accounting\AccountingActionController::class, 'storeInvoice'])->name('accounting.invoices.store');

        Route::get('students/{student}/account', [\App\Http\Controllers\Accounting\StudentAccountController::class, 'show'])->name('accounting.students.account');
        Route::post('students/{student}/account/payments', [\App\Http\Controllers\Accounting\StudentAccountController::class, 'recordPayment'])->name('accounting.students.account.payments');

        Route::get('payments', [\App\Http\Controllers\Accounting\PaymentLedgerController::class, 'index'])->name('accounting.payments.index');
        Route::post('payments', [\App\Http\Controllers\Accounting\AccountingActionController::class, 'recordPayment'])->name('accounting.payments.store');
        Route::post('payments/{payment}/reverse', [\App\Http\Controllers\Accounting\AccountingActionController::class, 'reversePayment'])->name('accounting.payments.reverse');

        Route::post('invoices/{invoice}/waiver', [\App\Http\Controllers\Accounting\AccountingActionController::class, 'approveWaiver'])->name('accounting.invoices.waiver');

        // Period locks
        Route::post('periods/{period}/lock', [\App\Http\Controllers\Accounting\AccountingActionController::class, 'lockPeriod'])->name('accounting.periods.lock');
        Route::post('periods/{period}/unlock', [\App\Http\Controllers\Accounting\AccountingActionController::class, 'unlockPeriod'])->name('accounting.periods.unlock');

        // Expenses & reports
        Route::get('expenses', [\App\Http\Controllers\Accounting\ExpenseController::class, 'index'])->name('accounting.expenses.index');
        Route::get('reports', [\App\Http\Controllers\Accounting\ReportController::class, 'index'])->name('accounting.reports.index');
    });

    /************************ AJAX *****************************/
    Route::group(['prefix' => 'ajax'], function() {
        Route::get('get_states/{nal_id}', 'AjaxController@get_states')->name('get_states');
        Route::get('get_lga/{state_id}', 'AjaxController@get_lga')->name('get_lga');
        Route::get('get_wards/{lga_id}', 'AjaxController@get_wards')->name('get_wards');
        Route::get('get_villages/{ward_id}', 'AjaxController@get_villages')->name('get_villages');
        Route::get('get_places/{village_id}', 'AjaxController@get_places')->name('get_places');
        Route::get('get_class_sections/{class_id}', 'AjaxController@get_class_sections')->name('get_class_sections');
        Route::get('get_class_subjects/{class_id}', 'AjaxController@get_class_subjects')->name('get_class_subjects');
        Route::get('dorms/{dorm}/rooms', 'AjaxController@getDormRooms')->name('ajax.dorm.rooms');
        Route::get('rooms/{room}/beds', 'AjaxController@getRoomBeds')->name('ajax.dorm.beds');
    });

});

/************************ SUPER ADMIN ****************************/
Route::group(['namespace' => 'SuperAdmin','middleware' => 'super_admin', 'prefix' => 'super_admin'], function(){

    Route::get('/settings', 'SettingController@index')->name('settings');
    Route::put('/settings', 'SettingController@update')->name('settings.update');

});

/************************ PARENT ****************************/
Route::group(['namespace' => 'MyParent','middleware' => 'my_parent',], function(){

    Route::get('/my_children', 'MyController@children')->name('my_children');
    Route::get('/my_children/attendance', 'MyController@childAttendance')->name('my_children.attendance');

});
