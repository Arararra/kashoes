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
use Illuminate\Support\HtmlString;
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
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->brandLogo(asset('images/KaShoes.png'))
            ->brandLogoHeight('5rem')
            // ── Brand identity ──────────────────────────────────────────
            ->brandName('')
            ->spa()
            ->darkMode(true)
            ->colors([
                'primary' => Color::hex('#b84c65'),
                'gray'    => Color::Zinc,
            ])
            // ── Logo di sidebar ────────────────────────────────────────
            ->renderHook(
                PanelsRenderHook::SIDEBAR_NAV_START,
                fn () => Blade::render('
                    <div style="padding:16px 20px 10px;display:flex;align-items:center;gap:12px;margin-bottom:10px;">
                        <div style="width:42px;height:42px;border-radius:50%;background:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.05);">
                            <img src="/images/KaShoes.png" style="width:100%;height:100%;object-fit:cover;" alt="KaShoes">
                        </div>
                        <div style="line-height:1.25;">
                            <div style="color:#ffffff;font-weight:800;font-size:16px;letter-spacing:-.3px;">KaShoes.</div>
                            <div style="color:rgba(255,255,255,0.7);font-size:9.5px;letter-spacing:.1em;text-transform:uppercase;margin-top:2px;">Management</div>
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
                       THEME VARIABLES (LIGHT & DARK)
                    ══════════════════════════════════════════════════════ */
                    :root {
                        --ks-primary:        #b84c65;
                        --ks-bg:             #f4f6f8; /* Light gray bg */
                        --ks-surface:        #ffffff; /* White cards */
                        --ks-sidebar-bg:     #b84c65; /* MAROON SIDEBAR FOR LIGHT MODE */
                        --ks-border:         #e2e8f0;
                        --ks-text:           #0f172a; /* Dark text */
                        --ks-text-secondary: #64748b;
                        --ks-radius-md:      12px;
                        --ks-radius-lg:      20px;
                        --ks-shadow-sm:      0 4px 12px rgba(0,0,0,.05);
                    }

                    .dark {
                        --ks-bg:             #1f2128; /* Dark slate bg */
                        --ks-surface:        #2a2d39; /* Dark cards */
                        --ks-sidebar-bg:     #b84c65; /* Maroon sidebar in dark mode */
                        --ks-border:         rgba(255, 255, 255, 0.05);
                        --ks-text:           #f8fafc; /* Light text */
                        --ks-text-secondary: #94a3b8;
                        --ks-shadow-sm:      0 4px 12px rgba(0,0,0,.15);
                    }

                    body, html { background-color: var(--ks-bg) !important; color: var(--ks-text) !important; }
                    .fi-main, .fi-body, .fi-layout { background-color: var(--ks-bg) !important; color: var(--ks-text) !important; }

                    /* ══════════════════════════════════════════════════════
                       SIDEBAR (ALWAYS MAROON)
                    ══════════════════════════════════════════════════════ */
                    aside.fi-sidebar {
                        background: var(--ks-sidebar-bg) !important; 
                        border-right: none !important;
                    }
                    
                    /* Desktop Floating Sidebar */
                    @media (min-width: 1024px) {
                        aside.fi-sidebar {
                            margin: 16px !important;
                            border-radius: 24px !important;
                            height: calc(100vh - 32px) !important;
                            box-shadow: 0 10px 40px rgba(184,76,101,0.2) !important;
                            overflow: hidden !important;
                            z-index: 50 !important;
                            position: sticky !important;
                            top: 16px !important;
                            align-self: flex-start !important;
                            width: 17rem !important;
                        }
                        
                        /* RESTORE TOPBAR ON DESKTOP (Transparent) */
                        .fi-topbar, header.fi-topbar, .fi-topbar nav, .fi-topbar nav > * {
                            background: transparent !important;
                            background-color: transparent !important;
                            backdrop-filter: none !important;
                            -webkit-backdrop-filter: none !important;
                            border-bottom: none !important;
                            box-shadow: none !important;
                        }
                        /* Hide hamburger and logo on desktop topbar (since they are in sidebar) */
                        .fi-topbar-start { display: none !important; }
                    }

                    /* Mobile Sidebar & Topbar adjustments */
                    @media (max-width: 1023px) {
                        .fi-topbar { background-color: var(--ks-surface) !important; border-bottom: 1px solid var(--ks-border) !important; display: flex !important; }
                        .fi-topbar-start { display: flex !important; }
                    }

                    .fi-sidebar-nav { background: transparent !important; }
                    .fi-sidebar-header { display: none !important; }
                    .fi-topbar .fi-logo { display: none !important; }

                    /* ── MENU INACTIVE (White transparent text for Maroon bg) ── */
                    .fi-sidebar-item-button {
                        border-radius: var(--ks-radius-md) !important;
                        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
                        font-weight: 600 !important;
                        margin: 0 16px 4px 16px !important; 
                        padding: 0.6rem 0.75rem !important;
                        border: none !important;
                        background-color: transparent !important;
                        color: rgba(255,255,255,0.7) !important;
                    }

                    .fi-sidebar-item-button span {
                        color: rgba(255,255,255,0.7) !important;
                        transition: all 0.3s ease !important;
                        font-size: 0.95rem !important;
                    }

                    .fi-sidebar svg, .fi-sidebar-item-button svg {
                        color: rgba(255,255,255,0.7) !important;
                        transition: all 0.3s ease !important;
                        width: 1.25rem !important;
                        height: 1.25rem !important;
                    }

                    /* ── WARNA ICON SIDEBAR SPESIFIK ── */
                    .fi-sidebar-item-button[href$="/admin"] svg, .fi-sidebar-item-button[href*="/dashboard"] svg { color: #ffd166 !important; opacity: 0.9; }
                    .fi-sidebar-item-button[href*="/services"] svg { color: #06d6a0 !important; opacity: 0.9; }
                    .fi-sidebar-item-button[href*="/customers"] svg { color: #48cae4 !important; opacity: 0.9; }
                    .fi-sidebar-item-button[href*="/orders"] svg { color: #ffb703 !important; opacity: 0.9; }
                    .fi-sidebar-item-button[href*="/report"] svg { color: #ff8fab !important; opacity: 0.9; }
                    .fi-sidebar-item-button[href*="/cash-flows"] svg { color: #80ed99 !important; opacity: 0.9; }
                    .fi-sidebar-item-button[href*="/users"] svg { color: #cdb4db !important; opacity: 0.9; }
                    .fi-sidebar-item-button[href*="/roles"] svg { color: #ffc8dd !important; opacity: 0.9; }
                    
                    .fi-sidebar-item-active > a svg,
                    .fi-sidebar-item-active > button svg,
                    .fi-sidebar-item-button[aria-current="page"] svg, 
                    .fi-sidebar-item-button.fi-active svg {
                        opacity: 1 !important;
                    }

                    /* ── EFEK HOVER ── */
                    .fi-sidebar-item-button:hover:not(.fi-active):not([aria-current="page"]) {
                        background-color: rgba(255,255,255,0.1) !important;
                    }
                    
                    .fi-sidebar-item-button:hover span:not(.fi-badge *),
                    .fi-sidebar-item-button:hover svg {
                        color: #ffffff !important;
                    }

                    /* ── EFEK AKTIF ── */
                    .fi-sidebar-item-active > a,
                    .fi-sidebar-item-active > button,
                    .fi-sidebar-item-button[aria-current="page"], 
                    .fi-sidebar-item-button.fi-active {
                        background: rgba(255,255,255,0.2) !important;
                        box-shadow: none !important;
                    }

                    .fi-sidebar-item-active > a span,
                    .fi-sidebar-item-active > button span,
                    .fi-sidebar-item-button[aria-current="page"] span, 
                    .fi-sidebar-item-button.fi-active span {
                        color: #ffffff !important;
                        font-weight: 700 !important;
                    }

                    .fi-sidebar-item-active > a svg,
                    .fi-sidebar-item-active > button svg,
                    .fi-sidebar-item-button[aria-current="page"] svg, 
                    .fi-sidebar-item-button.fi-active svg {
                        color: #ffffff !important;
                        stroke: #ffffff !important;
                        fill: none !important;
                    }

                    /* ── BADGES ── */
                    .fi-badge, .fi-sidebar-item-badge {
                        background-color: rgba(255,255,255,0.15) !important;
                        color: #ffffff !important;
                        border: none !important;
                    }
                    
                    .fi-sidebar-item-active .fi-badge,
                    .fi-sidebar-item-button[aria-current="page"] .fi-badge, .fi-active .fi-badge {
                        background-color: #ffffff !important;
                        color: var(--ks-primary) !important;
                    }

                    .fi-sidebar-item-active .fi-badge *,
                    .fi-sidebar-item-button[aria-current="page"] .fi-badge *, .fi-active .fi-badge * {
                        color: var(--ks-primary) !important;
                    }

                    /* ── LABEL GRUP ── */
                    li.fi-sidebar-group > div > span,
                    li.fi-sidebar-group > div > button > span,
                    .fi-sidebar-group-label, .fi-sidebar-group-label span,
                    .fi-sidebar-group-label-text, .fi-sidebar-group-title {
                        color: #ffffff !important;
                        font-size: 0.75rem !important;
                        letter-spacing: .1em !important;
                        text-transform: uppercase !important;
                        font-weight: 800 !important;
                        padding-left: 10px !important;
                    }
                    .fi-sidebar-group-label { margin-top: 16px !important; }

                    /* ── TOPBAR ICON & BREADCRUMB (Mobile Only) ── */
                    .fi-topbar button, .fi-topbar a, .fi-topbar svg, .fi-topbar span { color: var(--ks-text) !important; }
                    .fi-dropdown-panel button, .fi-dropdown-panel a, .fi-dropdown-panel span, .fi-dropdown-panel svg { color: var(--ks-text) !important; }
                    .fi-topbar .fi-breadcrumbs ol li span, .fi-topbar .fi-breadcrumbs ol li a { color: var(--ks-text-secondary) !important; }
                    .fi-topbar .fi-breadcrumbs ol li:last-child span { color: var(--ks-primary) !important; font-weight: 600 !important;}
                    
                    .dark .fi-topbar button, .dark .fi-topbar a, .dark .fi-topbar svg, .dark .fi-topbar span { color: #ffffff !important; }
                    .dark .fi-dropdown-panel button, .dark .fi-dropdown-panel a, .dark .fi-dropdown-panel span, .dark .fi-dropdown-panel svg { color: #ffffff !important; }
                    .dark .fi-topbar .fi-breadcrumbs ol li:last-child span { color: #ffffff !important; }

                    /* Header text override */
                    .fi-header-heading { color: var(--ks-text) !important; font-size: 1.8rem !important; font-weight: 700 !important; letter-spacing: -0.5px !important; }
                    .fi-header-subheading { color: var(--ks-text-secondary) !important; }

                    /* ══════════════════════════════════════════════════════
                       CARDS & WIDGETS
                    ══════════════════════════════════════════════════════ */
                    .fi-section, .fi-wi-stats-overview-stat, .fi-wi-account {
                        background-color: var(--ks-surface) !important;
                        border: 1px solid var(--ks-border) !important;
                        border-radius: var(--ks-radius-lg) !important;
                        box-shadow: var(--ks-shadow-sm) !important;
                        color: var(--ks-text) !important;
                    }
                    
                    /* Welcome widget specific */
                    .fi-wi-account .fi-section-content { background: transparent !important; }
                    
                    /* Override Filament utility text colors for themes */
                    .text-gray-950, .text-gray-900, .fi-section-header-heading, .fi-ta-record { color: var(--ks-text) !important; }
                    .text-gray-500, .text-gray-600, .fi-section-header-description, .fi-wi-stats-overview-stat-label, .fi-ta-header-cell { color: var(--ks-text-secondary) !important; font-weight: 500 !important; }
                    
                    .fi-wi-stats-overview-stat-value { font-size: 1.8rem !important; font-weight: 800 !important; color: var(--ks-text) !important; }
                    
                    /* Stats chart color */
                    .fi-wi-stats-overview-stat-chart path {
                        stroke: var(--ks-primary) !important;
                        stroke-width: 3px !important;
                    }

                    /* Tables */
                    .fi-ta { background-color: var(--ks-surface) !important; border: none !important; }
                    .fi-ta-header, .fi-ta-content, .fi-ta-table { background-color: transparent !important; color: var(--ks-text) !important; }
                    .fi-ta-cell, .fi-ta-header-cell { border-color: var(--ks-border) !important; }
                    .fi-ta-record:nth-child(even) { background-color: rgba(0,0,0,0.02) !important; }
                    .fi-ta-record:hover { background-color: rgba(0,0,0,0.05) !important; }

                    .dark .fi-ta-record:nth-child(even) { background-color: rgba(255,255,255,0.02) !important; }
                    .dark .fi-ta-record:hover { background-color: rgba(255,255,255,0.05) !important; }

                    /* Buttons - FIX FOR "SIGN OUT" BUTTON INVISIBLE TEXT */
                    .fi-btn-color-primary.fi-btn { background-color: var(--ks-primary) !important; border-color: var(--ks-primary) !important; color: #fff !important; }
                    .fi-btn { color: var(--ks-text) !important; } /* Ensure default buttons like Sign Out are visible */
                    .dark .fi-btn-color-gray { color: #ffffff !important; } /* Fix sign out button on dark mode */
                    
                    /* Dropdown panels (Log out user menu) */
                    .fi-dropdown-panel {
                        background-color: var(--ks-surface) !important;
                        border: 1px solid var(--ks-border) !important;
                    }
                    .fi-dropdown-list-item-label { color: var(--ks-text) !important; }

                    /* Inputs & Selects */
                    .fi-input, .ks-filter-select, select { background-color: var(--ks-surface) !important; border-color: var(--ks-border) !important; color: var(--ks-text) !important; }
                    .fi-input:focus, .ks-filter-select:focus, select:focus { border-color: var(--ks-primary) !important; box-shadow: 0 0 0 2px rgba(184,76,101,0.2) !important; }
                    
                    .dark .fi-input, .dark .ks-filter-select, .dark select { background-color: rgba(0,0,0,0.2) !important; }
                    
                    /* Fix Select Options in Dark Mode */
                    select option { background-color: var(--ks-surface) !important; color: var(--ks-text) !important; }
                    .dark select option { background-color: var(--ks-surface) !important; color: #ffffff !important; }
                    
                    /* Widget headings */
                    .fi-wi-widget h2, .fi-wi-widget h3 { color: var(--ks-text) !important; font-weight: 700 !important; }
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