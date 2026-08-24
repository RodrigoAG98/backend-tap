<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use MongoDB\Laravel\Eloquent\Model as MongoModel;
use Spatie\Activitylog\Contracts\Activity as ActivityContract;

class Activity extends MongoModel implements ActivityContract
{
    public $guarded = [];

    protected $connection = 'mongodb';

    protected $table = 'activity_log';

    protected $primaryKey = '_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $casts = [
        'properties' => 'collection',
    ];

    public function subject(): MorphTo
    {
        if (config('activitylog.include_soft_deleted_subjects', false)) {
            return $this->morphTo()
                ->withoutGlobalScope(SoftDeletingScope::class);
        }

        return $this->morphTo();
    }

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    public function getExtraProperty(
        string $propertyName,
        mixed $defaultValue = null
    ): mixed {
        return Arr::get(
            $this->properties?->toArray() ?? [],
            $propertyName,
            $defaultValue
        );
    }

    public function changes(): Collection
    {
        if (! $this->properties instanceof Collection) {
            return new Collection();
        }

        return $this->properties->only([
            'attributes',
            'old',
        ]);
    }

    public function getChangesAttribute(): Collection
    {
        return $this->changes();
    }

    public function scopeInLog(
        Builder $query,
        ...$logNames
    ): Builder {
        if (is_array($logNames[0] ?? null)) {
            $logNames = $logNames[0];
        }

        return $query->whereIn('log_name', $logNames);
    }

    public function scopeCausedBy(
        Builder $query,
        Model $causer
    ): Builder {
        return $query
            ->where('causer_type', $causer->getMorphClass())
            ->where('causer_id', $causer->getKey());
    }

    public function scopeForSubject(
        Builder $query,
        Model $subject
    ): Builder {
        return $query
            ->where('subject_type', $subject->getMorphClass())
            ->where('subject_id', $subject->getKey());
    }

    public function scopeForEvent(
        Builder $query,
        string $event
    ): Builder {
        return $query->where('event', $event);
    }

    public function scopeHasBatch(
        Builder $query
    ): Builder {
        return $query->whereNotNull('batch_uuid');
    }

    public function scopeForBatch(
        Builder $query,
        string $batchUuid
    ): Builder {
        return $query->where('batch_uuid', $batchUuid);
    }
}