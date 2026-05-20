<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\Facades\Blade;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            // ── Brand identity ──────────────────────────────────────────
            ->brandName('')
            ->spa()
            ->darkMode(false)
            ->colors([
                'primary' => Color::hex('#b84c65'),
                'gray'    => Color::Zinc,
            ])
            // ── Logo di sidebar ────────────────────────────────────────
            ->renderHook(
                PanelsRenderHook::SIDEBAR_NAV_START,
                fn () => Blade::render('
                    <div style="padding:12px 16px 10px;display:flex;align-items:center;gap:10px;border-bottom:1px solid rgba(255,255,255,0.15);margin-bottom:6px;">
                        <div style="width:42px;height:42px;border-radius:50%;background:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.18);">
                            <img src="/images/KaShoes.png" style="width:100%;height:100%;object-fit:cover;" alt="KaShoes">
                        </div>
                        <div style="line-height:1.25;">
                            <div style="color:#fff;font-weight:700;font-size:14px;letter-spacing:.3px;">KaShoes</div>
                            <div style="color:rgba(255,255,255,0.5);font-size:9.5px;letter-spacing:.1em;text-transform:uppercase;">Management</div>
                        </div>
                    </div>
                '),
            )
            // ── Design Tokens + Brand CSS ──────────────────────────────
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => Blade::render('
                    <style>
                    /* ══════════════════════════════════════════════════════
                       KASHOES DESIGN TOKENS
                    ══════════════════════════════════════════════════════ */
                    :root {
                        /* Primary */
                        --ks-primary:        #b84c65;
                        --ks-primary-light:  #d4768a;
                        --ks-primary-dark:   #8c3349;
                        --ks-primary-muted:  #f5e6ea;

                        /* Base – Light Mode */
                        --ks-bg:             #faf6f3;
                        --ks-surface:        #ffffff;
                        --ks-surface-alt:    #f0ebe6;
                        --ks-border:         #e2d8d2;
                        --ks-text:           #1e1414;
                        --ks-text-secondary: #6b5050;
                        --ks-text-muted:     #a08888;

                        /* Semantic */
                        --ks-success:        #4a8c6f;
                        --ks-success-bg:     #e6f4ee;
                        --ks-warning:        #c4813a;
                        --ks-warning-bg:     #fef3e2;
                        --ks-error:          #c0392b;
                        --ks-error-bg:       #fde8e8;
                        --ks-info:           #4a6e8c;
                        --ks-info-bg:        #e6eff6;

                        /* Spacing */
                        --ks-space-1: 4px;  --ks-space-2: 8px;
                        --ks-space-3: 12px; --ks-space-4: 16px;
                        --ks-space-6: 24px; --ks-space-8: 32px;

                        /* Radius */
                        --ks-radius-sm:  4px;
                        --ks-radius-md:  8px;
                        --ks-radius-lg:  12px;
                        --ks-radius-xl:  16px;
                        --ks-radius-full: 9999px;

                        /* Shadows */
                        --ks-shadow-sm:      0 1px 3px rgba(30,20,20,.08);
                        --ks-shadow-md:      0 4px 12px rgba(30,20,20,.10);
                        --ks-shadow-lg:      0 8px 24px rgba(30,20,20,.12);
                        --ks-shadow-brand:   0 4px 16px rgba(184,76,101,.22);

                        /* Transitions */
                        --ks-ease-fast:  150ms ease;
                        --ks-ease-base:  250ms ease;
                    }

                    /* ══════════════════════════════════════════════════════
                       GLOBAL LAYOUT
                    ══════════════════════════════════════════════════════ */
                    body, html {
                        background-color: var(--ks-bg) !important;
                    }
                    .fi-main, .fi-body {
                        background-color: var(--ks-bg) !important;
                    }

                    /* ══════════════════════════════════════════════════════
                       SIDEBAR
                    ══════════════════════════════════════════════════════ */
                    .fi-sidebar,
                    .fi-sidebar-nav,
                    aside.fi-sidebar {
                        background: linear-gradient(175deg, var(--ks-primary) 0%, var(--ks-primary-dark) 100%) !important;
                    }
                    /* ── Semua item sidebar: force putih ── */
                    .fi-sidebar-item-button,
                    .fi-sidebar-item-button span,
                    .fi-sidebar-item-button span span {
                        color: rgba(255,255,255,0.90) !important;
                        border-radius: var(--ks-radius-md) !important;
                        transition: background var(--ks-ease-fast), color var(--ks-ease-fast) !important;
                        font-weight: 500 !important;
                    }

                    /* ── Semua SVG/icon di sidebar: force putih ── */
                    .fi-sidebar svg,
                    .fi-sidebar-item-button svg,
                    .fi-sidebar-item-button * svg,
                    .fi-sidebar-item-icon svg {
                        color: rgba(255,255,255,0.88) !important;
                        stroke: rgba(255,255,255,0.88) !important;
                        fill: none !important;
                        opacity: 1 !important;
                    }

                    /* ── Hover ── */
                    .fi-sidebar-item-button:hover,
                    .fi-sidebar-item-button:hover span {
                        background: rgba(255,255,255,0.15) !important;
                        color: #fff !important;
                    }
                    .fi-sidebar-item-button:hover svg,
                    .fi-sidebar-item-button:hover * svg {
                        color: #fff !important;
                        stroke: #fff !important;
                    }

                    /* ── AKTIF: override total semua default Filament ── */
                    .fi-sidebar-item-button[aria-current="page"],
                    .fi-sidebar-item-button.fi-active {
                        background: rgba(255,255,255,0.20) !important;
                        box-shadow: inset 0 0 0 1.5px rgba(255,255,255,0.30) !important;
                    }
                    /* Semua child element item aktif: paksa putih */
                    .fi-sidebar-item-button[aria-current="page"],
                    .fi-sidebar-item-button[aria-current="page"] *,
                    .fi-sidebar-item-button.fi-active,
                    .fi-sidebar-item-button.fi-active * {
                        color: #ffffff !important;
                        stroke: #ffffff !important;
                        font-weight: 700 !important;
                    }
                    /* SVG aktif spesifik */
                    .fi-sidebar-item-button[aria-current="page"] svg,
                    .fi-sidebar-item-button.fi-active svg {
                        color: #ffffff !important;
                        stroke: #ffffff !important;
                        fill: none !important;
                        opacity: 1 !important;
                    }

                    /* ── Badge ── */
                    .fi-sidebar-item-badge,
                    .fi-sidebar-item-badge * {
                        background: rgba(255,255,255,0.25) !important;
                        color: #fff !important;
                        font-weight: 600 !important;
                    }

                    /* ── Group label ── */
                    .fi-sidebar-group-label {
                        color: rgba(255,255,255,0.50) !important;
                        font-size: 0.6rem !important;
                        letter-spacing: .12em !important;
                        text-transform: uppercase !important;
                        font-weight: 700 !important;
                    }

                    /* ── Scrollbar ── */
                    .fi-sidebar ::-webkit-scrollbar { width: 3px; }
                    .fi-sidebar ::-webkit-scrollbar-thumb {
                        background: rgba(255,255,255,.25);
                        border-radius: var(--ks-radius-sm);
                    }

                    /* Hide brand logo in topbar (pojok kiri atas) + hapus sisa ruang putih */
                    .fi-topbar .fi-logo,
                    .fi-topbar-item:has(.fi-logo),
                    .fi-logo { display: none !important; }

                    /* Container kiri topbar (di atas sidebar) — paksa warna sama */
                    .fi-topbar nav > div:first-child,
                    .fi-topbar > nav > div:first-child,
                    .fi-topbar-start,
                    .fi-sidebar-header,
                    [data-tippy-content] .fi-logo { display: none !important; }

                    /* Pastikan SELURUH topbar bar — termasuk bagian kiri atas sidebar — berwarna brand */
                    .fi-topbar { background-color: var(--ks-primary) !important; }
                    .fi-topbar nav { background-color: var(--ks-primary) !important; }
                    .fi-topbar nav > * { background-color: transparent !important; }

                    /* ══════════════════════════════════════════════════════
                       TOPBAR / HEADER
                    ══════════════════════════════════════════════════════ */
                    .fi-topbar,
                    header.fi-topbar,
                    .fi-topbar nav {
                        background-color: var(--ks-primary) !important;
                        border-bottom: none !important;
                        box-shadow: var(--ks-shadow-brand) !important;
                    }
                    .fi-topbar button,
                    .fi-topbar a,
                    .fi-topbar svg {
                        color: rgba(255,255,255,0.88) !important;
                    }
                    .fi-topbar button:hover svg,
                    .fi-topbar a:hover svg {
                        color: #fff !important;
                    }
                    .fi-breadcrumbs ol li span,
                    .fi-breadcrumbs ol li a {
                        color: rgba(255,255,255,0.72) !important;
                    }
                    .fi-breadcrumbs ol li:last-child span {
                        color: #fff !important;
                        font-weight: 600 !important;
                    }
                    .fi-breadcrumbs ol li svg {
                        color: rgba(255,255,255,0.38) !important;
                    }

                    /* ══════════════════════════════════════════════════════
                       PAGE HEADING
                    ══════════════════════════════════════════════════════ */
                    .fi-header-heading {
                        color: var(--ks-primary) !important;
                        font-weight: 700 !important;
                    }
                    .fi-header-subheading {
                        color: var(--ks-text-secondary) !important;
                    }

                    /* ══════════════════════════════════════════════════════
                       CARDS / SECTIONS (fi-section)
                    ══════════════════════════════════════════════════════ */
                    .fi-section {
                        background-color: var(--ks-surface) !important;
                        border: 1px solid var(--ks-border) !important;
                        border-radius: var(--ks-radius-lg) !important;
                        box-shadow: var(--ks-shadow-sm) !important;
                        transition: box-shadow var(--ks-ease-fast) !important;
                    }
                    .fi-section:hover {
                        box-shadow: var(--ks-shadow-md) !important;
                    }
                    .fi-section-header-heading {
                        color: var(--ks-primary) !important;
                        font-weight: 700 !important;
                    }
                    .fi-section-header {
                        border-bottom: 1px solid var(--ks-border) !important;
                    }

                    /* ══════════════════════════════════════════════════════
                       STAT OVERVIEW WIDGET
                    ══════════════════════════════════════════════════════ */
                    .fi-wi-stats-overview-stat {
                        background: var(--ks-surface) !important;
                        border: 1px solid var(--ks-border) !important;
                        border-radius: var(--ks-radius-lg) !important;
                        box-shadow: var(--ks-shadow-sm) !important;
                        transition: transform var(--ks-ease-fast), box-shadow var(--ks-ease-fast) !important;
                    }
                    .fi-wi-stats-overview-stat:hover {
                        transform: translateY(-2px) !important;
                        box-shadow: var(--ks-shadow-brand) !important;
                    }
                    .fi-wi-stats-overview-stat-label {
                        color: var(--ks-text-secondary) !important;
                        font-size: 0.8rem !important;
                    }
                    .fi-wi-stats-overview-stat-value {
                        color: var(--ks-text) !important;
                        font-weight: 700 !important;
                    }
                    .fi-wi-stats-overview-stat-description {
                        color: var(--ks-text-muted) !important;
                        font-size: 0.75rem !important;
                    }

                    /* ══════════════════════════════════════════════════════
                       TABLE WIDGET
                    ══════════════════════════════════════════════════════ */
                    .fi-ta-header-cell {
                        color: var(--ks-text-secondary) !important;
                        font-size: 0.78rem !important;
                        font-weight: 600 !important;
                        text-transform: uppercase !important;
                        letter-spacing: .05em !important;
                    }
                    .fi-ta-cell {
                        color: var(--ks-text) !important;
                    }
                    .fi-ta-row:hover td {
                        background: var(--ks-primary-muted) !important;
                    }

                    /* ══════════════════════════════════════════════════════
                       BUTTONS
                    ══════════════════════════════════════════════════════ */
                    .fi-btn-color-primary.fi-btn {
                        background-color: var(--ks-primary) !important;
                        border-color: var(--ks-primary) !important;
                        border-radius: var(--ks-radius-md) !important;
                        transition: background var(--ks-ease-fast), box-shadow var(--ks-ease-fast) !important;
                    }
                    .fi-btn-color-primary.fi-btn:hover {
                        background-color: var(--ks-primary-light) !important;
                        border-color: var(--ks-primary-light) !important;
                        box-shadow: var(--ks-shadow-brand) !important;
                    }

                    /* ══════════════════════════════════════════════════════
                       FORM INPUTS
                    ══════════════════════════════════════════════════════ */
                    .fi-input {
                        border-color: var(--ks-border) !important;
                        background: var(--ks-bg) !important;
                        border-radius: var(--ks-radius-md) !important;
                        color: var(--ks-text) !important;
                    }
                    .fi-input:focus, .fi-input:focus-within {
                        border-color: var(--ks-primary) !important;
                        outline: 2px solid rgba(184,76,101,0.2) !important;
                    }
                    .fi-select-input {
                        border-color: var(--ks-border) !important;
                        border-radius: var(--ks-radius-md) !important;
                    }

                    /* ══════════════════════════════════════════════════════
                       BADGES
                    ══════════════════════════════════════════════════════ */
                    .fi-badge-color-primary {
                        background: var(--ks-primary-muted) !important;
                        color: var(--ks-primary-dark) !important;
                        border-radius: var(--ks-radius-full) !important;
                    }

                    /* ══════════════════════════════════════════════════════
                       REPORT PAGE — SUMMARY CARDS (via token override)
                    ══════════════════════════════════════════════════════ */
                    .ks-card-income  { background: var(--ks-success-bg) !important; }
                    .ks-card-expense { background: var(--ks-error-bg) !important; }
                    .ks-card-balance { background: var(--ks-primary-muted) !important; }

                    .ks-card-income  .ks-card-label  { color: var(--ks-success) !important; }
                    .ks-card-income  .ks-card-value  { color: var(--ks-success) !important; }
                    .ks-card-expense .ks-card-label  { color: var(--ks-error) !important; }
                    .ks-card-expense .ks-card-value  { color: var(--ks-error) !important; }
                    .ks-card-balance .ks-card-label  { color: var(--ks-primary-dark) !important; }
                    .ks-card-balance .ks-card-value  { color: var(--ks-primary) !important; }

                    .ks-card-value-minus { color: var(--ks-error) !important; }

                    /* ══════════════════════════════════════════════════════
                       REPORT PAGE — TABLES
                    ══════════════════════════════════════════════════════ */
                    .ks-badge-income {
                        background: var(--ks-success-bg) !important;
                        color: var(--ks-success) !important;
                        border-radius: var(--ks-radius-full) !important;
                        padding: 2px 8px !important;
                        font-size: 0.72rem !important;
                        font-weight: 600 !important;
                    }
                    .ks-badge-expense {
                        background: var(--ks-error-bg) !important;
                        color: var(--ks-error) !important;
                        border-radius: var(--ks-radius-full) !important;
                        padding: 2px 8px !important;
                        font-size: 0.72rem !important;
                        font-weight: 600 !important;
                    }
                    .ks-text-income  { color: var(--ks-success) !important; }
                    .ks-text-expense { color: var(--ks-error) !important; }

                    /* Dividers */
                    .ks-divider { border-color: var(--ks-border) !important; }

                    /* ══════════════════════════════════════════════════════
                       REPORT FILTER SELECTS
                    ══════════════════════════════════════════════════════ */
                    .ks-filter-select {
                        border: 1px solid var(--ks-border) !important;
                        border-radius: var(--ks-radius-md) !important;
                        background: var(--ks-bg) !important;
                        color: var(--ks-text) !important;
                        font-size: 0.875rem !important;
                        padding: 6px 12px !important;
                        transition: border-color var(--ks-ease-fast) !important;
                    }
                    .ks-filter-select:focus {
                        outline: 2px solid rgba(184,76,101,0.25) !important;
                        border-color: var(--ks-primary) !important;
                    }
                    .ks-filter-label {
                        font-size: 0.875rem !important;
                        font-weight: 600 !important;
                        color: var(--ks-text-secondary) !important;
                    }

                    /* ══════════════════════════════════════════════════════
                       ACCOUNT WIDGET (Welcome card)
                    ══════════════════════════════════════════════════════ */
                    .fi-wi-account {
                        border: 1px solid var(--ks-border) !important;
                        background: var(--ks-surface) !important;
                        border-radius: var(--ks-radius-lg) !important;
                    }
                    </style>
                '),
            )
            // ── Sidebar override — load LAST (after Filament CSS) ──────
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn () => Blade::render('
                    <style id="ks-sidebar-override">

                    /* ── Non-active: text & icon putih ── */
                    .fi-sidebar-item-button {
                        color: rgba(255,255,255,0.90) !important;
                    }
                    .fi-sidebar-item-button span {
                        color: rgba(255,255,255,0.90) !important;
                    }

                    /* ── AKTIF: background cream, text pakai warna gelap (bawaan Filament) ── */
                    .fi-sidebar-item-button[aria-current="page"],
                    .fi-sidebar-item-button.fi-active {
                        background-color: #fdf0f3 !important;
                        border-radius: 8px !important;
                        box-shadow: none !important;
                    }
                    /* Teks aktif: warna brand */
                    .fi-sidebar-item-button[aria-current="page"] span,
                    .fi-sidebar-item-button.fi-active span {
                        color: #b84c65 !important;
                        font-weight: 700 !important;
                    }

                    /* ── Hover ── */
                    .fi-sidebar-item-button:hover {
                        background-color: rgba(255,255,255,0.15) !important;
                    }

                    /* ── Badge (Roles, dll) ── */
                    .fi-sidebar-item-badge {
                        background-color: #fdf0f3 !important;
                        color: #b84c65 !important;
                        font-weight: 700 !important;
                        border-radius: 999px !important;
                        min-width: 20px !important;
                        text-align: center !important;
                    }

                    </style>
                '),
            )
            // ── Resources & pages ────────────────────────────────────────
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            // ── Navigation group order ────────────────────────────────
            ->navigationGroups([
                NavigationGroup::make('Finance')->collapsible(false),
                NavigationGroup::make('Account Management')->collapsed(),
            ])
            // ── Middleware ────────────────────────────────────────────
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
