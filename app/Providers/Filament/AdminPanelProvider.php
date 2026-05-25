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
                    <div style="padding:12px 16px 10px;display:flex;align-items:center;gap:10px;border-bottom:1px solid rgba(255,255,255,0.15);margin-bottom:10px;">
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
                    :root {
                        --ks-primary:        #b84c65;
                        --ks-bg:             #faf6f3;
                        --ks-surface:        #ffffff;
                        --ks-border:         #e2d8d2;
                        --ks-text:           #1e1414;
                        --ks-text-secondary: #6b5050;
                        --ks-radius-md:      8px;
                        --ks-radius-lg:      12px;
                        --ks-shadow-sm:      0 1px 3px rgba(30,20,20,.08);
                    }

                    body, html { background-color: var(--ks-bg) !important; }
                    .fi-main, .fi-body { background-color: var(--ks-bg) !important; }

                    /* ══════════════════════════════════════════════════════
                       SIDEBAR & TOPBAR 
                    ══════════════════════════════════════════════════════ */
                    .fi-sidebar, .fi-sidebar-nav, aside.fi-sidebar {
                        background: var(--ks-primary) !important; 
                        border-right: none !important;
                    }

                    .fi-topbar .fi-logo, .fi-topbar-start, .fi-sidebar-header { display: none !important; }

                    .fi-topbar, header.fi-topbar, .fi-topbar nav, .fi-topbar nav > * {
                        background-color: var(--ks-primary) !important;
                        background-image: none !important;
                        border-bottom: none !important;
                        box-shadow: none !important;
                    }

                    /* ══════════════════════════════════════════════════════
                       EFEK INTERAKTIF SIDEBAR (WARNA-WARNI ELEGANT)
                    ══════════════════════════════════════════════════════ */
                    
                    /* ── MENU INACTIVE (Pengaturan Dasar Floating Pills) ── */
                    .fi-sidebar-item-button {
                        border-radius: var(--ks-radius-md) !important;
                        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
                        font-weight: 500 !important;
                        margin: 0 10px 6px 10px !important; 
                        border: none !important;
                        border-left: 3px solid transparent !important;
                    }

                    .fi-sidebar-item-button span {
                        color: rgba(255, 255, 255, 0.85) !important;
                        transition: all 0.3s ease !important;
                    }

                    .fi-sidebar svg, .fi-sidebar-item-button svg {
                        transition: all 0.3s ease !important;
                    }

                    /* ── WARNA-WARNI TRANSPARAN UNTUK SEMUA MENU ── */
                    
                    a.fi-sidebar-item-button[href*="dashboard"]:not(.fi-active), a.fi-sidebar-item-button[href$="/admin"]:not(.fi-active) { background-color: rgba(99, 102, 241, 0.12) !important; border-left-color: #6366f1 !important; }
                    a.fi-sidebar-item-button[href*="dashboard"]:not(.fi-active) svg, a.fi-sidebar-item-button[href$="/admin"]:not(.fi-active) svg { color: #818cf8 !important; stroke: #818cf8 !important; }

                    a.fi-sidebar-item-button[href*="services"]:not(.fi-active) { background-color: rgba(251, 191, 36, 0.12) !important; border-left-color: #fbbf24 !important; }
                    a.fi-sidebar-item-button[href*="services"]:not(.fi-active) svg { color: #fcd34d !important; stroke: #fcd34d !important; }

                    a.fi-sidebar-item-button[href*="produk"]:not(.fi-active), a.fi-sidebar-item-button[href*="products"]:not(.fi-active) { background-color: rgba(56, 189, 248, 0.12) !important; border-left-color: #38bdf8 !important; }
                    a.fi-sidebar-item-button[href*="produk"]:not(.fi-active) svg, a.fi-sidebar-item-button[href*="products"]:not(.fi-active) svg { color: #7dd3fc !important; stroke: #7dd3fc !important; }

                    a.fi-sidebar-item-button[href*="customers"]:not(.fi-active), a.fi-sidebar-item-button[href*="pelanggan"]:not(.fi-active) { background-color: rgba(45, 212, 191, 0.12) !important; border-left-color: #2dd4bf !important; }
                    a.fi-sidebar-item-button[href*="customers"]:not(.fi-active) svg, a.fi-sidebar-item-button[href*="pelanggan"]:not(.fi-active) svg { color: #5eead4 !important; stroke: #5eead4 !important; }

                    a.fi-sidebar-item-button[href*="orders"]:not(.fi-active), a.fi-sidebar-item-button[href*="pesanan"]:not(.fi-active) { background-color: rgba(34, 197, 94, 0.12) !important; border-left-color: #22c55e !important; }
                    a.fi-sidebar-item-button[href*="orders"]:not(.fi-active) svg, a.fi-sidebar-item-button[href*="pesanan"]:not(.fi-active) svg { color: #86efac !important; stroke: #86efac !important; }

                    a.fi-sidebar-item-button[href*="reports"]:not(.fi-active), a.fi-sidebar-item-button[href*="report"]:not(.fi-active), a.fi-sidebar-item-button[href*="laporan"]:not(.fi-active) { background-color: rgba(168, 85, 247, 0.12) !important; border-left-color: #a855f7 !important; }
                    a.fi-sidebar-item-button[href*="reports"]:not(.fi-active) svg, a.fi-sidebar-item-button[href*="report"]:not(.fi-active) svg, a.fi-sidebar-item-button[href*="laporan"]:not(.fi-active) svg { color: #d8b4fe !important; stroke: #d8b4fe !important; }

                    a.fi-sidebar-item-button[href*="users"]:not(.fi-active), a.fi-sidebar-item-button[href*="pengguna"]:not(.fi-active) { background-color: rgba(249, 115, 22, 0.12) !important; border-left-color: #f97316 !important; }
                    a.fi-sidebar-item-button[href*="users"]:not(.fi-active) svg, a.fi-sidebar-item-button[href*="pengguna"]:not(.fi-active) svg { color: #fdba74 !important; stroke: #fdba74 !important; }

                    a.fi-sidebar-item-button[href*="roles"]:not(.fi-active), a.fi-sidebar-item-button[href*="peran"]:not(.fi-active) { background-color: rgba(244, 63, 94, 0.12) !important; border-left-color: #f43f5e !important; }
                    a.fi-sidebar-item-button[href*="roles"]:not(.fi-active) svg, a.fi-sidebar-item-button[href*="peran"]:not(.fi-active) svg { color: #fda4af !important; stroke: #fda4af !important; }

                    a.fi-sidebar-item-button:not(.fi-active):not([href*="dashboard"]):not([href$="/admin"]):not([href*="services"]):not([href*="produk"]):not([href*="products"]):not([href*="customers"]):not([href*="pelanggan"]):not([href*="orders"]):not([href*="pesanan"]):not([href*="reports"]):not([href*="report"]):not([href*="laporan"]):not([href*="users"]):not([href*="pengguna"]):not([href*="roles"]):not([href*="peran"]) {
                        background-color: rgba(255, 255, 255, 0.05) !important;
                        border-left-color: rgba(255, 255, 255, 0.15) !important;
                    }
                    a.fi-sidebar-item-button:not(.fi-active):not([href*="dashboard"]):not([href$="/admin"]):not([href*="services"]):not([href*="produk"]):not([href*="products"]):not([href*="customers"]):not([href*="pelanggan"]):not([href*="orders"]):not([href*="pesanan"]):not([href*="reports"]):not([href*="report"]):not([href*="laporan"]):not([href*="users"]):not([href*="pengguna"]):not([href*="roles"]):not([href*="peran"]) svg {
                        color: rgba(255,255,255,0.7) !important; stroke: rgba(255,255,255,0.7) !important;
                    }

                    /* ── EFEK HOVER (Menyala & Menggeser) ── */
                    .fi-sidebar-item-button:hover:not(.fi-active) {
                        filter: brightness(1.25) !important; 
                        box-shadow: 0 4px 10px rgba(0,0,0,0.1) !important;
                    }
                    
                    .fi-sidebar-item-button:hover span, .fi-sidebar-item-button:hover svg {
                        transform: translateX(4px) !important;
                        color: #ffffff !important;
                    }

                    /* ── EFEK AKTIF (Kotak Terang Putih, Teks Marun) ── */
                    .fi-sidebar-item-button[aria-current="page"], .fi-sidebar-item-button.fi-active {
                        background: #f3f4f6 !important;
                        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
                        border-left: 4px solid var(--ks-primary) !important;
                    }

                    .fi-sidebar-item-button[aria-current="page"] span, .fi-sidebar-item-button.fi-active span {
                        color: var(--ks-primary) !important;
                        font-weight: 700 !important;
                        transform: translateX(4px) !important;
                    }

                    .fi-sidebar-item-button[aria-current="page"] svg, .fi-sidebar-item-button.fi-active svg {
                        color: var(--ks-primary) !important;
                        stroke: var(--ks-primary) !important;
                    }

                    /* ── FIX ABSOLUT UNTUK BADGE ANGKA (MENJEBOL TAILWIND) ── */
                    /* Memaksa kotak menjadi putih dan warna teks di dalamnya menjadi marun */
                    .fi-sidebar-item-button .fi-badge,
                    .fi-sidebar-item-badge,
                    div[class*="badge"], span[class*="badge"] {
                        background-color: #ffffff !important;
                        color: #b84c65 !important;
                        /* Meretas variabel bawaan Tailwind dari Filament */
                        --c-50: #b84c65 !important;
                        --c-400: #b84c65 !important;
                        --c-500: #b84c65 !important;
                        --c-600: #b84c65 !important;
                        font-weight: 900 !important;
                    }
                    
                    .fi-sidebar-item-button .fi-badge *,
                    .fi-sidebar-item-badge * {
                        color: #b84c65 !important;
                    }

                    /* Membalik warna badge saat menu sedang aktif */
                    .fi-sidebar-item-button[aria-current="page"] .fi-badge,
                    .fi-sidebar-item-button[aria-current="page"] .fi-sidebar-item-badge,
                    .fi-active .fi-badge,
                    .fi-active .fi-sidebar-item-badge {
                        background-color: #b84c65 !important;
                        color: #ffffff !important;
                        --c-400: #ffffff !important;
                        --c-500: #ffffff !important;
                        --c-600: #ffffff !important;
                    }
                    
                    .fi-sidebar-item-button[aria-current="page"] .fi-badge *,
                    .fi-sidebar-item-button[aria-current="page"] .fi-sidebar-item-badge *,
                    .fi-active .fi-badge *,
                    .fi-active .fi-sidebar-item-badge * {
                        color: #ffffff !important;
                    }

                    /* ── LABEL GRUP (DENGAN GARIS ESTETIK) ── */
                    .fi-sidebar-group-label {
                        color: rgba(255,255,255,0.60) !important;
                        font-size: 0.65rem !important;
                        letter-spacing: .15em !important;
                        text-transform: uppercase !important;
                        font-weight: 700 !important;
                        display: flex !important;
                        align-items: center !important;
                        gap: 10px !important;
                        margin-top: 10px !important;
                    }
                    
                    .fi-sidebar-group-label::after {
                        content: "";
                        flex: 1;
                        height: 1px;
                        background: linear-gradient(90deg, rgba(255,255,255,0.2) 0%, transparent 100%);
                    }

                    /* ── TOPBAR ICON & BREADCRUMB ── */
                    .fi-topbar button, .fi-topbar a, .fi-topbar svg, .fi-topbar span { color: #ffffff !important; }
                    .fi-breadcrumbs ol li span, .fi-breadcrumbs ol li a { color: rgba(255,255,255,0.85) !important; }
                    .fi-breadcrumbs ol li:last-child span { color: #ffffff !important; font-weight: 700 !important; }

                    /* ══════════════════════════════════════════════════════
                       CARDS & WIDGETS
                    ══════════════════════════════════════════════════════ */
                    .fi-header-heading { color: var(--ks-primary) !important; font-weight: 700 !important; }
                    .fi-section, .fi-wi-stats-overview-stat {
                        background-color: var(--ks-surface) !important;
                        border: 1px solid var(--ks-border) !important;
                        border-radius: var(--ks-radius-lg) !important;
                        box-shadow: var(--ks-shadow-sm) !important;
                    }
                    .fi-wi-stats-overview-stat-label { color: var(--ks-text-secondary) !important; font-size: 0.8rem !important; }
                    .fi-wi-stats-overview-stat-value { color: var(--ks-text) !important; font-weight: 700 !important; }
                    
                    /* Buttons & Inputs */
                    .fi-btn-color-primary.fi-btn { background-color: var(--ks-primary) !important; border-color: var(--ks-primary) !important; }
                    .fi-input { border-color: var(--ks-border) !important; background: var(--ks-bg) !important; color: var(--ks-text) !important; }
                    .fi-input:focus { border-color: var(--ks-primary) !important; outline: 2px solid rgba(184,76,101,0.2) !important; }
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