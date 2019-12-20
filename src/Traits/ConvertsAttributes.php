<?php

namespace AdditionApps\Convoy\Traits;

use Illuminate\Support\Carbon;

trait ConvertsAttributes
{
    public function prepareAttributes($attributes = []): array
    {
        return collect($attributes)
            ->mapWithKeys(function ($value, $key) {
                if (array_key_exists($key, $this->casts)) {
                    switch ($this->casts[$key]) {
                        case 'json':
                            return [$key => $this->toJson($value)];
                        case 'date':
                            return [$key => $this->toDate($value)];
                    }
                }

                return [$key => $value];
            })
            ->all();
    }

    protected function toJson(array $value): string
    {
        return json_encode($value);
    }

    protected function toDate(Carbon $value): string
    {
        return $value->toDateTimeString();
    }

    public function castAttributes($attributes = []): array
    {
        return collect($attributes)
            ->mapWithKeys(function ($value, $key) {
                if (array_key_exists($key, $this->casts)) {
                    switch ($this->casts[$key]) {
                        case 'json':
                            return [$key => $this->fromJson($value)];
                        case 'date':
                            return [$key => $this->fromDate($value)];
                    }
                }

                return [$key => $value];
            })
            ->all();
    }

    protected function fromJson(string $value): array
    {
        return json_decode($value, true);
    }

    protected function fromDate(string $value): Carbon
    {
        return Carbon::parse($value);
    }
}
