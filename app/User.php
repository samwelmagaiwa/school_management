<?php

namespace App;

use App\Helpers\Qs;
use App\Models\BloodGroup;
use App\Models\Lga;
use App\Models\Nationality;
use App\Models\StaffRecord;
use App\Models\State;
use App\Models\StudentRecord;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
'name', 'username', 'email', 'phone', 'phone2', 'dob', 'gender', 'photo', 'address', 'ward', 'street', 'bg_id', 'password', 'nal_id', 'state_id', 'lga_id', 'code', 'user_type', 'email_verified_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function getPhotoAttribute($value)
    {
        // If nothing is stored, always fall back to the default avatar
        if (!$value) {
            return Qs::getDefaultUserImage();
        }

        // If the value is already a full URL (http/https), normalize it so that
        // images keep working even if APP_URL or the project path changes.
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            $parts = parse_url($value);
            $path = isset($parts['path']) ? ltrim($parts['path'], '/') : '';

            // Handle cases where the path might contain a subdirectory prefix (e.g., /lav_sms/storage/...)
            // by locating the known asset directories within the path.
            foreach (['global_assets/', 'assets/', 'storage/'] as $prefix) {
                if (($pos = strpos($path, $prefix)) !== false) {
                    return asset(substr($path, $pos));
                }
            }

            // For non-local URLs (external avatars, etc.), just return as-is
            return $value;
        }

        // Normalize leading slashes for relative paths
        $path = ltrim($value, '/');

        // If the path already points to a public asset directory (e.g. global_assets, assets, storage),
        // just wrap it with asset(). This covers default images like "global_assets/images/user.png".
        if (Str::startsWith($path, ['global_assets/', 'assets/', 'storage/'])) {
            return asset($path);
        }

        // Otherwise, assume the file lives on the "public" disk under /storage
        return asset('storage/' . $path);
    }

    public function student_record()
    {
        return $this->hasOne(StudentRecord::class);
    }

    public function lga()
    {
        return $this->belongsTo(Lga::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function nationality()
    {
        return $this->belongsTo(Nationality::class, 'nal_id');
    }

    public function blood_group()
    {
        return $this->belongsTo(BloodGroup::class, 'bg_id');
    }

    public function staff()
    {
        return $this->hasMany(StaffRecord::class);
    }
}
