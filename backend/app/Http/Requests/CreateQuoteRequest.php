<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateQuoteRequest extends FormRequest
{
    public const DESTINOS = ['NACIONAL', 'AMERICAS', 'EUROPA'];

    public const ADICIONAIS = ['BAGAGEM', 'ESPORTES_AVENTURA'];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'destino' => ['required', 'string', Rule::in(self::DESTINOS)],
            'data_inicio' => ['required', 'date', 'date_format:Y-m-d'],
            'data_fim' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:data_inicio'],
            'viajantes' => ['required', 'array', 'min:1'],
            'viajantes.*.nome' => ['required', 'string', 'max:255'],
            'viajantes.*.data_nascimento' => ['required', 'date', 'date_format:Y-m-d', 'before_or_equal:today'],
            'viajantes.*.adicionais' => ['present', 'array'],
            'viajantes.*.adicionais.*' => ['string', 'distinct', Rule::in(self::ADICIONAIS)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'destino.in' => 'O destino deve ser um dos seguintes: '.implode(', ', self::DESTINOS).'.',
            'data_fim.after_or_equal' => 'A data fim deve ser igual ou posterior à data início.',
            'viajantes.required' => 'Informe ao menos um viajante.',
            'viajantes.min' => 'Informe ao menos um viajante.',
            'viajantes.*.adicionais.*.in' => 'Os adicionais permitidos são: '.implode(', ', self::ADICIONAIS).'.',
            'viajantes.*.adicionais.*.distinct' => 'Não é permitido informar adicionais duplicados.',
        ];
    }
}
