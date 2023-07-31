<?php

namespace Basilicom\ImportDataValidator\Validator\Result;

abstract class AbstractValidationError
{
    private ?int $lineNumber;
    private string $info;

    public function __construct(?int $lineNumber, string $info)
    {
        $this->lineNumber = $lineNumber;
        $this->info = $info;
    }

    /**
     * @return int|null
     */
    public function getLineNumber(): ?int
    {
        return $this->lineNumber;
    }

    /**
     * @return string
     */
    public function getInfo(): string
    {
        return $this->info;
    }
}
