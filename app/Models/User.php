<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'role_id',
        'department_id',
        'full_name',
        'email',
        'password_hash',
        'phone',
        'is_active',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Kolom password kustom (tabel menggunakan 'password_hash', bukan 'password' default Laravel).
     */
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    // ------------------------------------------------------------------
    // Relasi
    // ------------------------------------------------------------------

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }

    public function requestedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'requester_id', 'user_id');
    }

    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_to', 'user_id');
    }

    public function ticketComments(): HasMany
    {
        return $this->hasMany(TicketComment::class, 'user_id', 'user_id');
    }

    public function uploadedAttachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'uploaded_by', 'user_id');
    }

    public function ownedAssets(): HasMany
    {
        return $this->hasMany(Asset::class, 'owner_id', 'user_id');
    }

    public function knowledgeArticles(): HasMany
    {
        return $this->hasMany(KnowledgeArticle::class, 'created_by', 'user_id');
    }

    /**
     * Catatan: method ini sengaja menimpa (override) method notifications()
     * bawaan trait Notifiable, karena sistem ini memakai tabel notifications
     * kustom sendiri (App\Models\Notification), bukan sistem notifikasi
     * database bawaan Laravel.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id', 'user_id');
    }
}
