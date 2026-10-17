<?php

namespace App\Http\Requests;

use App\Support\ResumeContent;
use Illuminate\Foundation\Http\FormRequest;

class ResumeFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $sections = implode(',', ResumeContent::sections());
        $rules = [
            'title' => ['required', 'string', 'max:160'],
            'summary' => ['nullable', 'string', 'max:4000'],
            'template_id' => ['required', 'integer', 'exists:resume_templates,id'],
            'lock_version' => ['required', 'integer'],
            'status' => ['nullable', 'in:draft,ready'],
            'action' => ['nullable', 'string', 'max:80'],
            'sections_present' => ['required', 'array'],
            'sections_present.*' => ['in:'.$sections],
            'section_order' => ['nullable', 'array'],
            'section_order.*' => ['in:'.$sections],
            'theme.primary' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme.accent' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];

        foreach (array_keys(ResumeContent::emptyPersonal()) as $key) {
            $rules['personal.'.$key] = ['nullable', 'string', 'max:160'];
        }

        foreach (ResumeContent::sections() as $section) {
            $rules[$section] = ['nullable', 'array', 'max:40'];
            $rules[$section.'.*.id'] = ['nullable', 'string', 'max:64'];

            foreach (ResumeContent::fields($section) as $field) {
                $rules[$section.'.*.'.$field['name']] = $field['type'] === 'checkbox'
                    ? ['nullable', 'boolean']
                    : ['nullable', 'string', 'max:'.($field['type'] === 'textarea' ? 4000 : 200)];
            }
        }

        return $rules;
    }
}
