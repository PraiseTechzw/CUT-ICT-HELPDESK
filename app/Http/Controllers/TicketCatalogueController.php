<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\SaveTicketCatalogueRequest;
use App\Models\TicketCategory;
use App\Models\TicketPriority;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TicketCatalogueController extends Controller
{
    public function index(): View
    {
        return view('admin.ticket-catalogues.index', [
            'categories' => TicketCategory::query()->orderBy('sort_order')->orderBy('name')->get(),
            'priorities' => TicketPriority::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function storeCategory(SaveTicketCatalogueRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $category = TicketCategory::query()->create($request->validated());
        $auditLogger->record($request->user(), 'ticket.category_created', $category, [
            'fields' => ['name', 'slug', 'sort_order', 'is_active'],
        ]);

        return back()->with('status', 'Ticket category added.');
    }

    public function updateCategory(SaveTicketCatalogueRequest $request, TicketCategory $category, AuditLogger $auditLogger): RedirectResponse
    {
        $category->update($request->validated());
        $auditLogger->record($request->user(), 'ticket.category_updated', $category, [
            'fields' => ['name', 'slug', 'sort_order', 'is_active'],
        ]);

        return back()->with('status', 'Ticket category updated.');
    }

    public function storePriority(SaveTicketCatalogueRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $priority = TicketPriority::query()->create($request->validated());
        $auditLogger->record($request->user(), 'ticket.priority_created', $priority, [
            'fields' => ['name', 'slug', 'sort_order', 'is_active'],
        ]);

        return back()->with('status', 'Ticket priority added.');
    }

    public function updatePriority(SaveTicketCatalogueRequest $request, TicketPriority $priority, AuditLogger $auditLogger): RedirectResponse
    {
        $priority->update($request->validated());
        $auditLogger->record($request->user(), 'ticket.priority_updated', $priority, [
            'fields' => ['name', 'slug', 'sort_order', 'is_active'],
        ]);

        return back()->with('status', 'Ticket priority updated.');
    }
}
