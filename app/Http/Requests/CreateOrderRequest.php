<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     */
    public function rules(): array
    {
        return [
            'client_name' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    /**
     * Obtiene los mensajes de error para las reglas de validación definidas.
     */
    public function messages(): array
    {
        return [
            'client_name.required' => 'El nombre del cliente es obligatorio.',
            'client_name.string' => 'El nombre del cliente debe ser una cadena de texto.',
            'client_name.max' => 'El nombre del cliente no puede exceder 255 caracteres.',
            'items.required' => 'Debe incluir al menos un item en la orden.',
            'items.array' => 'Los items deben ser un arreglo.',
            'items.min' => 'Debe incluir al menos un item.',
            'items.*.description.required' => 'La descripción del item es obligatoria.',
            'items.*.description.string' => 'La descripción del item debe ser una cadena de texto.',
            'items.*.description.max' => 'La descripción del item no puede exceder 255 caracteres.',
            'items.*.quantity.required' => 'La cantidad es obligatoria.',
            'items.*.quantity.integer' => 'La cantidad debe ser un número entero.',
            'items.*.quantity.min' => 'La cantidad debe ser al menos 1.',
            'items.*.unit_price.required' => 'El precio unitario es obligatorio.',
            'items.*.unit_price.numeric' => 'El precio unitario debe ser un número.',
            'items.*.unit_price.min' => 'El precio unitario debe ser mayor a 0.',
        ];
    }
} 