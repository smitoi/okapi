<?php

namespace App\Http\Requests\Okapi\Instance;

use App\Models\Okapi\Instance;
use App\Services\RuleService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;

/**
 * @property Instance $instance
 *
 * Class StoreInstanceRequest
 * @package App\Http\Requests\Okapi\Instance
 */
class StoreInstanceRequest extends FormRequest
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
            $this->route('type')
        );
    }
}
