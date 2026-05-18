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
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
                'pink'    => Color::Pink,
                'cream'   => Color::Rose,
                'gray'    => Color::Stone, 
            ])
            ->brandName('Kashoes')
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn (): string => \Illuminate\Support\Facades\Blade::render('
                    <style>
                        /* --- 1. BACKGROUND --- */
                        /* Background utama (Cream) */
                        body, .fi-layout, .fi-main {
                            background-color: #ffffffff !important;
                        }
                        .dark body, .dark .fi-layout, .dark .fi-main {
                            background-color: #ffffffff !important;
                        }
                        
                        /* Kotak Tabel dan Card (Pink) */
                        .fi-ta-ctn, .fi-section, .fi-card, .fi-modal-window {
                            background-color: #b84c65 !important; 
                        }
                        .dark .fi-ta-ctn, .dark .fi-section, .dark .fi-card, .dark .fi-modal-window {
                            background-color: #b84c65 !important;
                        }
                        
                        /* Header tabel dan baris tembus pandang menyatu dengan pink */
                        .fi-ta-header, .fi-ta-content, .fi-ta-table, .fi-ta-record {
                            background-color: transparent !important;
                        }

                        /* --- 2. WARNA TEKS --- */
                        /* Teks di area Cream menjadi Pink Tua (agar terlihat jelas dan kontras) */
                        body, h1, h2, h3, h4, p, span, a, div, button, svg {
                            color: #000000ff !important; /* Warna Deep Pink */
                        }

                        /* Teks di area Pink (dalam Tabel/Card) menjadi Cream */
                        .fi-ta-ctn, .fi-section, .fi-card, .fi-modal-window,
                        .fi-ta-ctn *, .fi-section *, .fi-card *, .fi-modal-window * {
                            color: #000000ff !important; /* Warna Cream */
                        }

                        /* Pengecualian: Tombol utama "New Service" agar teksnya tetap putih/terbaca */
                        .fi-btn-color-primary, .fi-btn-color-primary * {
                            color: #ffffff !important;
                        }

                        /* --- 3. INPUT, SEARCH & CHECKBOX --- */
                        /* Terapkan border dan warna hanya pada pembungkus utama (wrapper) agar ikon ikut masuk ke dalam kotak */
                        .fi-input-wrapper, 
                        input[type="checkbox"], select {
                            border: 1.5px solid #000000ff !important; 
                            background-color: #ffffffff !important; 
                            box-shadow: none !important;
                        }
                        
                        /* Pastikan elemen input di dalam wrapper tetap transparan agar menyatu dengan ikon */
                        .fi-input-wrapper input {
                            background-color: transparent !important;
                            border: none !important;
                        }
                        
                        /* Khusus mode gelap */
                        .dark .fi-input-wrapper, 
                        .dark input[type="checkbox"], .dark select {
                            border: 1.5px solid #000000ff !important; 
                            background-color: #ffffffff !important;
                        }
                    </style>
                ')
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
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
            ])
            ->navigationGroups([
                'Finance',
                'Account Management',
            ]);
    }
}
