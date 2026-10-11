<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdminPlaceholderController extends Controller
{
    /** @var array<string, array<string, string>> */
    private const PAGES = [
        'maintenance' => [
            'title' => 'Maintenance', 'eyebrow' => 'ASSET MANAGEMENT', 'icon' => 'settings',
            'description' => 'Plan, record, and review maintenance work for institutional equipment.',
            'panel' => 'Maintenance workspace', 'panel_description' => 'Track planned work, service history, and equipment health from one view.',
            'empty_title' => 'No maintenance work has been scheduled',
            'empty_description' => 'Once equipment records are connected, this workspace will keep upcoming service, overdue work, and completed maintenance visible to the ICT team.',
            'next_step' => 'Connect the approved equipment register, then define service statuses, owners, vendors, and due-date rules.',
        ],
        'asset-categories' => [
            'title' => 'Asset Categories', 'eyebrow' => 'ASSET MANAGEMENT', 'icon' => 'tags',
            'description' => 'Group institutional equipment into consistent, searchable categories.',
            'panel' => 'Asset category directory', 'panel_description' => 'Keep equipment grouped by the language used by ICT operations.',
            'empty_title' => 'No asset categories have been defined',
            'empty_description' => 'Approved categories such as computers, printers, networking, and audiovisual equipment will appear here when the inventory model is connected.',
            'next_step' => 'Confirm category names, codes, ownership, and whether categories are shared with another inventory system.',
        ],
        'user-reports' => [
            'title' => 'User Reports', 'eyebrow' => 'REPORTING', 'icon' => 'users-round',
            'description' => 'Review account and organizational trends for authorized administration teams.',
            'panel' => 'User reporting workspace', 'panel_description' => 'A focused view of account activity, roles, and service participation.',
            'empty_title' => 'No user reporting data is available',
            'empty_description' => 'Report cards and downloadable results will appear when the approved account metrics and privacy rules are connected.',
            'next_step' => 'Confirm report owners, permitted audiences, metrics, privacy thresholds, and export requirements.',
        ],
        'asset-reports' => [
            'title' => 'Asset Reports', 'eyebrow' => 'REPORTING', 'icon' => 'package',
            'description' => 'Understand equipment coverage, status, ownership, and service history.',
            'panel' => 'Asset reporting workspace', 'panel_description' => 'Surface inventory coverage, lifecycle status, and service trends.',
            'empty_title' => 'No asset reporting data is available',
            'empty_description' => 'Asset reports will appear after the equipment source of truth, identifiers, custodianship, and maintenance data are connected.',
            'next_step' => 'Confirm the authoritative asset register and the reports required by ICT operations.',
        ],
        'system-reports' => [
            'title' => 'System Reports', 'eyebrow' => 'REPORTING', 'icon' => 'chart-no-axes-combined',
            'description' => 'Monitor application health, configuration coverage, and operational readiness.',
            'panel' => 'System reporting workspace', 'panel_description' => 'See the operational signals that help ICT leaders keep the service reliable.',
            'empty_title' => 'No system reporting data is available',
            'empty_description' => 'This page will combine approved operational signals after monitoring ownership, service targets, and retention rules are defined.',
            'next_step' => 'Confirm system health indicators, monitoring ownership, alert thresholds, and report retention.',
        ],
        'articles' => [
            'title' => 'Articles', 'eyebrow' => 'KNOWLEDGE BASE', 'icon' => 'book-open',
            'description' => 'Publish reliable how-to guidance for common ICT support needs.',
            'panel' => 'Knowledge base articles', 'panel_description' => 'Draft, review, and publish support guidance with clear ownership.',
            'empty_title' => 'Your knowledge base is ready for content',
            'empty_description' => 'Approved support articles, owners, review dates, and visibility rules will appear here when the publishing workflow is enabled.',
            'next_step' => 'Confirm article owners, review cadence, audience, categories, and publishing approval.',
        ],
        'knowledge-categories' => [
            'title' => 'Categories', 'eyebrow' => 'KNOWLEDGE BASE', 'icon' => 'list',
            'description' => 'Organize support guidance into clear, discoverable topics.',
            'panel' => 'Knowledge categories', 'panel_description' => 'Make articles easy to browse with a clear content taxonomy.',
            'empty_title' => 'No knowledge categories have been defined',
            'empty_description' => 'Categories can be added once the content ownership and navigation model for the knowledge base are approved.',
            'next_step' => 'Confirm the category taxonomy and whether categories are shared with ticket categories.',
        ],
        'faqs' => [
            'title' => 'FAQs', 'eyebrow' => 'KNOWLEDGE BASE', 'icon' => 'info',
            'description' => 'Give requesters quick answers to frequently asked ICT questions.',
            'panel' => 'Frequently asked questions', 'panel_description' => 'Keep common answers short, current, and easy for requesters to find.',
            'empty_title' => 'No FAQs have been published',
            'empty_description' => 'Frequently asked questions will be published here after content ownership, review, and audience rules are agreed.',
            'next_step' => 'Confirm FAQ owners, review cadence, audience, and escalation links to the helpdesk.',
        ],
        'system-settings' => [
            'title' => 'System Settings', 'eyebrow' => 'SETTINGS', 'icon' => 'settings',
            'description' => 'Configure approved application behavior and operational defaults.',
            'panel' => 'System configuration', 'panel_description' => 'Review application defaults in one controlled settings workspace.',
            'empty_title' => 'No editable settings are configured',
            'empty_description' => 'Production settings require an approved configuration plan, ownership model, change control, and environment safeguards before they are editable here.',
            'next_step' => 'Confirm which settings belong in the application, who may change them, and how changes are audited.',
        ],
        'email-templates' => [
            'title' => 'Email Templates', 'eyebrow' => 'SETTINGS', 'icon' => 'mail',
            'description' => 'Manage approved email content when external delivery is enabled.',
            'panel' => 'Email template library', 'panel_description' => 'Keep notification copy consistent, reviewable, and ready for delivery.',
            'empty_title' => 'No email templates have been configured',
            'empty_description' => 'Email templates will be available after a mail provider, sender identity, delivery policy, privacy review, and ownership are approved.',
            'next_step' => 'Confirm the mail provider, sender identity, notification events, template owners, and retention rules.',
        ],
    ];

    public function index(string $page): View
    {
        if (! isset(self::PAGES[$page])) {
            throw new NotFoundHttpException;
        }

        $group = match (true) {
            str_contains($page, 'report') => 'reports',
            in_array($page, ['articles', 'knowledge-categories', 'faqs'], true) => 'knowledge',
            in_array($page, ['system-settings', 'email-templates'], true) => 'settings',
            default => 'assets',
        };

        $columns = match ($group) {
            'reports' => ['Report', 'Coverage', 'Last refreshed', 'Status', 'Action'],
            'knowledge' => ['Title', 'Category', 'Owner', 'Review date', 'Status'],
            'settings' => ['Setting', 'Area', 'Last changed', 'Owner', 'Status'],
            default => ['Record', 'Category', 'Owner', 'Last updated', 'Status'],
        };

        $kpis = match ($group) {
            'reports' => [['label' => 'Available reports', 'value' => '0', 'tone' => 'blue'], ['label' => 'Data sources', 'value' => '0', 'tone' => 'green'], ['label' => 'Last refresh', 'value' => '—', 'tone' => 'amber'], ['label' => 'Exports ready', 'value' => '0', 'tone' => 'purple']],
            'knowledge' => [['label' => 'Published', 'value' => '0', 'tone' => 'blue'], ['label' => 'Drafts', 'value' => '0', 'tone' => 'amber'], ['label' => 'Needs review', 'value' => '0', 'tone' => 'red'], ['label' => 'Categories', 'value' => '0', 'tone' => 'green']],
            'settings' => [['label' => 'Configured', 'value' => '0', 'tone' => 'blue'], ['label' => 'Needs review', 'value' => '0', 'tone' => 'amber'], ['label' => 'Protected', 'value' => '0', 'tone' => 'purple'], ['label' => 'Owners', 'value' => '0', 'tone' => 'green']],
            default => [['label' => 'Total records', 'value' => '0', 'tone' => 'blue'], ['label' => 'Active', 'value' => '0', 'tone' => 'green'], ['label' => 'Needs attention', 'value' => '0', 'tone' => 'amber'], ['label' => 'Categories', 'value' => '0', 'tone' => 'purple']],
        };

        return view('admin.placeholder.index', [
            'page' => self::PAGES[$page],
            'group' => $group,
            'columns' => $columns,
            'kpis' => $kpis,
        ]);
    }
}
