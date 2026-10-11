@extends('layouts.app')
@section('title', 'Departments')
@section('content')
<div class="admin-placeholder-page">
    <header class="admin-placeholder-header">
        <div><p class="ticket-page-kicker">ORGANIZATION MANAGEMENT</p><h1>Departments</h1><p>Organize staff and service ownership around approved institutional departments.</p></div>
        <span class="admin-placeholder-status"><span></span>Directory setup pending</span>
    </header>
    <section class="admin-placeholder-summary" aria-label="Department overview">
        <article class="admin-placeholder-stat"><span class="admin-placeholder-stat-icon is-blue"><svg data-lucide="building-2" aria-hidden="true"></svg></span><span><small>Total departments</small><strong>—</strong><em>Not configured</em></span></article>
        <article class="admin-placeholder-stat"><span class="admin-placeholder-stat-icon is-green"><svg data-lucide="users-round" aria-hidden="true"></svg></span><span><small>Assigned users</small><strong>—</strong><em>Awaiting directory</em></span></article>
        <article class="admin-placeholder-stat"><span class="admin-placeholder-stat-icon is-amber"><svg data-lucide="network" aria-hidden="true"></svg></span><span><small>Service owners</small><strong>—</strong><em>Awaiting ownership</em></span></article>
        <article class="admin-placeholder-stat"><span class="admin-placeholder-stat-icon is-purple"><svg data-lucide="shield-check" aria-hidden="true"></svg></span><span><small>Access rules</small><strong>—</strong><em>Not configured</em></span></article>
    </section>
    <section class="ticket-page-panel admin-placeholder-panel" aria-labelledby="department-directory-heading">
        <header class="admin-placeholder-panel-heading"><div><p class="ticket-page-kicker">DEPARTMENT DIRECTORY</p><h2 id="department-directory-heading">Department records</h2><p>The institutional department structure has not been confirmed yet.</p></div><span class="admin-placeholder-panel-icon"><svg data-lucide="building-2" aria-hidden="true"></svg></span></header>
        <div class="admin-placeholder-empty"><span class="admin-placeholder-empty-icon is-green"><svg data-lucide="building-2" aria-hidden="true"></svg></span><h3>No departments configured yet</h3><p>After CUT ICT confirms the approved department list and ownership model, this page can support department records, user assignments, and service responsibility.</p><div class="admin-placeholder-next is-green"><svg data-lucide="info" aria-hidden="true"></svg><span><strong>What happens next?</strong><small>Confirm department names, codes, owners, and whether departments should affect ticket routing or only provide organizational context.</small></span></div></div>
    </section>
</div>
@endsection
