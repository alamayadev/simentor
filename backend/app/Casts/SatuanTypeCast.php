<?php

namespace App\Casts;

use App\Enums\SatuanType;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class SatuanTypeCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($value === 'OB') {
            $value = 'O-B';
        }

        return SatuanType::tryFrom($value) ?? $value;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value instanceof SatuanType) {
            return $value->value;
        }

        if ($value === 'OB') {
            return 'O-B';
        }

        $enum = SatuanType::tryFrom($value);

        return $enum?->value ?? $value;
    }
}
