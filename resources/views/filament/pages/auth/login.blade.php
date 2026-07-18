<div>
    <style>
        * { box-sizing: border-box; }

        /* ── PAGE BACKGROUND ── */
        .ks-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f0f4f8 0%, #fce8ed 100%);
            padding: 2rem 1rem;
            font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif;
        }

        /* ── CARD CONTAINER ── */
        .ks-card {
            display: flex;
            flex-direction: row;
            width: 100%;
            max-width: 1360px;
            min-height: 760px;
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1), 0 4px 16px rgba(184, 76, 101, 0.08);
            overflow: hidden;
        }

        /* ── LEFT: ILLUSTRATION ── */
        .ks-illus {
            display: none;
            width: 70%;
            flex-shrink: 0;
            background: #eef5fc;
            align-items: center;
            justify-content: center;
        }

        .ks-illus img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        /* ── RIGHT: FORM ── */
        .ks-form-side {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 2.5rem 2.5rem 2.5rem 2rem;
        }

        .ks-form-inner {
            width: 100%;
            max-width: 340px;
        }

        /* Logo row */
        .ks-logo-row {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            margin-bottom: 2rem;
        }

        .ks-logo-row img {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fce7ed;
            box-shadow: 0 4px 12px rgba(184, 76, 101, 0.15);
            flex-shrink: 0;
        }

        .ks-brand-text {
            display: flex;
            flex-direction: column;
            line-height: 1.1;
        }

        .ks-brand-name {
            font-size: 1.15rem;
            font-weight: 800;
            color: #b84c65;
            letter-spacing: -0.3px;
        }

        .ks-brand-sub {
            font-size: 0.68rem;
            font-weight: 500;
            color: #b0b8c8;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-top: 2px;
        }

        /* Heading */
        .ks-title {
            font-size: 1.85rem;
            font-weight: 800;
            color: #1e293b;
            margin: 0 0 0.4rem;
            letter-spacing: -0.4px;
            line-height: 1.2;
        }

        .ks-accent-bar {
            width: 36px;
            height: 3px;
            background: linear-gradient(90deg, #b84c65, #e07a8f);
            border-radius: 99px;
            margin: 0 0 1rem;
        }

        .ks-subtitle {
            font-size: 0.85rem;
            color: #94a3b8;
            line-height: 1.65;
            margin: 0 0 1.75rem;
        }

        /* Inputs */
        .ks-form-side .fi-input {
            background-color: #f8fafc !important;
            border: 1.5px solid #e2e8f0 !important;
            border-radius: 8px !important;
            padding: 0.75rem 0.875rem !important;
            box-shadow: none !important;
            font-size: 0.9rem !important;
            color: #1e293b !important;
            transition: all 0.2s ease !important;
        }
        .ks-form-side .fi-input:focus {
            background-color: #fff !important;
            border-color: #b84c65 !important;
            box-shadow: 0 0 0 3px rgba(184, 76, 101, 0.1) !important;
        }
        .ks-form-side .fi-fo-field-wrp-label span {
            font-weight: 600 !important;
            color: #475569 !important;
            font-size: 0.82rem !important;
        }
        .ks-form-side .fi-form-actions {
            display: none !important;
        }
        .ks-form-side .fi-checkbox-input {
            accent-color: #b84c65 !important;
        }

        /* Submit button */
        .ks-btn {
            width: 100%;
            margin-top: 1.25rem;
            padding: 0.8rem;
            background: linear-gradient(135deg, #b84c65 0%, #cf5f7a 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            box-shadow: 0 4px 16px rgba(184, 76, 101, 0.3);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            letter-spacing: 0.2px;
        }

        .ks-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(184, 76, 101, 0.4);
        }

        .ks-btn:active {
            transform: translateY(0);
        }

        /* Footer */
        .ks-footer {
            margin-top: 1.75rem;
            font-size: 0.72rem;
            color: #cbd5e1;
            text-align: center;
        }

        /* ── DESKTOP ── */
        @media (min-width: 768px) {
            .ks-illus {
                display: flex;
            }
        }

        /* ── MOBILE ── */
        @media (max-width: 767px) {
            .ks-card {
                flex-direction: column;
                max-width: 420px;
                min-height: unset;
            }
            .ks-form-side {
                justify-content: center;
                padding: 2rem 1.75rem;
            }
        }
    </style>

    <!-- Page Background -->
    <div class="ks-page">
        <!-- Card -->
        <div class="ks-card">

            <!-- Left: Illustration -->
            <div class="ks-illus">
                <img src="{{ asset('images/login_illustration.png') }}" alt="KaShoes Illustration" />
            </div>

            <!-- Right: Form -->
            <div class="ks-form-side">
                <div class="ks-form-inner">

                    <!-- Brand -->
                    <div class="ks-logo-row">
                        <img src="{{ asset('images/KaShoes.png') }}" alt="KaShoes" />
                        <div class="ks-brand-text">
                            <span class="ks-brand-name">KaShoes</span>
                            <span class="ks-brand-sub">Management System</span>
                        </div>
                    </div>

                    <!-- Title -->
                    <h1 class="ks-title">Welcome Back :)</h1>
                    <div class="ks-accent-bar"></div>
                    <p class="ks-subtitle">Please login with your email and password to continue managing your store.</p>

                    <!-- Form -->
                    <x-filament-panels::form wire:submit="authenticate">
                        {{ $this->form }}
                        <button type="submit" class="ks-btn">Login Now →</button>
                    </x-filament-panels::form>

                    <!-- Footer -->
                    <div class="ks-footer">
                        &copy; {{ date('Y') }} KaShoes. All rights reserved.
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
