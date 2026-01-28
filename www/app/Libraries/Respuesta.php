<?php

declare(strict_types=1);

namespace Com\Daw2\Libraries;

class Respuesta
{
    private int $status;
    private ?array $data;

    public function __construct(int $status)
    {
        if ($status >= 200 || $status < 600) {
            $this->status = $status;
            $this->data = null;
        } else {
            throw new \Exception("Status no válido");
        }
    }

    public function setData(?array $data)
    {
        $this->data = $data;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function hasData(): bool
    {
        return !is_null($this->data);
    }
}
