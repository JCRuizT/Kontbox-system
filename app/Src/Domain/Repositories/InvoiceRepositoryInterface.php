<?php

namespace App\Src\Domain\Repositories;

use App\Src\Domain\Entities\Invoice;

interface InvoiceRepositoryInterface
{
    public function findById(int $id): ?Invoice;
    public function save(Invoice $invoice): void;
}
