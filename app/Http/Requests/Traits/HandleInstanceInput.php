<?php

namespace App\Http\Requests\Traits;

trait HandleInstanceInput
{
    public function prepareForValidation(): void
    {
        $relationships = $this->type->relationships->pluck('slug');
        $parameters = $this->all();
        foreach ($parameters as $key => $value) {
            if ($relationships->contains($key)) {
                if (is_array($value)) {
                    $parameters[$key] = $value ?? null;
                } elseif (!empty($value)) {
                    $parameters[$key] = $value ?? null;
                }
            }
        }

        $this->replace($parameters);
    }
}
