<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="theme-color" content="#b84c65">
    <title>Receipt - Order #{{ $order->id }} | KaShoes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --brand:   #b84c65;
            --brand-dk:#8b2d45;
            --surface: #f1f5f9;
            --card:    #ffffff;
            --text:    #1e293b;
            --muted:   #64748b;
            --subtle:  #94a3b8;
            --border:  #e2e8f0;
            --radius:  20px;
        }

        html, body { height: 100%; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--surface);
            color: var(--text);
            /* leave room at bottom for sticky toolbar */
            padding: 20px 12px 100px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* ── Sticky bottom toolbar (mobile-first) ─────────────────── */
        .toolbar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 100;
            display: flex;
            gap: 8px;
            padding: 12px 16px;
            padding-bottom: calc(12px + env(safe-area-inset-bottom));
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-top: 1px solid var(--border);
            box-shadow: 0 -4px 20px rgba(0,0,0,.08);
        }

        .btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 13px 10px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            border: none;
            transition: all .18s ease;
            text-decoration: none;
            white-space: nowrap;
            -webkit-tap-highlight-color: transparent;
        }

        .btn-back {
            background: var(--surface);
            color: var(--muted);
            border: 1.5px solid var(--border);
            flex: 0 0 auto;
            padding: 13px 18px;
        }

        .btn-print {
            background: var(--surface);
            color: var(--muted);
            border: 1.5px solid var(--border);
        }

        .btn-download {
            background: linear-gradient(135deg, var(--brand), var(--brand-dk));
            color: #fff;
            box-shadow: 0 4px 14px rgba(184,76,101,.35);
        }

        .btn:active { transform: scale(0.97); }

        /* ── Desktop: top toolbar ──────────────────────────────────── */
        @media (min-width: 600px) {
            body { padding: 32px 16px 48px; }

            .toolbar {
                position: static;
                background: transparent;
                backdrop-filter: none;
                border: none;
                box-shadow: none;
                padding: 0;
                margin-bottom: 20px;
                width: 100%;
                max-width: 500px;
                justify-content: flex-end;
            }

            .btn { flex: 0 0 auto; padding: 10px 20px; }
            .btn-back { flex: 0 0 auto; }

            .btn-download:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(184,76,101,.4); }
            .btn-print:hover, .btn-back:hover { background: #f8fafc; }
        }

        /* ── Receipt Card ──────────────────────────────────────────── */
        #receipt-card {
            background: var(--card);
            width: 100%;
            max-width: 500px;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: 0 8px 40px rgba(0,0,0,.10);
        }

        /* ── Header ────────────────────────────────────────────────── */
        .receipt-header {
            background: linear-gradient(145deg, var(--brand) 0%, var(--brand-dk) 100%);
            padding: 12px 24px 24px;
            text-align: center;
            position: relative;
        }

        .receipt-header::after {
            content: '';
            position: absolute;
            bottom: -1px; left: 0; right: 0;
            height: 22px;
            background: var(--card);
            border-radius: 22px 22px 0 0;
        }

        .logo-wrapper {
            display: flex;
            justify-content: center;
            margin-bottom: 12px;
        }

        .logo-circle {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 24px rgba(0,0,0,.20);
            padding: 5px;
            overflow: hidden;
        }

        .logo-circle img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 50%;
        }

        .receipt-header .tagline {
            color: rgba(255,255,255,.80);
            font-size: 11.5px;
            font-weight: 500;
            letter-spacing: .4px;
            margin-top: 2px;
            margin-bottom: 4px;
        }

        /* ── Body ──────────────────────────────────────────────────── */
        .receipt-body {
            padding: 0 20px 24px;
        }

        @media (min-width: 400px) {
            .receipt-body { padding: 0 24px 28px; }
        }

        /* ── Order Badge & Status ───────────────────────────────────── */
        .order-badge {
            display: flex;
            justify-content: center;
            margin: 18px 0 8px;
        }

        .order-badge span {
            background: #fff5f7;
            border: 1.5px solid #f9c5d1;
            color: var(--brand);
            padding: 5px 18px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .5px;
        }

        .status-row {
            text-align: center;
            margin-bottom: 18px;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 16px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-pending          { background: #e0f2fe; color: #0369a1; }
        .status-in_progress      { background: #fef9c3; color: #92400e; }
        .status-ready_for_pickup { background: #dbeafe; color: #1d4ed8; }
        .status-completed        { background: #dcfce7; color: #15803d; }
        .status-cancelled        { background: #fee2e2; color: #dc2626; }

        /* ── Section label ──────────────────────────────────────────── */
        .section-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.1px;
            text-transform: uppercase;
            color: var(--subtle);
            margin-bottom: 8px;
            margin-top: 18px;
        }

        /* ── Info rows ──────────────────────────────────────────────── */
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            padding: 6px 0;
            font-size: 13px;
            border-bottom: 1px solid #f8fafc;
        }

        .info-row:last-child { border-bottom: none; }

        .info-row .lbl {
            color: var(--muted);
            font-weight: 500;
            flex-shrink: 0;
            min-width: 90px;
        }

        .info-row .val {
            color: var(--text);
            font-weight: 600;
            text-align: right;
            word-break: break-word;
        }

        /* ── Divider ────────────────────────────────────────────────── */
        .divider-dashed {
            border: none;
            border-top: 1.5px dashed var(--border);
            margin: 18px 0;
        }

        /* ── Service items ──────────────────────────────────────────── */
        .service-item {
            background: #f8fafc;
            border-radius: 12px;
            padding: 11px 13px;
            margin-bottom: 8px;
        }

        .svc-name {
            font-size: 14px;
            font-weight: 700;
            color: var(--text);
        }

        .svc-desc {
            font-size: 12px;
            color: var(--muted);
            margin-top: 2px;
            line-height: 1.4;
        }

        .svc-price-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 8px;
        }

        .svc-qty  { font-size: 12px; color: var(--subtle); }
        .svc-price { font-size: 14px; font-weight: 700; color: var(--brand); }

        /* ── Totals ─────────────────────────────────────────────────── */
        .totals-section {
            background: #f8fafc;
            border-radius: 14px;
            padding: 14px 16px;
            margin-top: 14px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            padding: 5px 0;
        }

        .total-row .lbl { color: var(--muted); font-weight: 500; }
        .total-row .val { font-weight: 600; color: var(--text); }
        .total-row.discount .val { color: #dc2626; }

        .total-final {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1.5px solid var(--border);
            margin-top: 10px;
            padding-top: 12px;
        }

        .total-final .lbl { font-size: 15px; font-weight: 700; color: var(--text); }
        .total-final .val { font-size: 20px; font-weight: 800; color: var(--brand); }

        /* ── Footer ─────────────────────────────────────────────────── */
        .receipt-footer {
            text-align: center;
            margin-top: 22px;
            padding-top: 18px;
            border-top: 1.5px dashed var(--border);
        }

        .thank-you    { font-size: 14px; font-weight: 700; color: var(--brand); margin-bottom: 4px; }
        .footer-note  { font-size: 11.5px; color: var(--subtle); line-height: 1.6; }
        .generated-at { font-size: 10.5px; color: #cbd5e1; margin-top: 10px; }

        /* ── Member badge ───────────────────────────────────────────── */
        .member-badge {
            display: inline-flex;
            align-items: center;
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: #fff;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            vertical-align: middle;
            margin-left: 5px;
        }

        /* ── Spinner ────────────────────────────────────────────────── */
        .download-loading { display: none; align-items: center; gap: 8px; }

        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner {
            width: 15px; height: 15px;
            border: 2px solid rgba(255,255,255,.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .6s linear infinite;
        }

        /* ── Print ──────────────────────────────────────────────────── */
        @media print {
            body { background: #fff; padding: 0; }
            .toolbar { display: none !important; }
            #receipt-card { box-shadow: none; border-radius: 0; max-width: 100%; }
        }
    </style>
</head>
<body>

    {{-- ── Toolbar ── --}}
    <div class="toolbar" id="toolbar">
        <a href="{{ url()->previous() }}" class="btn btn-back">← Kembali</a>
        <button class="btn btn-print" onclick="window.print()">🖨️ Print</button>
        <button class="btn btn-download" id="btn-download" onclick="downloadReceipt()">
            <span id="dl-label">⬇ Download PNG</span>
            <span class="download-loading" id="dl-loading">
                <span class="spinner"></span> Tunggu...
            </span>
        </button>
    </div>

    {{-- ── Receipt Card ── --}}
    <div id="receipt-card">

        {{-- Header --}}
        <div class="receipt-header">
            <div class="logo-wrapper">
                <div class="logo-circle">
                    <img src="{{ asset('images/KaShoes.png') }}"
                         alt="KaShoes Logo"
                         crossorigin="anonymous">
                </div>
            </div>
            <div class="tagline">Solusi Perawatan Sepatu Terpercaya</div>
            <div class="tagline">Care · Clean · Clear</div>
        </div>

        <div class="receipt-body">

            {{-- Order Badge & Status --}}
            <div class="order-badge">
                <span>Order #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="status-row">
                @php
                    $statusLabel = match($order->status) {
                        'pending'          => 'Menunggu',
                        'in_progress'      => 'Sedang Diproses',
                        'ready_for_pickup' => 'Siap Diambil',
                        'completed'        => '✓ Selesai',
                        'cancelled'        => '✕ Dibatalkan',
                        default            => $order->status,
                    };
                @endphp
                <span class="status-badge status-{{ $order->status }}">{{ $statusLabel }}</span>
            </div>

            {{-- Customer Info --}}
            <div class="section-label">Informasi Customer</div>

            <div class="info-row">
                <span class="lbl">Nama</span>
                <span class="val">
                    {{ $order->customer_name ?? '-' }}
                    @if($order->customer?->is_member)
                        <span class="member-badge">⭐ Member</span>
                    @endif
                </span>
            </div>

            @if($order->customer_phone)
            <div class="info-row">
                <span class="lbl">No. Telepon</span>
                <span class="val">{{ $order->customer_phone }}</span>
            </div>
            @endif

            @if($order->customer_address)
            <div class="info-row">
                <span class="lbl">Alamat</span>
                <span class="val" style="max-width:200px;">{{ $order->customer_address }}</span>
            </div>
            @endif

            <div class="info-row">
                <span class="lbl">Tgl. Order</span>
                <span class="val">{{ $order->created_at->format('d M Y') }}</span>
            </div>

            <div class="info-row">
                <span class="lbl">Est. Selesai</span>
                <span class="val">{{ $order->estimated_finished_date?->format('d M Y') ?? '-' }}</span>
            </div>

            @if($order->finished_date)
            <div class="info-row">
                <span class="lbl">Tgl. Selesai</span>
                <span class="val">{{ $order->finished_date->format('d M Y') }}</span>
            </div>
            @endif

            <hr class="divider-dashed">

            {{-- Services --}}
            <div class="section-label">Layanan yang Dipesan</div>

            @foreach($items as $item)
            <div class="service-item">
                <div class="svc-name">{{ $item['name'] }}</div>
                @if(!empty($item['description']))
                    <div class="svc-desc">{{ $item['description'] }}</div>
                @endif
                <div class="svc-price-row">
                    <span class="svc-qty">× {{ $item['quantity'] }}</span>
                    <span class="svc-price">Rp {{ number_format($item['price'], 0, ',', '.') }}</span>
                </div>
            </div>
            @endforeach

            {{-- Totals --}}
            <div class="totals-section">
                <div class="total-row">
                    <span class="lbl">Subtotal</span>
                    <span class="val">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                </div>

                @if($discount > 0)
                <div class="total-row discount">
                    <span class="lbl">Diskon</span>
                    <span class="val">− Rp {{ number_format($discount, 0, ',', '.') }}</span>
                </div>
                @endif

                <div class="total-final">
                    <span class="lbl">Total</span>
                    <span class="val">Rp {{ number_format($total, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- Footer --}}
            <div class="receipt-footer">
                <div class="thank-you">Terima kasih atas kepercayaan Anda! 🙏</div>
                <div class="footer-note">
                    Simpan struk ini sebagai bukti pembayaran.<br>
                    Informasi lebih lanjut hubungi KaShoes.
                </div>
                <div class="generated-at">
                    Dicetak pada {{ now()->translatedFormat('d F Y, H:i') }} WIB
                </div>
            </div>

        </div>
    </div>

    <script>
        async function downloadReceipt() {
            const btn     = document.getElementById('btn-download');
            const label   = document.getElementById('dl-label');
            const loading = document.getElementById('dl-loading');
            const toolbar = document.getElementById('toolbar');

            btn.disabled          = true;
            label.style.display   = 'none';
            loading.style.display = 'flex';

            // Hide toolbar so it doesn't interfere
            toolbar.style.visibility = 'hidden';

            try {
                const card = document.getElementById('receipt-card');
                const cardWidth  = card.offsetWidth;
                const cardHeight = card.scrollHeight;

                // Create a temporary wrapper that forces exact dimensions
                const wrapper = document.createElement('div');
                wrapper.style.cssText = [
                    'position:fixed',
                    'top:0', 'left:0',
                    `width:${cardWidth}px`,
                    `height:${cardHeight}px`,
                    'overflow:visible',
                    'z-index:-9999',
                    'pointer-events:none',
                    'background:#ffffff',
                    'border-radius:20px',
                ].join(';');

                // Clone and append
                const clone = card.cloneNode(true);
                clone.style.cssText = `width:${cardWidth}px; border-radius:20px; overflow:visible;`;
                wrapper.appendChild(clone);
                document.body.appendChild(wrapper);

                const canvas = await html2canvas(wrapper, {
                    scale: 3,
                    useCORS: true,
                    allowTaint: true,
                    backgroundColor: '#ffffff',
                    logging: false,
                    scrollX: 0,
                    scrollY: 0,
                    width:  cardWidth,
                    height: cardHeight,
                    windowWidth:  cardWidth,
                    windowHeight: cardHeight,
                });

                document.body.removeChild(wrapper);

                const link    = document.createElement('a');
                link.download = `receipt-order-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}.png`;
                link.href     = canvas.toDataURL('image/png');
                link.click();

            } catch (err) {
                console.error(err);
                alert('Gagal membuat gambar. Silakan coba lagi.');
            } finally {
                toolbar.style.visibility = '';
                btn.disabled             = false;
                label.style.display      = '';
                loading.style.display    = 'none';
            }
        }
    </script>
</body>
</html>
