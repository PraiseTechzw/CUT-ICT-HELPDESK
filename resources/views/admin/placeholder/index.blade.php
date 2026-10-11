@extends('layouts.app')
@section('title', $page['title'])
@section('content')
<div class="admin-data-page">
    <header class="admin-data-header">
        <div>
            <p class="ticket-page-kicker">{{ $page['eyebrow'] }}</p>
            <h1>{{ $page['title'] }}</h1>
            <p>{{ $page['description'] }}</p>
        </div>
        <span class="admin-data-status"><span></span>Ready to configure</span>
    </header>

    <section class="admin-kpi-grid" aria-label="{{ $page['title'] }} summary">
        @foreach($kpis as $kpi)
            <article class="admin-kpi-card">
                <span class="admin-kpi-icon is-{{ $kpi['tone'] }}"><svg data-lucide="{{ $page['icon'] }}" aria-hidden="true"></svg></span>
                <span><small>{{ $kpi['label'] }}</small><strong>{{ $kpi['value'] }}</strong></span>
            </article>
        @endforeach
    </section>

    <section class="ticket-page-panel admin-data-panel" aria-labelledby="workspace-heading">
        <header class="admin-data-panel-heading">
            <div>
                <p class="ticket-page-kicker">{{ $page['eyebrow'] }}</p>
                <h2 id="workspace-heading">{{ $page['panel'] }}</h2>
                <p>{{ $page['panel_description'] }}</p>
            </div>
            <span class="admin-data-panel-icon"><svg data-lucide="{{ $page['icon'] }}" aria-hidden="true"></svg></span>
        </header>

        <div class="admin-data-toolbar" aria-label="Workspace filters">
            <label class="admin-data-search">
                <svg data-lucide="search" aria-hidden="true"></svg>
                <span class="sr-only">Search {{ strtolower($page['title']) }}</span>
                <input type="search" disabled placeholder="Search {{ strtolower($page['title']) }}...">
            </label>
            <div class="admin-data-toolbar-actions">
                <button type="button" class="admin-toolbar-button" disabled><svg data-lucide="sliders-horizontal" aria-hidden="true"></svg>Filters</button>
                <button type="button" class="admin-toolbar-button" disabled><svg data-lucide="download" aria-hidden="true"></svg>Export</button>
            </div>
        </div>

        <div class="admin-data-table-wrap">
            <table class="admin-data-table">
                <thead><tr>@foreach($columns as $column)<th scope="col">{{ $column }}</th>@endforeach</tr></thead>
                <tbody>
                    <tr><td colspan="{{ count($columns) }}">
                        <div class="admin-data-empty">
                            <span class="admin-data-empty-icon"><svg data-lucide="{{ $page['icon'] }}" aria-hidden="true"></svg></span>
                            <h3>{{ $page['empty_title'] }}</h3>
                            <p>{{ $page['empty_description'] }}</p>
                        </div>
                    </td></tr>
                </tbody>
            </table>
        </div>

        <footer class="admin-data-footer">
            <span>Showing 0 records</span>
            <span class="admin-data-footer-note"><svg data-lucide="info" aria-hidden="true"></svg>Data source connection required before records can be added.</span>
        </footer>
    </section>

    <aside class="admin-next-step">
        <span class="admin-next-step-icon"><svg data-lucide="clipboard-check" aria-hidden="true"></svg></span>
        <div><strong>Configuration checklist</strong><p>{{ $page['next_step'] }}</p></div>
        <span class="admin-next-step-badge">Planning</span>
    </aside>
</div>
@endsection
