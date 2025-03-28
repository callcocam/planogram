<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace Callcocam\Planogram\Http\Requests\Planogram;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateRequest
 * 
 * Classe de validação para requisições de atualização de registros Planogram.
 * Define regras de validação e mensagens personalizadas.
 */
class UpdatePlanogramRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer esta requisição.
     * 
     * @return bool Retorna true se autorizado, false caso contrário
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Define as regras de validação aplicáveis à requisição.
     * 
     * Note que para atualização, a regra 'sometimes' é aplicada a campos que
     * podem estar ausentes na requisição, sendo validados apenas se presentes.
     * 
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|required|string',
            'gondola' => 'sometimes|required|array',
            // Adicione mais regras de validação conforme necessário
        ];
    }
    
    /**
     * Define mensagens personalizadas para erros de validação.
     * 
     * @return array Mensagens personalizadas
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório',
            'name.max' => 'O nome não pode ter mais de :max caracteres',
            'status.required' => 'O status é obrigatório',
            // Adicione mais mensagens personalizadas conforme necessário
        ];
    }
    
    /**
     * Opcionalmente, você pode preparar os dados antes da validação
     * sobrecarregando o método prepareForValidation() aqui
     * 
     * protected function prepareForValidation(): void
     * {
     *     $this->merge([
     *         'field' => transform_field($this->field),
     *     ]);
     * }
     */
}
