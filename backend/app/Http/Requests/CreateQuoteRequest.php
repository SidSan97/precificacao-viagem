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
            'data_inicio' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:today'],
            'data_fim' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:data_inicio'],
            'viajantes' => ['required', 'array', 'min:1'],
            'viajantes.*.nome' => ['required', 'string', 'max:255'],
            'viajantes.*.data_nascimento' => ['required', 'date', 'date_format:Y-m-d', 'before_or_equal:today'],
            'viajantes.*.adicionais' => ['present', 'array', 'distinct'],
            'viajantes.*.adicionais.*' => ['string', Rule::in(self::ADICIONAIS)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'destino.in' => 'O destino deve ser um dos seguintes: '.implode(', ', self::DESTINOS).'.',
            'data_inicio.required' => 'Informe a data de início da viagem.',
            'data_inicio.date' => 'A data de início da viagem não é válida.',
            'data_inicio.date_format' => 'A data de início da viagem deve ser uma data válida (ex.: 2026-07-10).',
            'data_inicio.after_or_equal' => 'A data de início não pode ser anterior à data atual.',
            'data_fim.required' => 'Informe a data fim da viagem.',
            'data_fim.date' => 'A data fim da viagem não é válida.',
            'data_fim.date_format' => 'A data fim da viagem deve ser uma data válida (ex.: 2026-07-20).',
            'data_fim.after_or_equal' => 'A data fim deve ser igual ou posterior à data início.',
            'viajantes.required' => 'Informe ao menos um viajante.',
            'viajantes.min' => 'Informe ao menos um viajante.',
            'viajantes.*.data_nascimento.required' => 'Informe a data de nascimento do viajante.',
            'viajantes.*.data_nascimento.date' => 'A data de nascimento do viajante não é válida.',
            'viajantes.*.data_nascimento.date_format' => 'A data de nascimento do viajante deve ser uma data válida (ex.: 1990-03-15).',
            'viajantes.*.data_nascimento.before_or_equal' => 'A data de nascimento não pode ser uma data futura.',
            'viajantes.*.adicionais.*.in' => 'Os adicionais permitidos são: '.implode(', ', self::ADICIONAIS).'.',
            'viajantes.*.adicionais.distinct' => 'Não é permitido informar adicionais duplicados.',
        ];
    }
}
