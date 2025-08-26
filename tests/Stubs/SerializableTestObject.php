<?php

namespace JustusTheis\Registry\Tests\Stubs;

class SerializableTestObject
{
    private string $value;

    public function __construct(string $value = 'serializable value')
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
