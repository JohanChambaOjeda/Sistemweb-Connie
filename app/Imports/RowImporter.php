<?php

namespace App\Imports;

use Illuminate\Support\Facades\Validator;

/**
 * Contrato de los importadores: qué campos espera, cómo se validan y cómo se
 * guarda una fila. El flujo (subida, mapeo, vista previa, confirmación) es el
 * mismo para todos.
 */
abstract class RowImporter
{
    /**
     * Clave del importador, por ejemplo "customers".
     */
    abstract public function type(): string;

    /**
     * @return list<ImportField>
     */
    abstract public function fields(): array;

    /**
     * Reglas de validación por clave de campo.
     *
     * @return array<string, mixed>
     */
    abstract public function rules(): array;

    /**
     * Guarda una fila ya validada.
     *
     * @param  array<string, mixed>  $row
     */
    abstract public function import(array $row): void;

    public function label(): string
    {
        return __('imports.types.'.$this->type());
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Nombres de los campos para los mensajes de error.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $attributes = [];

        foreach ($this->fields() as $field) {
            $attributes[$field->key] = $field->label;
        }

        return $attributes;
    }

    /**
     * Normaliza una fila antes de validarla (limpia espacios, convierte sí/no).
     *
     * @param  array<string, string|null>  $row
     * @return array<string, mixed>
     */
    public function prepare(array $row): array
    {
        return array_map(
            fn (?string $value) => $value === null ? null : (trim($value) === '' ? null : trim($value)),
            $row,
        );
    }

    /**
     * Errores de una fila, con el nombre del campo ya traducido.
     *
     * @param  array<string, mixed>  $row
     * @return list<string>
     */
    public function errorsFor(array $row): array
    {
        $validator = Validator::make($row, $this->rules(), $this->messages(), $this->attributes());

        return array_values(array_map(
            fn (array $messages) => (string) ($messages[0] ?? ''),
            $validator->errors()->toArray(),
        ));
    }

    /**
     * Interpreta los valores que la gente escribe para un sí/no.
     */
    public static function toBoolean(mixed $value, bool $default = true): bool
    {
        if ($value === null || $value === '') {
            return $default;
        }

        $normalized = mb_strtolower(trim((string) $value));

        return in_array($normalized, ['1', 'si', 'sí', 'yes', 'true', 'activo', 'active', 'x'], true);
    }
}
