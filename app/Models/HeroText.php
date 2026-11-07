<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedAttributes;
use Database\Factories\HeroTextFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * The single, site-wide headline shown in the homepage hero section. A
 * singleton: the admin edits one row (the first/only one) rather than
 * managing a list, via HeroTextController's get/update actions.
 */
#[Fillable(['text_ar', 'text_en'])]
class HeroText extends Model
{
    /** @use HasFactory<HeroTextFactory> */
    use HasFactory, HasLocalizedAttributes;
}
