<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }


    public $timestamp = true;

    public $badgeId = "badge_id";

    const CREATED_AT = 'createdate';
    const UPDATED_AT = ' updatedate';

    protected $dateFormat = 'Y-m-d';
    protected $keyType = 'string';
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'badge_id';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_karyawan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'badge_id',
        'email',
        'password',
        'fullname',
        'dept_code',
        'line_code',
        'rfid_no',
        'position_code',
        'id_grup',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the regis status associated with the user.
     */
    public function regisStatus(): BelongsTo
    {
        return $this->belongsTo(Lookup::class, 'regis_mysatnusa', 'id');
    }

    public function pemberitahuan(): HasMany
    {
        return $this->hasMany(Notification::class, 'employee_no', 'sent_by');
    }

    public function penerimaPemberitahuan()
    {
        return $this->hasMany(NotificationReceiver::class, 'employee_no');
    }


    /* scope */
    public function scopeLoggedIn(Builder $query): void
    {
        $query->where('employee_no', session('loggedInUser'));
    }
}
