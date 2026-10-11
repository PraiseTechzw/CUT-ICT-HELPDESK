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
            'panel' => 'Maintenance workspace', 'panel_description' => 'Maintenance records are not connected yet.',
            'empty_title' => 'No maintenance records yet',
            'empty_description' => 'Once the equipment source of truth and maintenance workflow are approved, schedules, work orders, service notes, and completion history can be managed here.',
            'next_step' => 'Confirm equipment ownership, service vendors, maintenance statuses, and the approved maintenance record source.',
        ],
        'asset-categories' => [
            'title' => 'Asset Categories', 'eyebrow' => 'ASSET MANAGEMENT', 'icon' => 'tags',
            'description' => 'Group institutional equipment into consistent, searchable categories.',
            'panel' => 'Asset category directory', 'panel_description' => 'Asset categories are not configured yet.',
            'empty_title' => 'No asset categories yet',
            'empty_description' => 'Approved categories such as computers, printers, networking, and audio visual equipment can be added after the institutional asset model is confirmed.',
            'next_step' => 'Confirm category names, codes, ownership, and whether categories are shared with another inventory system.',
        ],
        'user-reports' => [
            'title' => 'User Reports', 'eyebrow' => 'REPORTING', 'icon' => 'users-round',
            'description' => 'Review account and organizational trends for authorized administration teams.',
            'panel' => 'User reporting workspace', 'panel_description' => 'User reporting definitions are not configured yet.',
            'empty_title' => 'No user reports available',
            'empty_description' => 'This workspace will be enabled after report audiences, privacy rules, date semantics, and approved account metrics are confirmed.',
            'next_step' => 'Confirm report owners, permitted audiences, metrics, privacy thresholds, and export requirements.',
        ],
        'asset-reports' => [
            'title' => 'Asset Reports', 'eyebrow' => 'REPORTING', 'icon' => 'package',
            'description' => 'Understand equipment coverage, status, ownership, and service history.',
            'panel' => 'Asset reporting workspace', 'panel_description' => 'Asset reporting depends on an approved inventory source.',
            'empty_title' => 'No asset reports available',
            'empty_description' => 'Asset reports will appear after the equipment source of truth, identifiers, custodianship, and maintenance data are connected.',
            'next_step' => 'Confirm the authoritative asset register and the reports required by ICT operations.',
        ],
        'system-reports' => [
            'title' => 'System Reports', 'eyebrow' => 'REPORTING', 'icon' => 'chart-no-axes-combined',
            'description' => 'Monitor application health, configuration coverage, and operational readiness.',
            'panel' => 'System reporting workspace', 'panel_description' => 'System reporting is not configured yet.',
            'empty_title' => 'No system reports available',
            'empty_description' => 'This page will combine approved operational signals after monitoring ownership, service targets, and retention rules are defined.',
            'next_step' => 'Confirm system health indicators, monitoring ownership, alert thresholds, and report retention.',
        ],
        'articles' => [
            'title' => 'Articles', 'eyebrow' => 'KNOWLEDGE BASE', 'icon' => 'book-open',
            'description' => 'Publish reliable how-to guidance for common ICT support needs.',
            'panel' => 'Knowledge base articles', 'panel_description' => 'No articles have been published yet.',
            'empty_title' => 'Your knowledge base is ready for content',
            'empty_description' => 'Approved support articles, ownership, review dates, and visibility rules will appear here when the knowledge workflow is enabled.',
            'next_step' => 'Confirm article owners, review cadence, audience, categories, and publishing approval.',
        ],
        'knowledge-categories' => [
            'title' => 'Categories', 'eyebrow' => 'KNOWLEDGE BASE', 'icon' => 'list',
            'description' => 'Organize support guidance into clear, discoverable topics.',
            'panel' => 'Knowledge categories', 'panel_description' => 'Knowledge categories are not configured yet.',
            'empty_title' => 'No knowledge categories yet',
            'empty_description' => 'Categories can be added once the content ownership and navigation model for the knowledge base are approved.',
            'next_step' => 'Confirm the category taxonomy and whether categories are shared with ticket categories.',
        ],
        'faqs' => [
            'title' => 'FAQs', 'eyebrow' => 'KNOWLEDGE BASE', 'icon' => 'info',
            'description' => 'Give requesters quick answers to frequently asked ICT questions.',
            'panel' => 'Frequently asked questions', 'panel_description' => 'No FAQs have been published yet.',
            'empty_title' => 'No FAQs yet',
            'empty_description' => 'Frequently asked questions will be published here after content ownership, review, and audience rules are agreed.',
            'next_step' => 'Confirm FAQ owners, review cadence, audience, and escalation links to the helpdesk.',
        ],
        'system-settings' => [
            'title' => 'System Settings', 'eyebrow' => 'SETTINGS', 'icon' => 'settings',
            'description' => 'Configure approved application behavior and operational defaults.',
            'panel' => 'System configuration', 'panel_description' => 'No editable system settings are exposed yet.',
            'empty_title' => 'System settings are protected',
            'empty_description' => 'Production settings require an approved configuration plan, ownership model, change control, and environment-specific safeguards before they are editable here.',
            'next_step' => 'Confirm which settings belong in the application, who may change them, and how changes are audited.',
        ],
        'email-templates' => [
            'title' => 'Email Templates', 'eyebrow' => 'SETTINGS', 'icon' => 'mail',
            'description' => 'Manage approved email content when external delivery is enabled.',
            'panel' => 'Email template library', 'panel_description' => 'External email delivery is not configured.',
            'empty_title' => 'No email templates yet',
            'empty_description' => 'Email templates will be available after a mail provider, sender identity, delivery policy, privacy review, and template ownership are approved.',
            'next_step' => 'Confirm the mail provider, sender identity, notification events, template owners, and retention rules.',
        ],
    ];

    public function index(string $page): View
    {
        if (! isset(self::PAGES[$page])) {
            throw new NotFoundHttpException;
        }

        return view('admin.placeholder.index', ['page' => self::PAGES[$page]]);
    }
}
