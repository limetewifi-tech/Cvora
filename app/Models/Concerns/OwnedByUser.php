<?php

namespace App\Models\Concerns;

trait OwnedByUser
{
    public function resolveRouteBinding($value, $field = null)
    {
        $field ??= $this->getRouteKeyName();

        return $this->newQuery()
            ->where($field, $value)
            ->where($this->getTable().'.user_id', auth()->id() ?? 0)
            ->first();
    }
}
