<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\SaveTicketCatalogueRequest;
use App\Models\TicketCategory;
use App\Models\TicketPriority;
use App\Support\AuditLogger;
use App\Support\TicketStatus;
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

    public function categories(): View
    {
        $categories = TicketCategory::query()->orderBy('sort_order')->orderBy('name');
        if (request()->user()->can('tickets.view_all')) {
            $categories->withCount('tickets');
        }

        return view('admin.ticket-catalogues.categories', [
            'categories' => $categories->get(),
            'canViewTicketCounts' => request()->user()->can('tickets.view_all'),
        ]);
    }

    public function priorities(): View
    {
        $priorities = TicketPriority::query()->orderBy('sort_order')->orderBy('name');
        if (request()->user()->can('tickets.view_all')) {
            $priorities->withCount('tickets');
        }

        return view('admin.ticket-catalogues.priorities', [
            'priorities' => $priorities->get(),
            'canViewTicketCounts' => request()->user()->can('tickets.view_all'),
        ]);
    }

    public function statuses(): View
    {
        return view('admin.ticket-catalogues.statuses', [
            'statuses' => TicketStatus::labels(),
            'descriptions' => TicketStatus::descriptions(),
            'transitions' => TicketStatus::transitions(),
        ]);
    }

    public function storeCategory(SaveTicketCatalogueRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $category = TicketCategory::query()->create($request->validated());
        $auditLogger->record($request->user(), 'ticket.category_created', $category, [
            'fields' => ['name', 'slug', 'description', 'sort_order', 'is_active'],
        ]);

        return back()->with('status', 'Ticket category added.');
    }

    public function updateCategory(SaveTicketCatalogueRequest $request, TicketCategory $category, AuditLogger $auditLogger): RedirectResponse
    {
        $category->update($request->validated());
        $auditLogger->record($request->user(), 'ticket.category_updated', $category, [
            'fields' => ['name', 'slug', 'description', 'sort_order', 'is_active'],
        ]);

        return back()->with('status', 'Ticket category updated.');
    }

    public function storePriority(SaveTicketCatalogueRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $priority = TicketPriority::query()->create($request->validated());
        $auditLogger->record($request->user(), 'ticket.priority_created', $priority, [
            'fields' => ['name', 'slug', 'description', 'color', 'sort_order', 'is_active'],
        ]);

        return back()->with('status', 'Ticket priority added.');
    }

    public function updatePriority(SaveTicketCatalogueRequest $request, TicketPriority $priority, AuditLogger $auditLogger): RedirectResponse
    {
        $priority->update($request->validated());
        $auditLogger->record($request->user(), 'ticket.priority_updated', $priority, [
            'fields' => ['name', 'slug', 'description', 'color', 'sort_order', 'is_active'],
        ]);

        return back()->with('status', 'Ticket priority updated.');
    }
}
