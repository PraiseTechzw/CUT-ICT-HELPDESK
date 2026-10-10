<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\TicketCategory;
use App\Models\TicketPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SaveTicketCatalogueRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $name = $this->input('name');
        $slug = $this->input('slug');

        if (is_string($name)) {
            $this->merge(['slug' => Str::slug(is_string($slug) && $slug !== '' ? $slug : $name)]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()?->can('tickets.catalogues.manage') ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $category = $this->route('category');
        $priority = $this->route('priority');
        $table = $category instanceof TicketCategory ? 'ticket_categories' : 'ticket_priorities';
        $record = $category instanceof TicketCategory ? $category : ($priority instanceof TicketPriority ? $priority : null);

        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'alpha_dash', 'max:100', Rule::unique($table, 'slug')->ignore($record?->id)],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
