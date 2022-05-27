<?php

namespace App\Http\Requests\Okapi\Instance;

use App\Models\Okapi\Instance;
use App\Models\Okapi\Type;
use App\Services\RuleService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;

/**
 * @property Instance $instance
 * @property Type $type
 *
 * Class UpdateInstanceRequest
 * @package App\Http\Requests\Okapi\Instance
 */
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
            $this->route('type'), Instance::queryForType($this->type)->where('id', $this->instance)->firstOrFail(),
        );
    }
}
