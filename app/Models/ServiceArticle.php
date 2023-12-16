<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceArticle extends Model
{
    use HasFactory, SoftDeletes;

    // status
    public const STATUS_NOT_ACCEPTED = 0;
    public const STATUS_ACCEPTED = 1;
    public const STATUS_WAITING_ACCEPT = 2;


    public static $status = [
        self::STATUS_NOT_ACCEPTED => 'Không được duyệt',
        self::STATUS_ACCEPTED => 'Được duyệt',
        self::STATUS_WAITING_ACCEPT => 'Chờ duyệt'
    ];


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'user_id',
        'title',
        'content',
        'slug',
        'views',
        'images',
        'price',
        'status'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'images' => 'array'
    ];

    public static function next()
    {
        return static::max('id') + 1;
    }

    /**
     * Get the firebaseUser associated with the user.
     */
    public function firebaseUser(): BelongsTo
    {
        return $this->belongsTo(FirebaseUser::class, 'user_id', 'id');
    }
}
