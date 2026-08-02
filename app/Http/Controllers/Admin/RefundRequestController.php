<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RefundRequest;
use App\Services\AdminNotificationService;
use App\Services\UserNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RefundRequestController extends Controller
{
    public function index(Request $request): View
    {
        $status = strtoupper((string) $request->query('status', ''));

        $requests = RefundRequest::query()
            ->with(['user', 'order'])
            ->when(in_array($status, $this->statuses(), true), fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.refund-requests.index', [
            'refundRequests' => $requests,
            'statuses' => $this->statuses(),
            'currentStatus' => $status,
        ]);
    }

    public function update(Request $request, RefundRequest $refundRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:'.implode(',', $this->statuses())],
            'admin_note' => ['nullable', 'string', 'max:3000'],
        ]);

        $previousStatus = (string) $refundRequest->status;

        $refundRequest->update([
            'status' => $validated['status'],
            'admin_note' => $validated['admin_note'] ?? null,
            'resolved_at' => in_array($validated['status'], [RefundRequest::STATUS_APPROVED, RefundRequest::STATUS_REJECTED], true) ? now() : null,
            'resolved_by' => in_array($validated['status'], [RefundRequest::STATUS_APPROVED, RefundRequest::STATUS_REJECTED], true) ? $request->user()?->id : null,
        ]);

        AdminNotificationService::create(
            'refund_request_updated',
            'Refund request #'.$refundRequest->id.' updated',
            'Status changed to '.$validated['status'].'.',
            route('admin.refund_requests.index'),
            'info',
            $refundRequest
        );

        if ($previousStatus !== $validated['status'] || !empty($validated['admin_note'] ?? null)) {
            UserNotificationService::create(
                $refundRequest->user_id,
                'refund_request_updated',
                'Refund request #'.$refundRequest->id.' updated',
                trim('Status changed to '.$validated['status'].'. '.($validated['admin_note'] ?? '')),
                route('refunds.index'),
                match ($validated['status']) {
                    RefundRequest::STATUS_APPROVED => 'success',
                    RefundRequest::STATUS_REJECTED => 'warning',
                    default => 'info',
                },
                $refundRequest
            );
        }

        return back()->with('success', 'Refund request updated.');
    }

    private function statuses(): array
    {
        return [
            RefundRequest::STATUS_PENDING,
            RefundRequest::STATUS_REVIEWING,
            RefundRequest::STATUS_APPROVED,
            RefundRequest::STATUS_REJECTED,
        ];
    }
}
