<?php

namespace App\Http\Requests\Dashboard;

use App\Enums\SettingKey;

class SettingRequest extends DashboardFormRequest
{
    /**
     * One rule set per known setting key (see App\Enums\SettingKey).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return collect(SettingKey::cases())
            ->mapWithKeys(fn (SettingKey $key) => [$key->value => $key->rules()])
            ->all();
    }
}
