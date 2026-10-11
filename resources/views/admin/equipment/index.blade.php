@extends('layouts.app')
@section('title', 'Equipment')
@section('content')
<div class="admin-placeholder-page">
    <header class="admin-placeholder-header">
        <div><p class="ticket-page-kicker">ASSET MANAGEMENT</p><h1>Equipment</h1><p>Keep track of ICT equipment, ownership, and service history in one place.</p></div>
        <span class="admin-placeholder-status"><span></span>Inventory setup pending</span>
    </header>
    <section class="admin-placeholder-summary" aria-label="Equipment overview">
        <article class="admin-placeholder-stat"><span class="admin-placeholder-stat-icon is-blue"><svg data-lucide="package" aria-hidden="true"></svg></span><span><small>Total equipment</small><strong>—</strong><em>Not configured</em></span></article>
        <article class="admin-placeholder-stat"><span class="admin-placeholder-stat-icon is-green"><svg data-lucide="circle-check" aria-hidden="true"></svg></span><span><small>In service</small><strong>—</strong><em>Awaiting inventory</em></span></article>
        <article class="admin-placeholder-stat"><span class="admin-placeholder-stat-icon is-amber"><svg data-lucide="settings" aria-hidden="true"></svg></span><span><small>Needs attention</small><strong>—</strong><em>Awaiting inventory</em></span></article>
        <article class="admin-placeholder-stat"><span class="admin-placeholder-stat-icon is-purple"><svg data-lucide="tags" aria-hidden="true"></svg></span><span><small>Categories</small><strong>—</strong><em>Not configured</em></span></article>
    </section>
    <section class="ticket-page-panel admin-placeholder-panel" aria-labelledby="equipment-directory-heading">
        <header class="admin-placeholder-panel-heading"><div><p class="ticket-page-kicker">INVENTORY DIRECTORY</p><h2 id="equipment-directory-heading">Equipment records</h2><p>The equipment source of truth has not been connected yet.</p></div><span class="admin-placeholder-panel-icon"><svg data-lucide="database" aria-hidden="true"></svg></span></header>
        <div class="admin-placeholder-empty"><span class="admin-placeholder-empty-icon"><svg data-lucide="package" aria-hidden="true"></svg></span><h3>No equipment records yet</h3><p>Once CUT ICT confirms the asset source of truth, equipment records, categories, custodians, locations, and maintenance history can be enabled here.</p><div class="admin-placeholder-next"><svg data-lucide="info" aria-hidden="true"></svg><span><strong>What happens next?</strong><small>Connect or approve the institutional inventory workflow before adding records. This avoids creating a second, conflicting asset register.</small></span></div></div>
    </section>
</div>
@endsection
