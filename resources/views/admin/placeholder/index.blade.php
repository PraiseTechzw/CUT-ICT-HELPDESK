@extends('layouts.app')
@section('title', $page['title'])
@section('content')
<div class="admin-placeholder-page">
    <header class="admin-placeholder-header">
        <div><p class="ticket-page-kicker">{{ $page['eyebrow'] }}</p><h1>{{ $page['title'] }}</h1><p>{{ $page['description'] }}</p></div>
        <span class="admin-placeholder-status"><span></span>Setup pending</span>
    </header>
    <section class="ticket-page-panel admin-placeholder-panel" aria-labelledby="placeholder-panel-heading">
        <header class="admin-placeholder-panel-heading"><div><p class="ticket-page-kicker">{{ $page['eyebrow'] }}</p><h2 id="placeholder-panel-heading">{{ $page['panel'] }}</h2><p>{{ $page['panel_description'] }}</p></div><span class="admin-placeholder-panel-icon"><svg data-lucide="{{ $page['icon'] }}" aria-hidden="true"></svg></span></header>
        <div class="admin-placeholder-empty"><span class="admin-placeholder-empty-icon"><svg data-lucide="{{ $page['icon'] }}" aria-hidden="true"></svg></span><h3>{{ $page['empty_title'] }}</h3><p>{{ $page['empty_description'] }}</p><div class="admin-placeholder-next"><svg data-lucide="info" aria-hidden="true"></svg><span><strong>What happens next?</strong><small>{{ $page['next_step'] }}</small></span></div></div>
    </section>
</div>
@endsection
