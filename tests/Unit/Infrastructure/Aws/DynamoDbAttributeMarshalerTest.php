<?php

use App\Infrastructure\Aws\DynamoDbAttributeMarshaler;

describe('DynamoDbAttributeMarshaler', function () {
    it('serializa e desserializa strings', function () {
        $marshaler = new DynamoDbAttributeMarshaler;

        $item = ['pk' => 'QUOTE#1', 'sk' => 'RESULT', 'payload' => '{"ok":true}'];
        $roundTrip = $marshaler->unmarshalItem($marshaler->marshalItem($item));

        expect($roundTrip)->toBe($item);
    });
});
