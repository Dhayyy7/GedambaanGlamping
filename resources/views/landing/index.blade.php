@php
    $setting = $setting ?? \App\Models\Setting::getSetting();
    $rawWa = $setting->wa_number ?? '';
    $waClean = preg_replace('/[^0-9]/', '', $rawWa);
    if (str_starts_with($waClean, '0')) {
        $waClean = '62' . substr($waClean, 1);
    }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $setting->homestay_name ?? 'Gedambaan Glamping' }} - Escape to Nature, Wake Up to SunRise Moment</title>
    <meta name="description" content="Glamping Premium di tepi Pantai Gedambaan Kotabaru. Nikmati pengalaman camping mewah dengan fasilitas lengkap dan matahari terbit.">
    
    <!-- Dynamic Favicon -->
    @if(!empty($setting->logo) && file_exists(public_path(ltrim($setting->logo, '/'))))
        <link rel="icon" type="image/png" href="/{{ ltrim($setting->logo, '/') }}">
        <link rel="shortcut icon" type="image/png" href="/{{ ltrim($setting->logo, '/') }}">
    @else
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏠</text></svg>">
    @endif

    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Custom Landing Stylesheet (Vibrant Slate & Amber) -->
    <link rel="stylesheet" href="/css/landing.css">

    <!-- Custom Landing JavaScript -->
    <script src="/js/landing.js" defer></script>
</head>
<body>

    <!-- ==========================================
         1. STICKY HEADER / NAVBAR
         ========================================== -->
    <header id="mainNavbar" class="landing-header navbar-transparent">
        <div class="nav-container">
            <!-- Brand Logo & Name -->
            <a href="{{ route('home') }}" class="nav-brand">
                @if(!empty($setting->logo) && file_exists(public_path(ltrim($setting->logo, '/'))))
                    <img src="/{{ ltrim($setting->logo, '/') }}" alt="Logo {{ $setting->homestay_name }}" class="nav-logo-img">
                @else
                    <div style="width: 42px; height: 42px; border-radius: 12px; background: linear-gradient(135deg, #c2410c, #f97316); display: flex; align-items: center; justify-content: center; color: white; font-weight: 800;">
                        <i class="fa-solid fa-house-chimney"></i>
                    </div>
                @endif
                <span class="nav-brand-title">
                    {{ $setting->homestay_name ?? 'Gedambaan Glamping' }}
                </span>
            </a>

            <!-- Navigation Links (Desktop) -->
            <nav class="nav-links">
                <a href="#kamars" class="nav-item-link">
                    <i class="fa-solid fa-tent" style="color: #f97316;"></i>
                    <span>Pesan Glamping</span>
                </a>
                <a href="#fasilitas" class="nav-item-link">
                    <i class="fa-solid fa-wand-magic-sparkles" style="color: #f97316;"></i>
                    <span>Fasilitas</span>
                </a>
                <a href="#testimoni" class="nav-item-link">
                    <i class="fa-solid fa-star" style="color: #f97316;"></i>
                    <span>Testimoni</span>
                </a>
                <a href="#lokasi" class="nav-item-link">
                    <i class="fa-solid fa-location-dot" style="color: #f97316;"></i>
                    <span>Kontak</span>
                </a>
            </nav>

            <!-- Actions Header -->
            <div class="nav-actions">
                @php
                    $waClean = preg_replace('/[^0-9]/', '', $setting->wa_number ?? '6281234567890');
                    if (str_starts_with($waClean, '0')) {
                        $waClean = '62' . substr($waClean, 1);
                    }
                @endphp
                
                <a href="https://wa.me/{{ $waClean }}" target="_blank" class="nav-phone-contact">
                    <i class="fa-solid fa-phone"></i>
                    <span>0{{ substr($waClean, 2) }}</span>
                </a>

                <a href="https://wa.me/{{ $waClean }}?text=Halo%20Admin%20{{ urlencode($setting->homestay_name ?? 'Gedambaan Glamping') }},%20saya%20ingin%20bertanya%20mengenai%20reservasi." target="_blank" class="btn-header-wa">
                    <i class="fa-brands fa-whatsapp"></i>
                    <span>Hubungi Kami</span>
                </a>

                <button type="button" id="darkModeToggle" class="btn-theme-toggle" title="Mode Gelap / Terang">
                    <i class="fa-solid fa-moon"></i>
                </button>
            </div>
        </div>
    </header>


    @php
        $heroAssetUrls = [];
        if (is_array($setting->media_assets) && count($setting->media_assets) > 0) {
            foreach ($setting->media_assets as $asset) {
                if (isset($asset['path']) && file_exists(public_path($asset['path']))) {
                    $heroAssetUrls[] = '/' . $asset['path'];
                }
            }
        }
    @endphp

    <!-- ==========================================
         2. HERO SECTION
         ========================================== -->
    <section id="heroSection" class="hero-wrapper" data-assets='@json($heroAssetUrls)'>
        <div class="hero-inner">
            <!-- Badge Subtitle -->
            <div class="hero-badge">
                <span>EST. 2026 — GLAMPING PREMIUM</span>
            </div>

            <!-- Main Title (NusaKos Style Italic Serif) -->
            <h1 class="hero-title">
                "Escape to Nature,
                <span class="hero-title-serif">Wake Up to SunRise Moment"</span>
            </h1>

            <!-- Subtitle Description -->
            <p class="hero-desc">
                Glamping Premium di tepi Pantai Gedambaan Kotabaru. Nikmati momen alam memukau dengan fasilitas lengkap dan udara pantai yang segar.
            </p>

            <!-- CTA Buttons -->
            <div class="hero-cta-group">
                <a href="#kamars" class="btn-hero-primary">
                    <span>PESAN GLAMPING</span>
                </a>
                <a href="{{ route('check-room') }}" class="btn-hero-secondary">
                    <span>CEK KETERSEDIAAN</span>
                </a>
            </div>
        </div>

        <!-- Floating Hero Info Widget -->
        <div class="hero-info-container">
            <div class="hero-info-card animate-on-scroll">
                <div class="hero-card-main">
                    <div class="info-left">
                        <div class="info-icon-box">
                            <i class="fa-solid fa-tent"></i>
                        </div>
                        <div style="text-align: left;">
                            <div class="info-status-badge">
                                <span class="pulse-dot"></span>
                                <span>Unit Glamping Tersedia</span>
                            </div>
                            <div class="info-title">{{ $rooms->count() }} Unit Glamping</div>
                        </div>
                    </div>

                    <div class="info-right-buttons">
                        <a href="#kamars" class="btn-card-primary">
                            <i class="fa-solid fa-tent"></i>
                            <span>Pesan Unit Glamping</span>
                            <i class="fa-solid fa-chevron-right" style="font-size: 0.7rem;"></i>
                        </a>
                        <a href="{{ route('check-room') }}" class="btn-card-secondary">
                            <i class="fa-solid fa-calendar-days"></i>
                            <span>Cek Ketersediaan</span>
                        </a>
                    </div>
                </div>

                <!-- Footer Strip Inside Widget -->
                <div class="hero-card-footer-strip">
                    <div><i class="fa-solid fa-location-dot" style="color: var(--primary);"></i> {{ $setting->address ?? ($setting->homestay_name . ', Akses Mudah & Aman') }}</div>
                    <div>•</div>
                    <div><i class="fa-solid fa-clock" style="color: var(--primary);"></i> Check-in 14:00:00 • Out 12:00:00</div>
                </div>
            </div>
        </div>
    </section>


    <!-- ==========================================
         3. FASILITAS UTAMA (FEATURES)
         ========================================== -->
    <section id="fasilitas" class="section-wrapper">
        <div class="section-header animate-on-scroll">
            <span class="badge-soft">Fasilitas Premium</span>
            <h2 class="section-title">Kenyamanan Terbaik Untuk Anda</h2>
            <p class="section-desc">
                Didesain khusus untuk memberikan kenyamanan menginap serasa di rumah sendiri dengan fasilitas modern yang lengkap.
            </p>
        </div>

        <div class="features-grid">
            <!-- Feature 1: Naturehike Premium Tent -->
            <div class="feature-card animate-on-scroll delay-1">
                <div class="feature-icon-box">
                    <i class="fa-solid fa-tent"></i>
                </div>
                <h3 class="feature-card-title">Naturehike Premium Tent</h3>
                <p class="feature-card-desc">
                    Tenda glamping mewah kualitas terbaik dari Naturehike yang kokoh, luas, dan nyaman di tepi pantai.
                </p>
            </div>

            <!-- Feature 2: Air Bed & Sofabed -->
            <div class="feature-card animate-on-scroll delay-2">
                <div class="feature-icon-box">
                    <i class="fa-solid fa-mattress-pillow"></i>
                </div>
                <h3 class="feature-card-title">Air Bed & Sofabed</h3>
                <p class="feature-card-desc">
                    Dilengkapi kasur angin empuk (Air Bed) serta Sofabed santai untuk menjamin kualitas tidur maksimal.
                </p>
            </div>

            <!-- Feature 3: Air Cooler / Kipas Angin -->
            <div class="feature-card animate-on-scroll delay-3">
                <div class="feature-icon-box">
                    <i class="fa-solid fa-fan"></i>
                </div>
                <h3 class="feature-card-title">Air Cooler / Kipas Angin</h3>
                <p class="feature-card-desc">
                    Penyejuk udara siap menjaga suhu di dalam tenda tetap sejuk dan nyaman kapan pun Anda beristirahat.
                </p>
            </div>

            <!-- Feature 4: Area Bilas dan Toilet Dekat -->
            <div class="feature-card animate-on-scroll delay-1">
                <div class="feature-icon-box">
                    <i class="fa-solid fa-toilet"></i>
                </div>
                <h3 class="feature-card-title">Area Bilas & Toilet Dekat</h3>
                <p class="feature-card-desc">
                    Akses sangat mudah dan dekat ke area bilas serta fasilitas toilet umum yang senantiasa terjaga kebersihannya.
                </p>
            </div>

            <!-- Feature 5: Free Wifi & Welcome Drink -->
            <div class="feature-card animate-on-scroll delay-2">
                <div class="feature-icon-box">
                    <i class="fa-solid fa-wifi"></i>
                </div>
                <h3 class="feature-card-title">Free Wifi & Welcome Drink</h3>
                <p class="feature-card-desc">
                    Fasilitas internet wifi gratis serta minuman penyambutan (welcome drink) menyegarkan saat check-in.
                </p>
            </div>

            <!-- Feature 6: CCTV 24 Jam & BBQ Request -->
            <div class="feature-card animate-on-scroll delay-3">
                <div class="feature-icon-box">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <h3 class="feature-card-title">CCTV 24 Jam & BBQ Request</h3>
                <p class="feature-card-desc">
                    Kamera pengawas 24 jam menjamin keamanan area glamping, serta tersedia perlengkapan BBQ sesuai permintaan.
                </p>
            </div>
        </div>
    </section>


    <!-- ==========================================
         4. KAMAR PILIHAN (ROOM CARDS GRID)
         ========================================== -->
    <section id="kamars" class="bg-soft-section section-wrapper" style="max-width: 100%;">
        <div style="max-width: 1280px; margin: 0 auto;">
            <div class="section-header animate-on-scroll">
                <span class="badge-soft">Available Units</span>
                <h2 class="section-title">Pilihan Unit Glamping (G1 - G5)</h2>
                <p class="section-desc">
                    Nikmati sensasi menginap di tenda premium tepi Pantai Gedambaan Kotabaru dengan rate Weekday Rp 450.000 & Weekend Rp 550.000.
                </p>
            </div>

            <div class="rooms-grid">
                @forelse($rooms as $index => $room)
                    <div class="room-card animate-on-scroll delay-{{ ($index % 3) + 1 }}">
                        <div>
                            <!-- Room Image Header -->
                            <div class="room-img-container">
                                @php
                                    $imgs = is_array($room->images) ? array_values(array_filter($room->images)) : [];
                                    $validImgs = [];
                                    foreach ($imgs as $im) {
                                        if (!empty($im)) {
                                            $validImgs[] = str_starts_with($im, 'http') ? $im : ('/' . ltrim($im, '/'));
                                        }
                                    }
                                    $thumb = count($validImgs) > 0 ? end($validImgs) : null;
                                @endphp
                                @if($thumb)
                                    <img src="{{ $thumb }}" alt="{{ $room->name }}" class="room-img" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                                    <div style="display: none; width: 100%; height: 100%; align-items: center; justify-content: center; color: #94a3b8; font-size: 3rem; background-color: #f1f5f9;">
                                        <i class="fa-solid fa-bed"></i>
                                    </div>
                                @else
                                    <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 3rem; background-color: #f1f5f9;">
                                        <i class="fa-solid fa-bed"></i>
                                    </div>
                                @endif

                                <!-- Badge Code Over Image -->
                                {{-- <div class="badge-code">
                                    {{ $room->code }}
                                </div> --}}

                                <!-- Discount Badge if Any -->
                                @if($room->discount && $room->discount > 0)
                                    <div class="badge-discount">
                                        {{ number_format($room->discount, 0) }}% OFF
                                    </div>
                                @endif
                            </div>

                            <!-- Room Info Body -->
                            <div class="room-body">
                                <h3 class="room-title">
                                    {{ $room->name }}
                                </h3>

                                <div class="room-rating">
                                    <span class="stars"><i class="fa-solid fa-star"></i> 4.9</span>
                                    <span>•</span>
                                    <span>Rating Tamu Puas</span>
                                </div>

                                <!-- Key Amenities Pills -->
                                <div class="pills-container">
                                    @forelse($room->facilities as $f)
                                        <span class="pill-item">
                                            <i class="fa-solid {{ $f->icon ?? 'fa-check' }}"></i> {{ $f->name }}
                                        </span>
                                    @empty
                                        <span style="font-size: 0.75rem; color: #94a3b8; font-style: italic;">Fasilitas lengkap standar</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer Pricing & Actions -->
                        <div class="room-footer">
                            <div class="price-box" style="min-height: 76px; display: flex; flex-direction: column; justify-content: flex-end;">
                                @if($room->discount && $room->discount > 0)
                                    <div style="display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.15rem;">
                                        <span class="price-strike" style="font-size: 0.8rem; color: #94a3b8; text-decoration: line-through;">
                                            Rp {{ number_format($room->price, 0, ',', '.') }}
                                        </span>
                                        <span style="background-color: #fee2e2; color: #dc2626; font-size: 0.68rem; font-weight: 700; padding: 0.1rem 0.4rem; border-radius: 4px;">
                                            Diskon {{ number_format($room->discount, 0) }}%
                                        </span>
                                    </div>
                                @endif

                                <div style="display: flex; align-items: baseline; gap: 0.35rem; flex-wrap: wrap;">
                                    <span class="price-final" style="font-size: 0.9rem; font-weight: 500; color: #c2410c;">Harga mulai</span>
                                </div>
                                <div style="display: flex; align-items: baseline; gap: 0.35rem; flex-wrap: wrap;">
                                    <span class="price-final" style="font-size: 1.5rem; font-weight: 800; color: #c2410c;">
                                        Rp {{ number_format($room->final_price, 0, ',', '.') }}
                                    </span>
                                    <span class="price-unit" style="font-size: 0.8rem; color: #64748b; font-weight: 600;">/ malam</span>
                                </div>

                                @if($room->weekend_price && $room->weekend_price > 0)
                                    <div style="font-size: 0.75rem; color: #64748b; opacity: 0.8; margin-top: 0.25rem; font-weight: 500; display: flex; align-items: center; gap: 0.35rem;">
                                        <i class="fa-solid fa-calendar-week" style="font-size: 0.7rem; color: #f97316;"></i>
                                        <span>Harga weekend <strong style="color: #334155;">Rp {{ number_format($room->final_weekend_price, 0, ',', '.') }}</strong> / malam</span>
                                    </div>
                                @else
                                    <div style="font-size: 0.75rem; color: transparent; margin-top: 0.25rem; font-weight: 500; user-select: none;">
                                        &nbsp;
                                    </div>
                                @endif
                            </div>

                            <a href="{{ route('booking', $room->id) }}" class="btn-book-now" style="width: 100%; padding: 0.9rem; border-radius: 12px; background-color: var(--primary, #c2410c); color: #ffffff; font-size: 0.85rem; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase; text-decoration: none; border: none; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 0.6rem; box-shadow: 0 4px 15px rgba(194, 65, 12, 0.3); transition: all 0.25s ease;">
                                <span>Pesan Sekarang</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <div style="grid-column: 1 / -1; text-align: center; padding: 4rem 1rem; background: white; border-radius: 20px; border: 1px solid var(--border-color);" class="animate-on-scroll">
                        <div style="font-size: 3rem; color: #cbd5e1; margin-bottom: 0.5rem;"><i class="fa-solid fa-bed"></i></div>
                        <h4 style="font-weight: 700; color: var(--text-slate-800);">Belum Ada Kamar Terdaftar</h4>
                        <p style="font-size: 0.8rem; color: var(--text-slate-500); margin-top: 0.2rem;">Silakan tambahkan unit kamar melalui Panel Admin.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>


    <!-- ==========================================
         5. EXTRA FASILITAS (EXTRA SERVICES)
         ========================================== -->
    {{-- @if(count($extraFacilities) > 0)
    <section class="bg-amber-dark" style="padding: 5rem 1.5rem;">
        <div style="max-width: 1280px; margin: 0 auto;">
            <div class="section-header animate-on-scroll" style="margin-bottom: 3rem;">
                <span class="badge-soft" style="background: rgba(255,255,255,0.18); color: #ffffff;">
                    Layanan Tambahan
                </span>
                <h2 class="section-title" style="color: white;">Extra Fasilitas & Layanan</h2>
                <p class="section-desc" style="color: rgba(255,255,255,0.85);">Dapatkan kenyamanan ekstra dengan tambahan layanan berikut saat reservasi.</p>
            </div>

            <div class="extra-grid">
                @foreach($extraFacilities as $index => $ef)
                <div class="extra-card animate-on-scroll delay-{{ ($index % 3) + 1 }}">
                    <div style="font-size: 1.5rem; color: var(--gold-soft); margin-bottom: 0.5rem;"><i class="fa-solid fa-plus-circle"></i></div>
                    <h3 class="extra-title">{{ $ef->name }}</h3>
                    <div class="extra-price">
                        +Rp {{ number_format($ef->price, 0, ',', '.') }}
                    </div>
                    <p style="font-size: 0.75rem; color: rgba(255,255,255,0.8);">{{ $ef->description ?? 'Layanan tambahan sesuai permintaan' }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif --}}


    <!-- ==========================================
         6. TESTIMONI & ULASAN GOOGLE MAPS (5 ULASAN)
         ========================================== -->
    <section id="testimoni" class="section-wrapper">
        <div class="section-header animate-on-scroll">
            <span class="badge-soft" style="background-color: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe;">
                <i class="fa-brands fa-google" style="color: #4285F4;"></i> Ulasan Google Maps (5.0 ★)
            </span>
            <h2 class="section-title">Apa Kata Tamu Di Google Maps?</h2>
            <p class="section-desc">
                Pengalaman nyata dari tamu yang telah menikmati menginap di {{ $setting->homestay_name ?? 'Gedambaan Glamping' }}.
            </p>
        </div>

        <!-- 4 Google Maps Review Cards Grid -->
        <div class="testimonials-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
            <!-- Review 1: Jesica Tiara (Daripada screenshot asli GMaps) -->
            <div class="testimonial-card animate-on-scroll delay-1" style="display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                        <div style="color: #eab308; font-size: 0.85rem;">
                            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                        </div>
                        <span style="font-size: 0.7rem; color: #94a3b8; font-weight: 600;">
                            <i class="fa-brands fa-google" style="color: #4285f4; font-size: 0.75rem;"></i> 7 bulan lalu
                        </span>
                    </div>
                    <p class="testimonial-text">
                        "Homie bgt, tenang, bersih, sarapannya enak², pelayanan baaiikk best pokoknya 🤎 Deket sama msjid dan jajanan juga 🥰..."
                    </p>
                </div>
                <div class="author-box" style="margin-top: 1rem;">
                    <div class="author-avatar" style="background-color: #fce7f3; color: #db2777;">JT</div>
                    <div>
                        <div class="author-name">Jesica Tiara</div>
                        <div class="author-city"><i class="fa-solid fa-circle-check" style="color: #3b82f6; font-size: 0.7rem;"></i> Local Guide • Google Maps</div>
                    </div>
                </div>
            </div>

            <!-- Review 2: Rizal Firmansyah -->
            <div class="testimonial-card animate-on-scroll delay-2" style="display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                        <div style="color: #eab308; font-size: 0.85rem;">
                            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                        </div>
                        <span style="font-size: 0.7rem; color: #94a3b8; font-weight: 600;">
                            <i class="fa-brands fa-google" style="color: #4285f4; font-size: 0.75rem;"></i> 3 bulan lalu
                        </span>
                    </div>
                    <p class="testimonial-text">
                        "Homestay terbaik di Banjarbaru! Suasana nyaman, ada kolam renang bersih, kamar ber-AC dingin, dan respon mas admin ramah sekali."
                    </p>
                </div>
                <div class="author-box" style="margin-top: 1rem;">
                    <div class="author-avatar" style="background-color: #dbeafe; color: #2563eb;">RF</div>
                    <div>
                        <div class="author-name">Rizal Firmansyah</div>
                        <div class="author-city"><i class="fa-solid fa-circle-check" style="color: #3b82f6; font-size: 0.7rem;"></i> Ulasan Terverifikasi</div>
                    </div>
                </div>
            </div>

            <!-- Review 3: Dewi Lestari -->
            <div class="testimonial-card animate-on-scroll delay-3" style="display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                        <div style="color: #eab308; font-size: 0.85rem;">
                            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                        </div>
                        <span style="font-size: 0.7rem; color: #94a3b8; font-weight: 600;">
                            <i class="fa-brands fa-google" style="color: #4285f4; font-size: 0.75rem;"></i> 5 bulan lalu
                        </span>
                    </div>
                    <p class="testimonial-text">
                        "Recommended banget buat keluarga maupun transit. Tempatnya tenang, bersih, perlengkapan mandi lengkap, dan akses dekat ke mana-mana."
                    </p>
                </div>
                <div class="author-box" style="margin-top: 1rem;">
                    <div class="author-avatar" style="background-color: #dcfce7; color: #16a34a;">DL</div>
                    <div>
                        <div class="author-name">Dewi Lestari</div>
                        <div class="author-city"><i class="fa-solid fa-circle-check" style="color: #3b82f6; font-size: 0.7rem;"></i> Local Guide • Google Maps</div>
                    </div>
                </div>
            </div>

            <!-- Review 4: Andi Pratama -->
            <div class="testimonial-card animate-on-scroll delay-4" style="display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                        <div style="color: #eab308; font-size: 0.85rem;">
                            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                        </div>
                        <span style="font-size: 0.7rem; color: #94a3b8; font-weight: 600;">
                            <i class="fa-brands fa-google" style="color: #4285f4; font-size: 0.75rem;"></i> 2 bulan lalu
                        </span>
                    </div>
                    <p class="testimonial-text">
                        "Fasilitas sesuai ekspetasi, Smart TV & WiFi kencang buat kerja online. Tempat parkir luas dan aman. Pasti bakal langganan menginap!"
                    </p>
                </div>
                <div class="author-box" style="margin-top: 1rem;">
                    <div class="author-avatar" style="background-color: #fef3c7; color: #d97706;">AP</div>
                    <div>
                        <div class="author-name">Andi Pratama</div>
                        <div class="author-city"><i class="fa-solid fa-circle-check" style="color: #3b82f6; font-size: 0.7rem;"></i> Ulasan Terverifikasi</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Counter Bar -->
        <div class="stats-card animate-on-scroll">
            <div class="stats-grid">
                <div>
                    <div class="stat-number">500+</div>
                    <div class="stat-label">Tamu Puas</div>
                </div>
                <div>
                    <div class="stat-number">4.9</div>
                    <div class="stat-label">Rating Kepuasan</div>
                </div>
                <div>
                    <div class="stat-number">99%</div>
                    <div class="stat-label">Merekomendasikan</div>
                </div>
                <div>
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">Respon Layanan</div>
                </div>
            </div>
        </div>
    </section>


    <!-- ==========================================
         7. LOKASI KAMI (TEMUKAN KAMI - NUSAKOS SS2 LAYOUT)
         ========================================== -->
    <section id="lokasi" class="bg-soft-section section-wrapper" style="max-width: 100%;">
        <div style="max-width: 1280px; margin: 0 auto;">
            <div class="section-header animate-on-scroll">
                <span class="badge-soft">Lokasi Kami</span>
                <h2 class="section-title">Temukan Kami</h2>
                <p class="section-desc">
                    Kunjungi lokasi kami atau hubungi tim kami untuk informasi lebih lanjut
                </p>
            </div>

            <div class="nusakos-location-grid">
                <!-- Left Column: Interactive Map Box -->
                <div class="nusakos-map-box animate-on-scroll delay-1">
                    @php
                        $rawGmapLink = $setting->gmap_link;
                        $addressQuery = $setting->address ?: ($setting->homestay_name ?? 'Gedambaan Glamping');
                        
                        if (!empty($rawGmapLink) && (str_contains($rawGmapLink, 'embed') || str_contains($rawGmapLink, 'output=embed'))) {
                            $embedSrc = $rawGmapLink;
                        } else {
                            $embedSrc = 'https://maps.google.com/maps?q=' . urlencode($addressQuery) . '&t=&z=15&ie=UTF8&iwloc=&output=embed';
                        }

                        $directGmapUrl = !empty($rawGmapLink) ? $rawGmapLink : 'https://maps.google.com/?q=' . urlencode($addressQuery);
                    @endphp

                    <iframe src="{{ $embedSrc }}" class="nusakos-map-iframe" allowfullscreen="" loading="lazy"></iframe>
                    
                    <div style="position: absolute; bottom: 1rem; left: 1rem; right: 1rem; z-index: 10; display: flex; justify-content: flex-end; pointer-events: none;">
                        <a href="{{ $directGmapUrl }}" target="_blank" class="btn-card-primary" style="background: rgba(15, 23, 42, 0.85); color: white; border-radius: 50px; padding: 0.6rem 1.25rem; font-size: 0.75rem; backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.2); pointer-events: auto; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">
                            <i class="fa-solid fa-map-location-dot" style="color: #f97316;"></i>
                            <span>Buka di Google Maps</span>
                            <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 0.65rem;"></i>
                        </a>
                    </div>
                </div>

                <!-- Right Column: 4 Stacked Info Cards -->
                <div class="nusakos-info-stack">
                    <!-- 1. Alamat -->
                    <div class="nusakos-info-card animate-on-scroll delay-1">
                        <div class="nusakos-info-icon">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <div>
                            <div class="nusakos-info-label">Alamat</div>
                            <div class="nusakos-info-text">
                                {!! nl2br(e($setting->address ?? 'Tepi Pantai Gedambaan, Kotabaru, Kalimantan Selatan')) !!}
                            </div>
                        </div>
                    </div>

                    <!-- 2. WhatsApp -->
                    <div class="nusakos-info-card animate-on-scroll delay-2">
                        <div class="nusakos-info-icon">
                            <i class="fa-brands fa-whatsapp"></i>
                        </div>
                        <div>
                            <div class="nusakos-info-label">WhatsApp Admin</div>
                            <div class="nusakos-info-text">
                                <a href="https://wa.me/{{ $waClean }}" target="_blank" style="color: var(--primary); font-weight: 700;">
                                    +{{ $waClean }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Email -->
                    <div class="nusakos-info-card animate-on-scroll delay-3">
                        <div class="nusakos-info-icon">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <div>
                            <div class="nusakos-info-label">Email / Info</div>
                            <div class="nusakos-info-text">
                               info@gedambaanglamping.com
                            </div>
                        </div>
                    </div>

                    <!-- 4. Jam Operasional -->
                    <div class="nusakos-info-card animate-on-scroll delay-4">
                        <div class="nusakos-info-icon">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                        <div>
                            <div class="nusakos-info-label">Jam Operasional</div>
                            <div class="nusakos-info-text">
                                Check-in: 14:00:00 • Check-out: 12:00:00
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- ==========================================
         8. FOOTER
         ========================================== -->
    <footer class="landing-footer">
        <div class="footer-container">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                @if(isset($setting->logo) && file_exists(public_path($setting->logo)))
                    <img src="/{{ $setting->logo }}" alt="Logo" style="height: 34px; width: auto;">
                @else
                    <i class="fa-solid fa-house-chimney" style="color: var(--primary); font-size: 1.25rem;"></i>
                @endif
                <span style="font-size: 1.15rem; font-weight: 700; color: white;">
                    {{ $setting->homestay_name ?? 'Gedambaan Glamping' }}
                </span>
            </div>

            <div style="font-size: 0.75rem; color: var(--text-slate-400);">
                © {{ date('Y') }} {{ $setting->homestay_name ?? 'Gedambaan Glamping' }}. All rights reserved.
            </div>

            <div style="display: flex; align-items: center; gap: 1.25rem; font-size: 0.85rem;">
                <a href="#kamars" style="color: var(--text-slate-400);">Glamping Units</a>
                <span>•</span>
                <a href="#fasilitas" style="color: var(--text-slate-400);">Fasilitas</a>
            </div>
        </div>
    </footer>


    <!-- ==========================================
         9. FLOATING WHATSAPP BUTTON
         ========================================== -->
    <a href="https://wa.me/{{ $waClean }}?text=Halo%20Admin%20{{ urlencode($setting->homestay_name ?? 'Gedambaan Glamping') }},%20saya%20ingin%20reservasi%20unit%20glamping." target="_blank" class="floating-wa-btn">
        <i class="fa-brands fa-whatsapp" style="font-size: 1.25rem;"></i>
        <span>Tanya Admin</span>
    </a>


    <!-- ==========================================
         10. DIRECT WA BOOKING CALCULATOR MODAL
         ========================================== -->
    <div id="bookingModal" class="modal-backdrop-custom">
        <div class="modal-card-custom">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; padding-bottom: 0.85rem; border-bottom: 1px solid var(--border-color);">
                <div>
                    <span style="font-size: 0.7rem; font-weight: 800; color: var(--primary-hover); text-transform: uppercase; letter-spacing: 0.08em;">Form Reservasi Direct WA</span>
                    <h3 id="modal_room_title" style="font-size: 1.35rem; font-weight: 800; color: var(--text-slate-900); margin-top: 0.1rem;">Pesan Kamar</h3>
                </div>
                <button type="button" onclick="closeBookingModal()" style="width: 34px; height: 34px; border-radius: 50%; background: #f1f5f9; color: #475569; border: none; font-weight: 800; cursor: pointer;">
                    ✕
                </button>
            </div>

            <div style="text-align: left;">
                <!-- Customer Name -->
                <div class="form-group-landing">
                    <label for="modal_customer_name" class="form-label-landing">Nama Pemesan <span style="color: #e11d48;">*</span></label>
                    <input type="text" id="modal_customer_name" class="form-input-landing" placeholder="Masukkan nama lengkap Anda" required>
                </div>

                <!-- Dates Grid -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;" class="form-group-landing">
                    <div>
                        <label for="modal_check_in" class="form-label-landing">Check-In</label>
                        <input type="date" id="modal_check_in" class="form-input-landing">
                    </div>
                    <div>
                        <label for="modal_check_out" class="form-label-landing">Check-Out</label>
                        <input type="date" id="modal_check_out" class="form-input-landing">
                    </div>
                </div>

                <!-- Extra Facilities Options -->
                @if(count($extraFacilities) > 0)
                <div class="form-group-landing">
                    <label class="form-label-landing">Extra Fasilitas Tambahan (Opsional)</label>
                    <div class="extra-facility-box" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 0.5rem; background: #f8fafc; padding: 0.85rem; border-radius: 12px; border: 1px solid var(--border-color); max-height: 140px; overflow-y: auto;">
                        @foreach($extraFacilities as $ef)
                        <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem; color: var(--text-slate-800); cursor: pointer; font-weight: 600;">
                            <input type="checkbox" class="extra-facility-checkbox" data-price="{{ $ef->price }}" data-name="{{ $ef->name }}" onchange="calculateBookingSummary()" style="accent-color: var(--primary);">
                            <span>{{ $ef->name }} (+Rp {{ number_format($ef->price, 0, ',', '.') }})</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Dynamic Pricing Summary Breakdown -->
                <div style="background: var(--dark-slate); color: white; padding: 1.35rem; border-radius: 18px; margin-top: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem; margin-bottom: 0.35rem; color: rgba(255,255,255,0.75);">
                        <span>Harga Kamar per Malam:</span>
                        <strong id="modal_room_price" style="color: white;">Rp 0</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem; margin-bottom: 0.35rem; color: rgba(255,255,255,0.75);">
                        <span>Durasi Menginap:</span>
                        <strong id="modal_total_nights" style="color: var(--gold-soft);">1 Malam</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem; margin-bottom: 0.35rem; color: rgba(255,255,255,0.75);">
                        <span>Subtotal Kamar:</span>
                        <strong id="modal_room_subtotal" style="color: white;">Rp 0</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem; margin-bottom: 0.35rem; color: rgba(255,255,255,0.75);">
                        <span>Subtotal Extra Fasilitas:</span>
                        <strong id="modal_extra_subtotal" style="color: var(--gold-soft);">Rp 0</strong>
                    </div>
                    <div style="padding-top: 0.75rem; margin-top: 0.75rem; border-top: 1px solid rgba(255,255,255,0.15); display: flex; justify-content: space-between; align-items: baseline;">
                        <span style="font-size: 0.85rem; font-weight: 800;">Estimasi Total Biaya:</span>
                        <span id="modal_grand_total" style="font-size: 1.35rem; font-weight: 900; color: var(--gold-soft);">Rp 0</span>
                    </div>
                </div>
            </div>

            <!-- Modal Action Buttons -->
            <div style="margin-top: 1.5rem; display: flex; align-items: center; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" onclick="closeBookingModal()" style="padding: 0.8rem 1.35rem; border-radius: 10px; background: #f1f5f9; color: #475569; font-weight: 800; font-size: 0.75rem; text-transform: uppercase; border: none; cursor: pointer;">
                    Batal
                </button>
                <button type="button" onclick="submitBookingToWA()" style="padding: 0.8rem 1.5rem; border-radius: 10px; background: var(--emerald-green); color: white; font-weight: 800; font-size: 0.75rem; text-transform: uppercase; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 0.6rem; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);">
                    <i class="fa-brands fa-whatsapp" style="font-size: 1rem;"></i>
                    <span>Kirim Pesanan ke WhatsApp</span>
                </button>
            </div>
        </div>
    </div>

</body>
</html>
