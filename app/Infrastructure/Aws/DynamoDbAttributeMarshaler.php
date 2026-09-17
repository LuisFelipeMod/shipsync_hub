<?php

namespace App\Infrastructure\Aws;

use InvalidArgumentException;

/**
 * Converte itens PHP ↔ atributos DynamoDB (tipos S/N/BOOL/NULL/L/M).
 * Evita depender de {@see \Aws\DynamoDb\Marshaler}, que pode faltar se o SDK não estiver instalado por completo.
 */
final class DynamoDbAttributeMarshaler
{
    /**
     * @param  array<string, mixed>  $item
     * @return array<string, array<string, mixed>>
     */
    public function marshalItem(array $item): array
    {
        $marshaled = [];

        foreach ($item as $key => $value) {
            $marshaled[(string) $key] = $this->marshalValue($value);
        }

        return $marshaled;
    }

    /**
     * @param  array<string, array<string, mixed>>  $item
     * @return array<string, mixed>
     */
    public function unmarshalItem(array $item): array
    {
        $plain = [];

        foreach ($item as $key => $value) {
            $plain[(string) $key] = $this->unmarshalValue($value);
        }

        return $plain;
    }

    private function marshalValue(mixed $value): array
    {
        if ($value === null) {
            return ['NULL' => true];
        }

        if (is_bool($value)) {
            return ['BOOL' => $value];
        }

        if (is_int($value) || is_float($value)) {
            return ['N' => (string) $value];
        }

        if (is_string($value)) {
            return ['S' => $value];
        }

        if (is_list($value)) {
            return ['L' => array_map(fn (mixed $entry): array => $this->marshalValue($entry), $value)];
        }

        if (is_array($value)) {
            return ['M' => $this->marshalItem($value)];
        }

        throw new InvalidArgumentException('Tipo não suportado para DynamoDB: '.get_debug_type($value));
    }

    private function unmarshalValue(array $attribute): mixed
    {
        if (array_key_exists('S', $attribute)) {
            return $attribute['S'];
        }

        if (array_key_exists('N', $attribute)) {
            $number = $attribute['N'];
            if (! is_string($number)) {
                return $number;
            }

            return str_contains($number, '.') ? (float) $number : (int) $number;
        }

        if (array_key_exists('BOOL', $attribute)) {
            return (bool) $attribute['BOOL'];
        }

        if (array_key_exists('NULL', $attribute)) {
            return null;
        }

        if (array_key_exists('L', $attribute)) {
            return array_map(
                fn (array $entry): mixed => $this->unmarshalValue($entry),
                $attribute['L'],
            );
        }

        if (array_key_exists('M', $attribute)) {
            return $this->unmarshalItem($attribute['M']);
        }

        throw new InvalidArgumentException('Atributo DynamoDB desconhecido.');
    }
}
