<?php

namespace JustusTheis\Registry\Tests\Stubs;

class NonSerializableTestObject
{
    private string $data;

    public function __construct(string $data = 'some data')
    {
        $this->data = $data;
    }

    public function getData(): string
    {
        return $this->data;
    }

    // Intentionally no __toString() method to make it non-serializable
}
