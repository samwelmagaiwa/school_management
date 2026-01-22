<?php

namespace App\Helpers;

use App\Models\Setting;
use App\Models\StudentRecord;
use App\Models\Subject;
use App\Models\Department;
use Hashids\Hashids;
use Illuminate\Support\Facades\Auth;

class Qs
{
    public static function displayError($errors)
    {
        foreach ($errors as $err) {
            $data[] = $err;
        }
        return '
                <div class="alert alert-danger alert-styled-left alert-dismissible">
									<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
									<span class="font-weight-semibold">Oops!</span> '.
        implode(' ', $data).'
							    </div>
                ';
    }

    public static function getAppCode()
    {
        return self::getSetting('system_title') ?: 'samTECH';
    }

    public static function getDefaultUserImage()
    {
        return asset('global_assets/images/user.png');
    }

    public static function getPanelOptions()
    {
        return '    <div class="header-elements">
                    <div class="list-icons">
                        <a class="list-icons-item" data-action="collapse"></a>
                        <a class="list-icons-item" data-action="remove"></a>
                    </div>
                </div>';
    }

    public static function displaySuccess($msg)
    {
        return '
 <div class="alert alert-success alert-bordered">
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span><span class="sr-only">Close</span></button> '.
        $msg.'  </div>
                ';
    }

    public static function currencyUnit(): string
    {
        return 'TZSH';
    }

    public static function formatCurrency($amount, $decimals = 2): string
    {
        $value = is_numeric($amount) ? number_format((float) $amount, $decimals) : $amount;
        return self::currencyUnit().' '.$value;
    }

    public static function getTeamSA()
    {
        // Treat legacy "super_adm" as equivalent to "super_admin"
        return ['admin', 'super_admin', 'super_adm'];
    }

    public static function getTeamAccount()
    {
        return ['admin', 'super_admin', 'super_adm', 'accountant'];
    }

    public static function getTeamSAT()
    {
        return ['admin', 'super_admin', 'super_adm', 'teacher'];
    }

    public static function getTeamAcademic()
    {
        return ['admin', 'super_admin', 'super_adm', 'teacher', 'student'];
    }

    public static function getTeamAdministrative()
    {
        return ['admin', 'super_admin', 'super_adm', 'accountant'];
    }

    public static function hash($id)
    {
        $salt = 'samTECH_SECRET_STABLE';
        $hash = new Hashids($salt, 14);
        
        // Strict validation: only encode numeric values
        if (!is_numeric($id) && !is_int($id)) {
            \Log::error('Attempting to hash non-numeric value', [
                'value' => is_object($id) ? get_class($id) : $id,
                'type' => gettype($id),
                'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)
            ]);
            // Return a distinctive error that will fail validation
            return '';
        }
        
        $val = (int)$id;
        return $hash->encode($val);
    }

    public static function getUserRecord($remove = [])
    {
        $data = ['name', 'email', 'phone', 'phone2', 'dob', 'gender', 'address', 'bg_id', 'nal_id', 'state_id', 'lga_id', 'ward', 'street'];

        return $remove ? array_values(array_diff($data, $remove)) : $data;
    }

    public static function getStaffRecord($remove = [])
    {
        $data = ['emp_date',];

        return $remove ? array_values(array_diff($data, $remove)) : $data;
    }

    public static function getStudentData($remove = [])
    {
        $data = ['my_class_id', 'section_id', 'my_parent_id', 'dorm_id', 'dorm_room_id', 'dorm_bed_id', 'dorm_room_no', 'year_admitted', 'house', 'age'];

        return $remove ? array_values(array_diff($data, $remove)) : $data;

    }

    public static function decodeHash($str, $toString = true)
    {
        // If it's already a number, return it (safeguard)
        if (is_numeric($str)) {
            return $toString ? (string)$str : [(int)$str];
        }

        // Check if we received a JSON object instead of a hash
        if (is_string($str) && (str_starts_with($str, '{') || str_starts_with($str, '['))) {
            \Log::error('DecodeHash received JSON object instead of hash string', [
                'input' => substr($str, 0, 200),
                'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)
            ]);
            return '';
        }

        $salt = 'samTECH_SECRET_STABLE';
        $hash = new Hashids($salt, 14);
        $decoded = $hash->decode($str);

        // Fallback to old date-based salt for links generated today if needed
        if(count($decoded) < 1) {
            $date = date('dMY').'samTECH';
            $hash2 = new Hashids($date, 14);
            $decoded = $hash2->decode($str);
        }

        // If still empty, log the attempt for debugging
        if(count($decoded) < 1) {
             \Log::error('Hash Decoding Failed for string', [
                 'string' => substr($str, 0, 100),
                 'salt' => $salt,
                 'fallback' => date('dMY').'samTECH'
             ]);
        }

        return $toString ? implode(',', $decoded) : $decoded;
    }

    public static function userIsTeamAccount()
    {
        return in_array(Auth::user()->user_type, self::getTeamAccount()) || Auth::user()->hasPermission('payment.view');
    }

    public static function userIsTeamSA()
    {
        return in_array(Auth::user()->user_type, self::getTeamSA()) ||
            Auth::user()->user_type == 'hr' ||
            Auth::user()->hasPermission('dept.manage') ||
            Auth::user()->hasPermission('class.manage') ||
            Auth::user()->hasPermission('staff.manage') ||
            Auth::user()->hasPermission('user.view');
    }

    public static function userIsTeamSAT()
    {
        return in_array(Auth::user()->user_type, self::getTeamSAT()) ||
            Auth::user()->hasPermission('marks.manage') ||
            Auth::user()->hasPermission('student.view') ||
            Auth::user()->hasPermission('exam.manage');
    }

    public static function userIsAcademic()
    {
        return in_array(Auth::user()->user_type, self::getTeamAcademic()) || Auth::user()->hasPermission('academic.manage');
    }

    public static function userIsAdministrative()
    {
        return in_array(Auth::user()->user_type, self::getTeamAdministrative()) ||
            Auth::user()->hasPermission('payment.view') ||
            Auth::user()->hasPermission('user.view');
    }

    public static function getTeamInventory()
    {
        return ['admin', 'super_admin', 'super_adm', 'storekeeper'];
    }

    public static function userIsTeamInventory()
    {
        return in_array(Auth::user()->user_type, self::getTeamInventory()) || Auth::user()->hasPermission('inventory.manage');
    }

    public static function getTeamTransport()
    {
        return ['admin', 'super_admin', 'super_adm', 'transport_officer'];
    }

    public static function userIsTeamTransport()
    {
        return in_array(Auth::user()->user_type, self::getTeamTransport()) || Auth::user()->hasPermission('transport.manage');
    }


    public static function userIsAdmin()
    {
        return Auth::user()->user_type == 'admin';
    }

    public static function getUserType()
    {
        $type = Auth::user()->user_type;

        // Normalise legacy alias
        if ($type === 'super_adm') {
            return 'super_admin';
        }

        return $type;
    }

    public static function userIsSuperAdmin()
    {
        return in_array(Auth::user()->user_type, ['super_admin', 'super_adm']);
    }

    public static function userIsStudent()
    {
        return Auth::user()->user_type == 'student';
    }

    public static function userIsTeacher()
    {
        return Auth::user()->user_type == 'teacher';
    }

    public static function userIsParent()
    {
        return Auth::user()->user_type == 'parent';
    }

    public static function userIsStaff()
    {
        return in_array(Auth::user()->user_type, self::getStaff());
    }

    public static function getStaff($remove=[])
    {
        $data =  ['super_admin', 'super_adm', 'admin', 'teacher', 'accountant', 'librarian', 'hostel_officer'];
        return $remove ? array_values(array_diff($data, $remove)) : $data;
    }

    public static function getAllUserTypes($remove=[])
    {
        $data =  ['super_admin', 'super_adm', 'admin', 'teacher', 'accountant', 'librarian', 'hostel_officer', 'student', 'parent'];
        return $remove ? array_values(array_diff($data, $remove)) : $data;
    }

    /**
     * Check if current user is Head of Department (has at least one department with head_id = user).
     */
    public static function userIsHOD(): bool
    {
        return Department::where('head_id', Auth::id())->exists();
    }

    /**
     * Get IDs of departments where the current user is HOD.
     */
    public static function hodDepartmentIds(): array
    {
        return Department::where('head_id', Auth::id())->pluck('id')->all();
    }

    // Check if User is Head of Super Admins (Untouchable)
    public static function headSA(int $user_id)
    {
        return $user_id === 1;
    }

    public static function userIsPTA()
    {
        return in_array(Auth::user()->user_type, self::getPTA());
    }

    public static function userIsMyChild($student_id, $parent_id)
    {
        $data = ['user_id' => $student_id, 'my_parent_id' =>$parent_id];
        return StudentRecord::where($data)->exists();
    }

    public static function getSRByUserID($user_id)
    {
        return StudentRecord::where('user_id', $user_id)->first();
    }

    public static function getPTA()
    {
        return ['super_admin', 'super_adm', 'admin', 'teacher', 'parent'];
    }

    /*public static function filesToUpload($programme)
    {
        return ['birth_cert', 'passport',  'neco_cert', 'waec_cert', 'ref1', 'ref2'];
    }*/

    public static function getPublicUploadPath()
    {
        return 'uploads/';
    }

    public static function getUserUploadPath()
    {
        return 'uploads/'.date('Y').'/'.date('m').'/'.date('d').'/';
    }

    public static function getUploadPath($user_type)
    {
        return 'uploads/'.$user_type.'/';
    }

    public static function getFileMetaData($file)
    {
        //$dataFile['name'] = $file->getClientOriginalName();
        $dataFile['ext'] = $file->getClientOriginalExtension();
        $dataFile['type'] = $file->getClientMimeType();
        $dataFile['size'] = self::formatBytes($file->getSize());
        return $dataFile;
    }

    public static function generateUserCode()
    {
        return substr(uniqid(mt_rand()), -7, 7);
    }

    public static function formatBytes($size, $precision = 2)
    {
        $base = log($size, 1024);
        $suffixes = array('B', 'KB', 'MB', 'GB', 'TB');

        return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
    }

    public static function getSetting($type)
    {
        $setting = Setting::where('type', $type)->first();

        // Gracefully handle missing settings instead of throwing
        return $setting->description ?? null;
    }

    public static function getCurrentSession()
    {
        // Fall back to a sane default if not configured
        return self::getSetting('current_session') ?? '2018-2019';
    }

    public static function getNextSession()
    {
        $oy = self::getCurrentSession();

        if (! $oy) {
            return '2019-2020';
        }

        $old_yr = explode('-', $oy);

        // If it's a range (e.g. 2018-2019)
        if (isset($old_yr[1]) && is_numeric($old_yr[0]) && is_numeric($old_yr[1])) {
            return ++$old_yr[0].'-'.++$old_yr[1];
        }

        // If it's a single year (e.g. 2018)
        if (is_numeric($old_yr[0])) {
            $next = $old_yr[0] + 1;
            return $old_yr[0].'-'.$next;
        }

        return '2019-2020';
    }

    public static function getSystemName()
    {
        return self::getSetting('system_name') ?? 'samTECH ACADEMY';
    }

    public static function findMyChildren($parent_id)
    {
        return StudentRecord::where('my_parent_id', $parent_id)->with(['user', 'my_class'])->get();
    }

    public static function findTeacherSubjects($teacher_id)
    {
        return Subject::where('teacher_id', $teacher_id)->with('my_class')->get();
    }

    public static function findStudentRecord($user_id)
    {
        return StudentRecord::where('user_id', $user_id)->first();
    }

    public static function getMarkType($class_type)
    {
       switch($class_type){
           case 'J' : return 'junior';
           case 'S' : return 'senior';
           case 'N' : return 'nursery';
           case 'P' : return 'primary';
           case 'PN' : return 'pre_nursery';
           case 'C' : return 'creche';
       }
        return $class_type;
    }

    public static function json($msg, $ok = TRUE, $arr = [])
    {
        return $arr ? response()->json($arr) : response()->json(['ok' => $ok, 'msg' => $msg]);
    }

    public static function jsonStoreOk()
    {
        return self::json(__('msg.store_ok'));
    }

    public static function jsonUpdateOk()
    {
        return self::json(__('msg.update_ok'));
    }

    public static function jsonDeleteOk()
    {
        return self::json(__('msg.del_ok'));
    }

    public static function storeOk($routeName)
    {
        return self::goWithSuccess($routeName, __('msg.store_ok'));
    }

    public static function deleteOk($routeName)
    {
        return self::goWithSuccess($routeName, __('msg.del_ok'));
    }

    public static function updateOk($routeName)
    {
        return self::goWithSuccess($routeName, __('msg.update_ok'));
    }

    public static function goToRoute($goto, $status = 302, $headers = [], $secure = null)
    {
        $data = [];
        $to = (is_array($goto) ? $goto[0] : $goto) ?: 'dashboard';
        if(is_array($goto)){
            array_shift($goto);
            $data = $goto;
        }
        return app('redirect')->to(route($to, $data), $status, $headers, $secure);
    }

    public static function goWithDanger($to = 'dashboard', $msg = NULL)
    {
        $msg = $msg ? $msg : __('msg.rnf');
        return self::goToRoute($to)->with('flash_danger', $msg);
    }

    public static function goWithSuccess($to, $msg)
    {
        return self::goToRoute($to)->with('flash_success', $msg);
    }

    public static function getDaysOfTheWeek()
    {
        return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    }

}
