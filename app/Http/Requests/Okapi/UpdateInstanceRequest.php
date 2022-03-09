<?php

namespace App\Http\Requests\Okapi;

use App\Services\RuleService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;

class UpdateInstanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return App::make(RuleService::class)->getRequestRulesArrayForFields(
            $this->route('type')->fields->load('rules')
        );
    }
}
