<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table('employer_profiles')]
#[Fillable([
    'user_id',
    'company_name',
    'company_summary',
    'logo_disk',
    'logo_path',
    'logo_original_name',
])]
#[Hidden(['logo_disk', 'logo_path'])]
class EmployerProfile extends Model
{
    /**
     * TODO: add User <-> EmployerProfile relationship methods
     * once user/auth domain wiring is finalized.
     */
}
