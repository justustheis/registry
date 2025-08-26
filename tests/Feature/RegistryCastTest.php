<?php

namespace JustusTheis\Registry\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use JustusTheis\Registry\Tests\TestCase;
use JustusTheis\Registry\Facades\Registry;
use JustusTheis\Registry\Tests\Stubs\TestUserFactory;

class RegistryCastTest extends TestCase
{
    #[Test]
    public function it_casts_to_explicit_string_type()
    {
        Registry::set('test.string', 123, false, 'string');

        $value = Registry::get('test.string');

        $this->assertSame('123', $value);
        $this->assertIsString($value);
        $this->assertDatabaseHas('registries', [
            'key'   => 'test.string',
            'value' => 123,
            'type'  => 'string',
        ]);
    }

    #[Test]
    public function it_casts_to_explicit_integer_type()
    {
        Registry::set('test.integer', '456', false, 'int');

        $value = Registry::get('test.integer');

        $this->assertSame(456, $value);
        $this->assertIsInt($value);
        $this->assertDatabaseHas('registries', [
            'key'   => 'test.integer',
            'value' => '456',
            'type'  => 'int',
        ]);
    }

    #[Test]
    public function it_casts_to_explicit_float_type()
    {
        Registry::set('test.float', '12.34', false, 'float');

        $value = Registry::get('test.float');

        $this->assertSame(12.34, $value);
        $this->assertIsFloat($value);
        $this->assertDatabaseHas('registries', [
            'key'   => 'test.float',
            'value' => '12.34',
            'type'  => 'float',
        ]);
    }

    #[Test]
    public function it_casts_to_explicit_boolean_type_with_1_0_as_strings()
    {
        Registry::set('test.bool.true', '1', false, 'bool');
        Registry::set('test.bool.false', '0', false, 'bool');

        $trueValue = Registry::get('test.bool.true');
        $falseValue = Registry::get('test.bool.false');

        $this->assertSame(true, $trueValue);
        $this->assertIsBool($trueValue);
        $this->assertSame(false, $falseValue);
        $this->assertIsBool($falseValue);
    }

    #[Test]
    public function it_casts_to_explicit_boolean_type_with_1_0_as_ints()
    {
        Registry::set('test.bool.true', 1, false, 'bool');
        Registry::set('test.bool.false', 0, false, 'bool');

        $trueValue = Registry::get('test.bool.true');
        $falseValue = Registry::get('test.bool.false');

        $this->assertSame(true, $trueValue);
        $this->assertIsBool($trueValue);
        $this->assertSame(false, $falseValue);
        $this->assertIsBool($falseValue);
    }

    #[Test]
    public function it_casts_to_explicit_boolean_type_with_true_false_as_strings()
    {
        Registry::set('test.bool.true', 'true', false, 'bool');
        Registry::set('test.bool.false', 'false', false, 'bool');

        $trueValue = Registry::get('test.bool.true');
        $falseValue = Registry::get('test.bool.false');

        $this->assertSame(true, $trueValue);
        $this->assertIsBool($trueValue);
        $this->assertSame(false, $falseValue);
        $this->assertIsBool($falseValue);
    }

    #[Test]
    public function it_casts_to_explicit_boolean_type_with_true_false_as_bools()
    {
        Registry::set('test.bool.true', true, false, 'bool');
        Registry::set('test.bool.false', false, false, 'bool');

        $trueValue = Registry::get('test.bool.true');
        $falseValue = Registry::get('test.bool.false');

        $this->assertSame(true, $trueValue);
        $this->assertIsBool($trueValue);
        $this->assertSame(false, $falseValue);
        $this->assertIsBool($falseValue);
    }

    #[Test]
    public function it_casts_to_explicit_boolean_type_with_yes_no()
    {
        Registry::set('test.bool.true', 'yes', false, 'bool');
        Registry::set('test.bool.false', 'no', false, 'bool');

        $trueValue = Registry::get('test.bool.true');
        $falseValue = Registry::get('test.bool.false');

        $this->assertSame(true, $trueValue);
        $this->assertIsBool($trueValue);
        $this->assertSame(false, $falseValue);
        $this->assertIsBool($falseValue);
    }

    #[Test]
    public function it_casts_to_explicit_boolean_type_with_on_off()
    {
        Registry::set('test.bool.true', 'on', false, 'bool');
        Registry::set('test.bool.false', 'off', false, 'bool');

        $trueValue = Registry::get('test.bool.true');
        $falseValue = Registry::get('test.bool.false');

        $this->assertSame(true, $trueValue);
        $this->assertIsBool($trueValue);
        $this->assertSame(false, $falseValue);
        $this->assertIsBool($falseValue);
    }

    #[Test]
    public function it_casts_to_explicit_array_type_with_json_array()
    {
        $jsonArray = '["item1", "item2", "item3"]';
        Registry::set('test.array', $jsonArray, false, 'array');

        $value = Registry::get('test.array');

        $this->assertSame(['item1', 'item2', 'item3'], $value);
        $this->assertIsArray($value);
    }

    #[Test]
    public function it_casts_to_explicit_array_type_with_real_array()
    {
        $array = ['item1', 'item2', 'item3'];
        Registry::set('test.array', $array, false, 'array');

        $value = Registry::get('test.array');

        $this->assertSame(['item1', 'item2', 'item3'], $value);
        $this->assertIsArray($value);
    }

    #[Test]
    public function it_casts_to_explicit_object_type_with_json_object()
    {
        $jsonObject = '{"name": "John", "age": 30}';
        Registry::set('test.object', $jsonObject, false, 'object');

        $value = Registry::get('test.object');

        $this->assertIsObject($value);
        $this->assertSame('John', $value->name);
        $this->assertSame(30, $value->age);
    }

    #[Test]
    public function it_casts_to_explicit_object_type_with_actual_object()
    {
        $object = TestUserFactory::create();
        Registry::set('test.object', $object, false, 'object');

        $value = Registry::get('test.object');

        $this->assertIsObject($value);
        $this->assertSame('Test User', $value->name);
        $this->assertSame(1, $value->id);
    }

    #[Test]
    public function it_casts_to_explicit_null_type()
    {
        Registry::set('test.null', 'any value', false, 'null');

        $value = Registry::get('test.null');

        $this->assertNull($value);
    }

    #[Test]
    public function it_auto_detects_null_values_when_type_is_null()
    {
        config([
            'registry.auto_cast_types' => true,
            'registry.cast_rules'      => [
                'boolean_true_values'  => ['true', 'yes', 'on'],
                'boolean_false_values' => ['false', 'no', 'off'],
                'null_values'          => ['null'],
                'numeric_detection'    => true,
                'strict_boolean_mode'  => true,
            ],
        ]);

        Registry::set('test.auto.null', 'null');

        $value = Registry::get('test.auto.null');

        $this->assertNull($value);
        $this->assertDatabaseHas('registries', [
            'key'   => 'test.auto.null',
            'value' => 'null',
            'type'  => null,
        ]);
    }

    #[Test]
    public function it_auto_detects_boolean_values_when_type_is_null()
    {
        config([
            'registry.auto_cast_types' => true,
            'registry.cast_rules'      => [
                'boolean_true_values'  => ['true', 'yes', 'on'],
                'boolean_false_values' => ['false', 'no', 'off'],
                'null_values'          => ['null'],
                'numeric_detection'    => true,
                'strict_boolean_mode'  => true,
            ],
        ]);

        Registry::set('test.auto.bool.true', 'true');
        Registry::set('test.auto.bool.false', 'false');
        Registry::set('test.auto.bool.yes', 'yes');
        Registry::set('test.auto.bool.no', 'no');
        Registry::set('test.auto.bool.on', 'on');
        Registry::set('test.auto.bool.off', 'off');

        $this->assertSame(true, Registry::get('test.auto.bool.true'));
        $this->assertSame(false, Registry::get('test.auto.bool.false'));
        $this->assertSame(true, Registry::get('test.auto.bool.yes'));
        $this->assertSame(false, Registry::get('test.auto.bool.no'));
        $this->assertSame(true, Registry::get('test.auto.bool.on'));
        $this->assertSame(false, Registry::get('test.auto.bool.off'));
    }

    #[Test]
    public function it_auto_detects_integer_values_when_type_is_null()
    {
        config(['registry.auto_cast_types' => true]);

        Registry::set('test.auto.int.positive', '123');
        Registry::set('test.auto.int.negative', '-456');
        Registry::set('test.auto.int.zero', '0');

        $this->assertSame(123, Registry::get('test.auto.int.positive'));
        $this->assertSame(-456, Registry::get('test.auto.int.negative'));
        $this->assertSame(0, Registry::get('test.auto.int.zero'));
        $this->assertIsInt(Registry::get('test.auto.int.positive'));
    }

    #[Test]
    public function it_auto_detects_float_values_when_type_is_null()
    {
        config(['registry.auto_cast_types' => true]);

        Registry::set('test.auto.float.decimal', '12.34');
        Registry::set('test.auto.float.scientific', '1.23e4');
        Registry::set('test.auto.float.negative', '-5.67');

        $this->assertSame(12.34, Registry::get('test.auto.float.decimal'));
        $this->assertSame(12300.0, Registry::get('test.auto.float.scientific'));
        $this->assertSame(-5.67, Registry::get('test.auto.float.negative'));
        $this->assertIsFloat(Registry::get('test.auto.float.decimal'));
    }

    #[Test]
    public function it_auto_detects_array_values_when_type_is_null()
    {
        config(['registry.auto_cast_types' => true]);

        Registry::set('test.auto.array', ['test one', 'test two']);

        $this->assertSame(['test one', 'test two'], Registry::get('test.auto.array'));
        $this->assertIsArray(Registry::get('test.auto.array'));
    }

    #[Test]
    public function it_auto_detects_object_values_when_type_is_null()
    {
        config(['registry.auto_cast_types' => true]);
        $user = TestUserFactory::create();

        Registry::set('test.auto.object', $user);

        $retrieved = Registry::get('test.auto.object');

        $this->assertIsObject($retrieved);
        $this->assertEquals(1, $retrieved->id);
        $this->assertEquals('Test User', $retrieved->name);
    }

    #[Test]
    public function it_leaves_strings_unchanged_when_no_pattern_matches()
    {
        config(['registry.auto_cast_types' => true]);

        Registry::set('test.auto.string', 'just a regular string');

        $value = Registry::get('test.auto.string');

        $this->assertSame('just a regular string', $value);
        $this->assertIsString($value);
    }

    #[Test]
    public function it_disables_auto_casting_when_config_is_false()
    {
        config(['registry.auto_cast_types' => false]);

        Registry::set('test.no.auto.bool', 'true');
        Registry::set('test.no.auto.int', '123');
        Registry::set('test.no.auto.float', '12.34');
        Registry::set('test.no.auto.null', 'null');

        $this->assertSame('true', Registry::get('test.no.auto.bool'));
        $this->assertSame('123', Registry::get('test.no.auto.int'));
        $this->assertSame('12.34', Registry::get('test.no.auto.float'));
        $this->assertSame('null', Registry::get('test.no.auto.null'));
        $this->assertIsString(Registry::get('test.no.auto.bool'));
    }

    #[Test]
    public function it_still_casts_explicit_types_when_auto_casting_disabled()
    {
        config(['registry.auto_cast_types' => false]);

        Registry::set('test.explicit.when.disabled', '123', false, 'int');

        $value = Registry::get('test.explicit.when.disabled');

        $this->assertSame(123, $value);
        $this->assertIsInt($value);
    }

    #[Test]
    public function it_respects_custom_boolean_values_configuration()
    {
        config([
            'registry.auto_cast_types'                 => true,
            'registry.cast_rules.boolean_true_values'  => ['YES', 'ENABLED'],
            'registry.cast_rules.boolean_false_values' => ['NO', 'DISABLED'],
        ]);

        Registry::set('test.custom.bool.yes', 'YES');
        Registry::set('test.custom.bool.no', 'NO');
        Registry::set('test.custom.bool.enabled', 'ENABLED');
        Registry::set('test.custom.bool.disabled', 'DISABLED');

        $this->assertSame(true, Registry::get('test.custom.bool.yes'));
        $this->assertSame(false, Registry::get('test.custom.bool.no'));
        $this->assertSame(true, Registry::get('test.custom.bool.enabled'));
        $this->assertSame(false, Registry::get('test.custom.bool.disabled'));
    }

    #[Test]
    public function it_respects_custom_null_values_configuration()
    {
        config([
            'registry.auto_cast_types'        => true,
            'registry.cast_rules.null_values' => ['EMPTY', 'VOID'],
        ]);

        Registry::set('test.custom.null.empty', 'EMPTY');
        Registry::set('test.custom.null.void', 'VOID');

        $this->assertNull(Registry::get('test.custom.null.empty'));
        $this->assertNull(Registry::get('test.custom.null.void'));
    }

    #[Test]
    public function it_respects_strict_boolean_mode_configuration()
    {
        config([
            'registry.auto_cast_types'                => true,
            'registry.cast_rules.strict_boolean_mode' => true,
        ]);

        Registry::set('test.strict.one', '1');
        Registry::set('test.strict.zero', '0');

        // In strict mode, '0' and '1' should be treated as numbers
        $this->assertSame(1, Registry::get('test.strict.one'));
        $this->assertSame(0, Registry::get('test.strict.zero'));
        $this->assertIsInt(Registry::get('test.strict.one'));
    }

    #[Test]
    public function it_respects_non_strict_boolean_mode_configuration()
    {
        config([
            'registry.auto_cast_types'                => true,
            'registry.cast_rules.strict_boolean_mode' => false,
        ]);

        Registry::set('test.non.strict.one', '1');
        Registry::set('test.non.strict.zero', '0');

        // In non-strict mode, '0' and '1' should be treated as booleans
        $this->assertSame(true, Registry::get('test.non.strict.one'));
        $this->assertSame(false, Registry::get('test.non.strict.zero'));
        $this->assertIsBool(Registry::get('test.non.strict.one'));
    }

    #[Test]
    public function it_respects_numeric_detection_configuration()
    {
        config([
            'registry.auto_cast_types'              => true,
            'registry.cast_rules.numeric_detection' => false,
        ]);

        Registry::set('test.no.numeric.int', '123');
        Registry::set('test.no.numeric.float', '12.34');

        // With numeric detection off, numbers should remain as strings
        $this->assertSame('123', Registry::get('test.no.numeric.int'));
        $this->assertSame('12.34', Registry::get('test.no.numeric.float'));
        $this->assertIsString(Registry::get('test.no.numeric.int'));
    }

    #[Test]
    public function it_handles_edge_cases_gracefully()
    {
        config(['registry.auto_cast_types' => true]);

        // Empty string
        Registry::set('test.edge.empty', '');
        $this->assertSame('', Registry::get('test.edge.empty'));

        // Whitespace
        Registry::set('test.edge.whitespace', '   ');
        $this->assertSame('   ', Registry::get('test.edge.whitespace'));

        // Case sensitivity
        Registry::set('test.edge.case.true', 'TRUE');
        Registry::set('test.edge.case.false', 'FALSE');
        $this->assertSame(true, Registry::get('test.edge.case.true'));
        $this->assertSame(false, Registry::get('test.edge.case.false'));

        // Invalid JSON for array type
        Registry::set('test.edge.invalid.json', 'not json', false, 'array');
        $value = Registry::get('test.edge.invalid.json');
        $this->assertIsArray($value);
        $this->assertSame(['not json'], $value); // Should cast to array anyway
    }

    #[Test]
    public function it_stores_type_field_correctly_in_database()
    {
        // Explicit type
        Registry::set('test.db.explicit', 'value', false, 'string');
        $this->assertDatabaseHas('registries', [
            'key'  => 'test.db.explicit',
            'type' => 'string',
        ]);

        // Null type (auto-detection)
        Registry::set('test.db.auto', 'value');

        $this->assertDatabaseHas('registries', [
            'key'  => 'test.db.auto',
            'type' => null,
        ]);
    }

    #[Test]
    public function it_preserves_fluent_api_type_setting()
    {
        // Test fluent API still works
        Registry::key('test.fluent.key')->type('integer')->value(123)->set();

        $this->assertDatabaseHas('registries', [
            'key'  => 'test.fluent.key',
            'type' => 'integer',
        ]);
    }
}
