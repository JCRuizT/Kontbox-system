<?php

namespace App\Src\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Src\Domain\Services\AuditService;
use App\Src\Infrastructure\Persistence\Models\Contract;
use App\Src\Infrastructure\Persistence\Models\ContractAmendment;
use Illuminate\Support\Facades\Storage;

class PdfController extends Controller
{
    public function contract(Contract $contract)
    {
        if (!$contract->signed_pdf_path || !Storage::disk('local')->exists($contract->signed_pdf_path)) {
            return back()->with('error', __('domain.contract.pdf_not_available'));
        }

        AuditService::log(
            __('domain.audit_log.view_pdf_contract', ['number' => $contract->contract_number]),
            $contract,
            ['action' => 'view_pdf'],
            AuditService::BUSINESS
        );

        return response()->file(storage_path('app/' . $contract->signed_pdf_path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline',
        ]);
    }

    public function amendment(ContractAmendment $amendment)
    {
        if (!$amendment->signed_pdf_path || !Storage::disk('local')->exists($amendment->signed_pdf_path)) {
            return back()->with('error', __('domain.contract.pdf_not_available'));
        }

        AuditService::log(
            __('domain.audit_log.view_pdf_amendment', ['number' => $amendment->amendment_number]),
            $amendment,
            ['action' => 'view_pdf'],
            AuditService::BUSINESS
        );

        return response()->file(storage_path('app/' . $amendment->signed_pdf_path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline',
        ]);
    }
}
