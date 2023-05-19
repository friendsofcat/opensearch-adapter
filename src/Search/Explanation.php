<?php declare(strict_types=1);

namespace OpenSearch\Adapter\Search;

use ArrayAccess;
use Illuminate\Support\Collection;

final class Explanation implements ArrayAccess
{
    use RawResult;

    public function value(): float
    {
        return $this->rawResult['value'] ?? 0;
    }

    public function description(): string
    {
        return $this->rawResult['description'];
    }

    /**
     * @return Collection<self>
     */
    public function details(): Collection
    {
        return collect($this->rawResult['details'] ?? [])->mapInto(self::class);
    }
}
