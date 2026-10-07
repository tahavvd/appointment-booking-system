<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the admin middleware already guards these routes
    }

    public function rules(): array
    {
        $service = $this->route('service'); // null when creating

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('services', 'name')->ignore($service?->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'base_price' => ['required', 'numeric', 'min:0', 'max:999999'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'photo' => [
                $service ? 'nullable' : 'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
                'dimensions:min_width=400,min_height=300',
            ],
            'is_active' => ['nullable', 'boolean'],
            'addons' => ['nullable', 'array', 'max:12'],
            'addons.*.id' => ['nullable', 'integer'],
            'addons.*.name' => ['required', 'string', 'max:255'],
            'addons.*.extra_price' => ['required', 'numeric', 'min:0', 'max:99999'],
            'addons.*.extra_duration_minutes' => ['required', 'integer', 'min:0', 'max:240'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'You already have a service with this name.',
            'photo.required' => 'Please upload a photo of the service.',
            'photo.image' => 'The photo must be an image.',
            'photo.mimes' => 'Use a JPG, PNG or WebP photo.',
            'photo.max' => 'The photo must be 4 MB or smaller.',
            'photo.uploaded' => 'The photo could not be uploaded. Check the file size.',
            'photo.dimensions' => 'The photo must be at least 400 × 300 pixels.',
            'addons.*.name.required' => 'Every add-on needs a name.',
            'addons.*.extra_price.required' => 'Every add-on needs a price (0 is fine).',
            'addons.*.extra_duration_minutes.required' => 'Every add-on needs a duration (0 is fine).',
        ];
    }
}
