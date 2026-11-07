<?php

namespace App\Models;

use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One site-wide setting (contact details, social links, ...). Read and
 * written through App\Services\SettingService, never directly, so the
 * cached copy stays in sync. Valid keys are App\Enums\SettingKey.
 */
#[Fillable(['key', 'value'])]
class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory;
}
