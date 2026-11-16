<?php

namespace App\Imports;

/**
 * Un campo que el importador espera encontrar en el Excel.
 *
 * Los alias sirven para adivinar la columna: otras fincas traerán archivos con
 * encabezados distintos, en español o en inglés.
 */
class ImportField
{
    /**
     * @param  list<string>  $aliases
     */
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly bool $required = false,
        public readonly array $aliases = [],
        public readonly ?string $example = null,
    ) {}

    /**
     * ¿Este encabezado del archivo corresponde al campo?
     */
    public function matches(string $header): bool
    {
        $needle = self::normalize($header);

        if ($needle === '') {
            return false;
        }

        foreach ([$this->key, $this->label, ...$this->aliases] as $candidate) {
            if (self::normalize($candidate) === $needle) {
                return true;
            }
        }

        return false;
    }

    /**
     * Compara sin tildes, sin mayúsculas y sin signos, para que "País" y
     * "pais" sean la misma columna.
     */
    public static function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = strtr($value, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n', 'ü' => 'u',
        ]);

        return trim((string) preg_replace('/[^a-z0-9]+/', ' ', $value));
    }
}
