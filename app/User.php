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
use Illuminate\Support\Facades\Cache;
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
'name', 'username', 'email', 'phone', 'phone2', 'dob', 'gender', 'photo', 'address', 'ward', 'street', 'bg_id', 'password', 'nal_id', 'state_id', 'lga_id', 'code', 'user_type', 'email_verified_at', 'last_seen_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
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

    public function user_type_rec()
    {
        return $this->belongsTo(\App\Models\UserType::class, 'user_type', 'title');
    }

    public function permissions()
    {
        return $this->belongsToMany(\App\Models\Permission::class);
    }

    public function hasPermission($permission_name)
    {
        // Check if user has permission directly
        if ($this->permissions->contains('name', $permission_name)) {
            return true;
        }

        // Check if user has permission through role
        if ($this->user_type_rec && $this->user_type_rec->permissions->contains('name', $permission_name)) {
            return true;
        }

        return false;
    }

    public function getIsOnlineAttribute(): bool
    {
        return Cache::has($this->onlineCacheKey());
    }

    protected function onlineCacheKey(): string
    {
        return sprintf('user-online-%d', $this->id);
    }
}
