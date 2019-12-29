@php echo "<?php"
@endphp


namespace {{ $modelNameSpace }};
@php
    $hasRoles = false;
    if(array_key_exists('belongsToMany', $relations) && count($relations['belongsToMany'])) {
        $hasRoles = $relations['belongsToMany']->filter(function($belongsToMany) {
            return $belongsToMany['related_table'] == 'roles';
        })->count() > 0;
        $relations['belongsToMany'] = $relations['belongsToMany']->reject(function($belongsToMany) {
            return $belongsToMany['related_table'] == 'roles';
        });
    }
@endphp

use Illuminate\Database\Eloquent\Model;
@if($fillable)@foreach($fillable as $fillableColumn)
@if($fillableColumn === "created_by_admin_user_id")use Elifbyte\Craftable\Traits\CreatedByAdminUserTrait;
@elseif($fillableColumn === "updated_by_admin_user_id")use Elifbyte\Craftable\Traits\UpdatedByAdminUserTrait;
@endif
@endforeach
@endif
@if($hasSoftDelete)use Illuminate\Database\Eloquent\SoftDeletes;
@endif
@if (isset($relations['belongsToMany']) && count($relations['belongsToMany']))
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
@endif
@if($hasRoles)use Spatie\Permission\Traits\HasRoles;
@endif
@if($translatable->count() > 0)use Brackets\Translatable\Traits\HasTranslations;
@endif
@if($hasUuid)use Elifbyte\AdminGenerator\Generate\Traits\Uuid;
@endif

class {{ $modelBaseName }} extends Model
{
@if($hasSoftDelete)
    use SoftDeletes;
@endif
@if($hasRoles)use HasRoles;
@endif
@if($translatable->count() > 0)use HasTranslations;
@endif
@if($hasUuid)use Uuid;

public $incrementing = false;
@endif

@if($fillable)@foreach($fillable as $fillableColumn)
@if($fillableColumn === "created_by_admin_user_id")use CreatedByAdminUserTrait;
@elseif($fillableColumn === "updated_by_admin_user_id")    use UpdatedByAdminUserTrait;
@endif
@endforeach
@endif
    @if (!is_null($tableName))protected $table = '{{ $tableName }}';

    @endif
@if ($fillable)protected $fillable = [
    @foreach($fillable as $f)
    '{{ $f }}',
    @endforeach

    ];
    @endif

    @if ($hidden && count($hidden) > 0)protected $hidden = [
    @foreach($hidden as $h)
    '{{ $h }}',
    @endforeach

    ];
    @endif

    @if ($dates)protected $dates = [
    @foreach($dates as $date)
    '{{ $date }}',
    @endforeach

    ];
    @endif
@if ($translatable->count() > 0)// these attributes are translatable
    public $translatable = [
    @foreach($translatable as $translatableField)
    '{{ $translatableField }}',
    @endforeach

    ];
    @endif
@if (!$timestamps)public $timestamps = false;
    @endif

    protected $appends = ['resource_url'];

    /* ************************ ACCESSOR ************************* */

    public function getResourceUrlAttribute()
    {
        return url('/admin/{{$resource}}/'.$this->getKey());
    }
@if (count($relations))

    /* ************************ RELATIONS ************************ */
@if (array_key_exists('belongsToMany', $relations) && count($relations['belongsToMany']))
@foreach($relations['belongsToMany'] as $belongsToMany)/**
    * Relation to {{ $belongsToMany['related_model_name_plural'] }}
    *
    * {{'@'}}return BelongsToMany
    */
    public function {{ $belongsToMany['related_table'] }}() {
        return $this->belongsToMany({{ $belongsToMany['related_model_class'] }}, '{{ $belongsToMany['relation_table'] }}', '{{ $belongsToMany['foreign_key'] }}', '{{ $belongsToMany['related_key'] }}');
    }
@endforeach
@endif

@if (array_key_exists('belongsTo', $relations) && count($relations['belongsTo']))
    @foreach($relations['belongsTo'] as $belongsTo)/**
    * Relation to {{ $belongsTo['related_model_name_plural'] }}
    *
    * {{'@'}}return BelongsTo
    */
    public function {{ $belongsTo['related_table'] }}() {
        return $this->belongsTo({{ $belongsTo['related_model_class'] }}, '{{ $belongsTo['foreign_key'] }}', '{{ $belongsTo['owner_key'] }}');
    }
    @endforeach
@endif

@if (array_key_exists('hasOne', $relations) && count($relations['hasOne']))
    @foreach($relations['hasOne'] as $hasOne)/**
    * Relation to {{ $hasOne['related_model_name_plural'] }}
    *
    * {{'@'}}return hasOne
    */
    public function {{ $hasOne['related_table'] }}() {
    return $this->hasOne({{ $hasOne['related_model_class'] }}, '{{ $hasOne['foreign_key'] }}', '{{ $hasOne['local_key'] }}');
    }
    @endforeach
@endif

@if (array_key_exists('hasMany', $relations) && count($relations['hasMany']))
    @foreach($relations['hasMany'] as $hasMany)/**
    * Relation to {{ $hasMany['related_model_name_plural'] }}
    *
    * {{'@'}}return hasMany
    */
    public function {{ $hasMany['related_table'] }}() {
    return $this->hasMany({{ $hasMany['related_model_class'] }}, '{{ $hasMany['foreign_key'] }}', '{{ $hasMany['local_key'] }}');
    }
    @endforeach
@endif
@endif}
