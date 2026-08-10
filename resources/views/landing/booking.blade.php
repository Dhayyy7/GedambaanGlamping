@php
    $setting = $setting ?? \App\Models\Setting::getSetting();
    $rawWa = $setting->wa_number ?? '';
    $waClean = preg_replace('/[^0-9]/', '', $rawWa);
    if (str_starts_with($waClean, '0')) {
        $waClean = '62' . substr($waClean, 1);
    }
    $rawImgs = is_array($room->images) ? array_values(array_filter($room->images)) : [];
    $imgs = [];
    foreach($rawImgs as $im) {
        if (!empty($im)) {
            $imgs[] = str_starts_with($im, 'http') ? $im : ('/' . ltrim($im, '/'));
        }
    }
    if (count($imgs) === 0) {
        $imgs = ['https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1200&q=80'];
    }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $room->name }} - {{ $setting->homestay_name ?? 'Gedambaan Glamping' }}</title>
    <meta name="description" content="Detail dan reservasi {{ $room->name }} di {{ $setting->homestay_name ?? 'Gedambaan Glamping' }}.">
    
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Custom Landing Stylesheet -->
    <link rel="stylesheet" href="/css/landing.css">

    <style>
        :root {
            --primary: #c2410c;
            --primary-hover: #9a3412;
            --primary-light: #ffedd5;
            --brand-amber: #f97316;
            --emerald-green: #10b981;
            --emerald-dark: #059669;
            --dark-slate: #0f172a;
            --dark-slate-hover: #1e293b;
            --gold-soft: #fde047;
            --card-bg: #ffffff;
            --bg-soft: #f8fafc;
            --text-slate-900: #0f172a;
            --text-slate-800: #1e293b;
            --text-slate-600: #475569;
            --text-slate-500: #64748b;
            --text-slate-400: #94a3b8;
            --border-color: #e2e8f0;

            --nusakos-bg: #f7f4ef;
            --nusakos-card-bg: #ffffff;
            --nusakos-brown: #8c7355;
            --nusakos-brown-dark: #6e583e;
            --nusakos-text-dark: #3a3229;
            --nusakos-text-muted: #82786c;
            --nusakos-border: #ede7de;
        }

        body {
            background-color: var(--nusakos-bg);
            color: var(--nusakos-text-dark);
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            margin: 0;
            padding: 0;
        }

        /* Top Header Navigation */
        .nusakos-navbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #ffffff;
            border-bottom: 1px solid var(--nusakos-border);
            padding: 0.85rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .nusakos-nav-left {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .btn-back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            color: var(--nusakos-text-dark);
            text-decoration: none;
            font-weight: 700;
            font-size: 0.9rem;
            transition: color 0.2s ease;
        }

        .btn-back-link:hover {
            color: var(--nusakos-brown);
        }

        .brand-logo-text {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-weight: 800;
            font-size: 1.15rem;
            color: var(--nusakos-brown-dark);
            text-decoration: none;
        }

        .btn-contact-header {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.55rem 1.25rem;
            border-radius: 50px;
            border: 1.5px solid var(--nusakos-text-dark);
            background: transparent;
            color: var(--nusakos-text-dark);
            font-weight: 700;
            font-size: 0.85rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-contact-header:hover {
            background: var(--nusakos-text-dark);
            color: #ffffff;
        }

        /* Main Container Layout */
        .nusakos-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1.25rem;
            display: grid;
            grid-template-columns: 1.35fr 1fr;
            gap: 2rem;
        }

        @media (max-width: 992px) {
            .nusakos-container {
                grid-template-columns: 1fr;
            }
        }

        /* Gallery Section */
        .gallery-main-wrapper {
            position: relative;
            width: 100%;
            height: 420px;
            border-radius: 20px;
            overflow: hidden;
            background: #e2dcd3;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        }

        .gallery-main-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: opacity 0.3s ease;
        }

        .badge-code-overlay {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(8px);
            padding: 0.35rem 0.85rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--nusakos-text-dark);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .badge-counter-overlay {
            position: absolute;
            bottom: 1rem;
            right: 1rem;
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(8px);
            color: #ffffff;
            padding: 0.3rem 0.75rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .carousel-nav-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(8px);
            color: var(--nusakos-text-dark);
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transition: all 0.2s ease;
            z-index: 10;
        }

        .carousel-nav-btn:hover {
            background: #ffffff;
            color: var(--nusakos-brown);
            transform: translateY(-50%) scale(1.1);
            box-shadow: 0 6px 16px rgba(0,0,0,0.25);
        }

        .carousel-nav-btn.prev-btn {
            left: 1rem;
        }

        .carousel-nav-btn.next-btn {
            right: 1rem;
        }

        .gallery-thumbs {
            display: flex;
            gap: 0.6rem;
            margin-top: 1rem;
            overflow-x: auto;
            padding-bottom: 0.5rem;
        }

        .thumb-item {
            width: 80px;
            height: 60px;
            border-radius: 10px;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            opacity: 0.7;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .thumb-item.active, .thumb-item:hover {
            opacity: 1;
            border-color: var(--nusakos-brown);
        }

        .thumb-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Facilities Section */
        .facility-card-box {
            background: #ffffff;
            border-radius: 20px;
            padding: 1.75rem;
            margin-top: 2rem;
            border: 1px solid var(--nusakos-border);
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        }

        .facility-title {
            font-size: 1.2rem;
            font-weight: 800;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            color: var(--nusakos-text-dark);
        }

        .facility-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1rem;
        }

        .facility-item {
            background: #fdfcf9;
            border: 1px solid var(--nusakos-border);
            border-radius: 12px;
            padding: 0.85rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }

        .facility-icon-box {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: #f0eae1;
            color: var(--nusakos-brown-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
        }

        .facility-name {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--nusakos-text-dark);
        }

        .facility-status {
            font-size: 0.7rem;
            color: var(--nusakos-text-muted);
        }

        /* Right Column Sidebar */
        .nusakos-sidebar {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .room-header-box {
            margin-bottom: 0.25rem;
        }

        .room-main-title {
            font-size: 2rem;
            font-weight: 800;
            margin: 0 0 0.4rem 0;
            color: var(--nusakos-text-dark);
        }

        .room-sub-meta {
            font-size: 0.85rem;
            color: var(--nusakos-text-muted);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Price Banner Card */
        .price-banner {
            background: var(--nusakos-brown);
            color: #ffffff;
            border-radius: 16px 16px 0 0;
            padding: 1.25rem 1.5rem;
        }

        .price-banner-label {
            font-size: 0.75rem;
            opacity: 0.9;
            margin-bottom: 0.2rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .price-banner-amount {
            font-size: 1.85rem;
            font-weight: 900;
        }

        .price-banner-unit {
            font-size: 0.85rem;
            font-weight: 500;
            opacity: 0.9;
        }

        /* Card Main Content */
        .sidebar-booking-card {
            background: #ffffff;
            border-radius: 0 0 16px 16px;
            border: 1px solid var(--nusakos-border);
            border-top: none;
            padding: 1.5rem;
            box-shadow: 0 6px 25px rgba(0,0,0,0.04);
        }

        .rate-type-box {
            background: #fdfcfa;
            border: 1px solid var(--nusakos-border);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1rem;
            text-align: center;
        }

        .rate-type-title {
            font-size: 0.75rem;
            color: var(--nusakos-text-muted);
            margin-bottom: 0.2rem;
        }

        .rate-type-price {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--nusakos-text-dark);
        }

        .check-time-box {
            background: #f7f4ef;
            border-radius: 12px;
            padding: 0.85rem 1rem;
            font-size: 0.8rem;
            color: var(--nusakos-text-dark);
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .btn-pesan-sekarang {
            width: 100%;
            padding: 1rem;
            border-radius: 12px;
            background: var(--nusakos-brown);
            color: #ffffff;
            font-size: 0.9rem;
            font-weight: 800;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            transition: all 0.2s ease;
            box-shadow: 0 4px 15px rgba(140, 115, 85, 0.3);
            margin-bottom: 0.75rem;
        }

        .btn-pesan-sekarang:hover {
            background: var(--nusakos-brown-dark);
            box-shadow: 0 6px 20px rgba(140, 115, 85, 0.4);
        }

        .btn-tanya-wa {
            width: 100%;
            padding: 0.85rem;
            border-radius: 12px;
            background: #ffffff;
            color: var(--nusakos-text-dark);
            border: 1.5px solid var(--nusakos-border);
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-tanya-wa:hover {
            background: #f7f4ef;
        }

        /* Calendar Box */
        .calendar-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid var(--nusakos-border);
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        }

        .calendar-header-title {
            font-size: 1rem;
            font-weight: 800;
            margin-bottom: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .calendar-legend {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            font-size: 0.75rem;
            margin-bottom: 1.25rem;
            color: var(--nusakos-text-muted);
        }

        .legend-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 3px;
            margin-right: 0.35rem;
        }

        .calendar-month-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border: 1px solid var(--nusakos-border);
            border-radius: 10px;
            padding: 0.5rem 1rem;
            margin-bottom: 1rem;
            font-size: 0.85rem;
            font-weight: 700;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.35rem;
            text-align: center;
        }

        .day-name {
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--nusakos-text-muted);
            padding-bottom: 0.4rem;
        }

        .date-cell {
            padding: 0.5rem 0;
            font-size: 0.8rem;
            border-radius: 8px;
            font-weight: 600;
        }

        .date-cell.booked {
            background: #d8c4b0;
            color: #ffffff;
            cursor: not-allowed;
        }

        .date-cell.maintenance {
            background: #ffedd5;
            color: #ea580c;
            border: 1px solid #fed7aa;
            font-weight: 800;
            cursor: not-allowed;
        }

        .date-cell.weekend-holiday {
            background: #f3e8ff;
            color: #7e22ce;
            border: 1px solid #c084fc;
            font-weight: 800;
        }

        .date-cell.available {
            color: var(--nusakos-text-dark);
        }

        .date-cell.other-month {
            color: #d1d5db;
        }

        /* Help Box */
        .help-box {
            background: #f4eee5;
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
            font-size: 0.85rem;
        }

        .help-box-title {
            font-weight: 800;
            margin-bottom: 0.75rem;
            color: var(--nusakos-text-dark);
        }

        .help-item {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 0.4rem;
            color: var(--nusakos-text-muted);
        }

        /* Responsive Breakpoints */
        @media (max-width: 992px) {
            .nusakos-container {
                grid-template-columns: 1fr;
                gap: 1.5rem;
                padding-top: 1rem;
            }

            .gallery-main-wrapper {
                height: 320px;
            }

            .price-sticky-card {
                position: static;
            }

            .nusakos-header {
                padding: 0 1rem;
            }
        }

        @media (max-width: 576px) {
            .gallery-main-wrapper {
                height: 240px;
                border-radius: 14px;
            }

            .thumb-item {
                width: 65px;
                height: 50px;
                border-radius: 8px;
            }

            .price-main {
                font-size: 1.4rem;
            }

            .facility-card-box {
                padding: 1.25rem;
                border-radius: 16px;
            }

            .amenity-grid {
                grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
                gap: 0.75rem;
            }

            .modal-content-card {
                padding: 1.25rem;
                border-radius: 16px;
                margin: 0 10px;
            }

            .btn-book-now, .btn-tanya-wa {
                padding: 0.85rem;
                font-size: 0.9rem;
            }

            .carousel-nav-btn {
                width: 34px;
                height: 34px;
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>

    <!-- Top Navigation Header -->
    <header class="nusakos-navbar">
        <div class="nusakos-nav-left">
            <a href="{{ route('home') }}#kamars" class="btn-back-link">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
            <a href="{{ route('home') }}" class="brand-logo-text">
                @if(!empty($setting->logo) && file_exists(public_path(ltrim($setting->logo, '/'))))
                    <img src="/{{ ltrim($setting->logo, '/') }}" alt="Logo" style="height: 28px; width: auto;">
                @else
                    <i class="fa-solid fa-house-chimney" style="color: var(--nusakos-brown);"></i>
                @endif
                <span>{{ $setting->homestay_name ?? 'Gedambaan Glamping' }}</span>
            </a>
        </div>
        <div>
            <a href="https://wa.me/{{ $waClean }}?text=Halo%20Admin,%20saya%20ingin%20bertanya%20mengenai%20{{ urlencode($room->name) }}" target="_blank" class="btn-contact-header">
                <i class="fa-solid fa-phone"></i>
                <span>Hubungi</span>
            </a>
        </div>
    </header>

    <!-- Main Booking Page Content -->
    <div class="nusakos-container">
        <!-- Left Column: Gallery & Amenities -->
        <div>
            <!-- Gallery Main Carousel -->
            <div class="gallery-main-wrapper" id="carouselWrapper">
                <img id="mainGalleryImg" src="{{ $imgs[0] }}" alt="{{ $room->name }}" class="gallery-main-img" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1200&q=80';">
                
                @if(count($imgs) > 1)
                <button type="button" class="carousel-nav-btn prev-btn" onclick="prevGalleryImg()" title="Foto Sebelumnya">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <button type="button" class="carousel-nav-btn next-btn" onclick="nextGalleryImg()" title="Foto Selanjutnya">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
                @endif

                <div class="badge-counter-overlay">
                    <span id="thumbIndex">1</span>/{{ count($imgs) }}
                </div>
            </div>

            <!-- Gallery Thumbnails Carousel Bar -->
            @if(count($imgs) > 1)
            <div class="gallery-thumbs" id="galleryThumbsContainer">
                @foreach($imgs as $idx => $img)
                    <div class="thumb-item {{ $idx === 0 ? 'active' : '' }}" onclick="setGalleryIndex({{ $idx + 1 }})">
                        <img src="{{ $img }}" alt="{{ $room->name }}" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1200&q=80';">
                    </div>
                @endforeach
            </div>
            @endif

            <!-- Room Description -->
            @if(!empty($room->description))
            <div class="facility-card-box" style="margin-top: 1.5rem;">
                <div class="facility-title">
                    <i class="fa-solid fa-align-left" style="color: var(--nusakos-brown);"></i>
                    <span>Keterangan Kamar</span>
                </div>
                <div style="font-size: 0.9rem; color: var(--nusakos-text-dark); line-height: 1.65; white-space: pre-line;">
                    {{ $room->description }}
                </div>
            </div>
            @endif

            <!-- Room Facilities -->
            <div class="facility-card-box" style="{{ !empty($room->description) ? 'margin-top: 1.5rem;' : '' }}">
                <div class="facility-title">
                    <i class="fa-solid fa-wand-magic-sparkles" style="color: var(--nusakos-brown);"></i>
                    <span>Fasilitas Kamar</span>
                </div>
                <div class="facility-grid">
                    @forelse($room->facilities as $f)
                        <div class="facility-item">
                            <div class="facility-icon-box">
                                <i class="fa-solid {{ $f->icon ?? 'fa-check' }}"></i>
                            </div>
                            <div>
                                <div class="facility-name">{{ $f->name }}</div>
                                <div class="facility-status">Tersedia</div>
                            </div>
                        </div>
                    @empty
                        <div style="font-size: 0.85rem; color: var(--nusakos-text-muted);">
                            Fasilitas standar homestay berkualitas.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Column: Sidebar Price & Booking -->
        <div class="nusakos-sidebar">
            <div class="room-header-box">
                <h1 class="room-main-title">{{ $room->name }}</h1>
                <div class="room-sub-meta">
                    <i class="fa-solid fa-user-group"></i>
                    <span>Lantai 1 • Kamar lebih luas dengan fasilitas tambahan</span>
                </div>
            </div>

            <div>
                <!-- Price Banner Top -->
                <div class="price-banner">
                    <div class="price-banner-label">Mulai dari</div>
                    <div class="price-banner-amount">
                        Rp {{ number_format($room->final_price, 0, ',', '.') }}
                        <span class="price-banner-unit">/malam</span>
                    </div>
                </div>

                <!-- Booking Action Box -->
                <div class="sidebar-booking-card">
                    <div class="rate-type-box">
                        @if($room->weekend_price && $room->weekend_price > 0)
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; text-align: center;">
                            <div>
                                <div class="rate-type-title">Weekday (Sen-Kam)</div>
                                <div class="rate-type-price" style="font-size: 1rem;">
                                    Rp {{ number_format($room->final_price, 0, ',', '.') }}
                                </div>
                            </div>
                            <div style="border-left: 1px solid var(--nusakos-border); padding-left: 0.5rem;">
                                <div class="rate-type-title">Weekend & Libur</div>
                                <div class="rate-type-price" style="font-size: 1rem; color: #4338ca;">
                                    Rp {{ number_format($room->final_weekend_price, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="rate-type-title">Harian</div>
                        <div class="rate-type-price">
                            Rp {{ number_format($room->final_price, 0, ',', '.') }} <span style="font-size: 0.8rem; font-weight: 500;">/malam</span>
                        </div>
                        @endif
                    </div>

                    <div class="check-time-box">
                        <i class="fa-regular fa-clock" style="font-size: 1.1rem; color: var(--nusakos-brown);"></i>
                        <div>
                            <div><strong>Check-in:</strong> 14:00:00</div>
                            <div><strong>Check-out:</strong> 12:00:00</div>
                        </div>
                    </div>

                    <button type="button" onclick="openBookingModal()" class="btn-pesan-sekarang">
                        <i class="fa-regular fa-calendar-check"></i>
                        <span>Pesan Sekarang</span>
                    </button>

                    <a href="https://wa.me/{{ $waClean }}?text=Halo%20Admin,%20saya%20ingin%20bertanya%20ketersediaan%20{{ urlencode($room->name) }}" target="_blank" class="btn-tanya-wa">
                        <i class="fa-brands fa-whatsapp" style="color: #22c55e;"></i>
                        <span>Tanya via WhatsApp</span>
                    </a>
                </div>
            </div>

            <!-- Availability Calendar Widget -->
            <div class="calendar-card">
                <div class="calendar-header-title">
                    <i class="fa-regular fa-calendar" style="color: var(--nusakos-brown);"></i>
                    <span>Ketersediaan</span>
                </div>
                <div class="calendar-legend" style="gap: 0.65rem; flex-wrap: wrap;">
                    <div><span class="legend-dot" style="background: #ffffff; border: 1px solid #d1d5db;"></span> Weekday</div>
                    <div><span class="legend-dot" style="background: #f3e8ff; border: 1px solid #c084fc;"></span> Weekend/Libur</div>
                    <div><span class="legend-dot" style="background: #ffedd5; border: 1px solid #fed7aa;"></span> Maintenance</div>
                    <div><span class="legend-dot" style="background: #d8c4b0;"></span> Terisi</div>
                </div>

                @php
                    $now = \Carbon\Carbon::now();
                    $daysInMonth = $now->daysInMonth;
                    $startOfWeek = $now->copy()->startOfMonth()->dayOfWeekIso; // 1 (Mon) - 7 (Sun)
                @endphp

                <div class="calendar-month-nav">
                    <i class="fa-solid fa-chevron-left" style="cursor: pointer;"></i>
                    <span>{{ $now->isoFormat('MMMM YYYY') }}</span>
                    <i class="fa-solid fa-chevron-right" style="cursor: pointer;"></i>
                </div>

                <div class="calendar-grid">
                    <div class="day-name">Sen</div>
                    <div class="day-name">Sel</div>
                    <div class="day-name">Rab</div>
                    <div class="day-name">Kam</div>
                    <div class="day-name">Jum</div>
                    <div class="day-name">Sab</div>
                    <div class="day-name">Min</div>

                    <!-- Offset for first day -->
                    @for($i = 1; $i < $startOfWeek; $i++)
                        <div class="date-cell other-month">27</div>
                    @endfor

                    @for($day = 1; $day <= $daysInMonth; $day++)
                        @php
                            $dtCarbon = $now->copy()->day($day);
                            $dateStr = $dtCarbon->format('Y-m-d');
                            $isBooked = in_array($dateStr, $bookedDates);
                            $isMaintenance = in_array($dateStr, $maintenanceDates ?? []);
                            $dayOfWeek = $dtCarbon->dayOfWeek; // 5 = Fri, 6 = Sat
                            $isWeekendOrHoliday = in_array($dayOfWeek, [5, 6]) || in_array($dateStr, $holidayDates ?? []);

                            $cellClass = 'available';
                            if ($isBooked) {
                                $cellClass = 'booked';
                            } elseif ($isMaintenance) {
                                $cellClass = 'maintenance';
                            } elseif ($isWeekendOrHoliday) {
                                $cellClass = 'weekend-holiday';
                            }
                        @endphp
                        <div class="date-cell {{ $cellClass }}" title="{{ $isBooked ? 'Terbooking' : ($isMaintenance ? 'Maintenance / Pemeliharaan Kamar' : ($isWeekendOrHoliday ? 'Rate Weekend / Tanggal Merah' : 'Kamar Bebas')) }}">
                            {{ $day }}
                        </div>
                    @endfor
                </div>
            </div>

            <!-- Help Box -->
            <div class="help-box">
                <div class="help-box-title">Butuh Bantuan?</div>
                <div class="help-item">
                    <i class="fa-solid fa-phone"></i>
                    <span>+{{ $waClean }}</span>
                </div>
                <div class="help-item">
                    <i class="fa-solid fa-envelope"></i>
                    <span>info@gedambaanglamping.com</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2-Phase Booking Calculator Modal -->
    <div id="bookingModal" class="modal-backdrop-custom">
        <div class="modal-card-custom" style="max-width: 580px;">
            <!-- Modal Top Header -->
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; padding-bottom: 0.85rem; border-bottom: 1px solid var(--border-color);">
                <div>
                    <span id="modal_step_badge" style="font-size: 0.7rem; font-weight: 800; color: var(--primary-hover); text-transform: uppercase; letter-spacing: 0.08em;">
                        FASE 1: DATA TAMU & RESERVASI
                    </span>
                    <h3 id="modal_room_title" style="font-size: 1.35rem; font-weight: 800; color: var(--text-slate-900); margin-top: 0.1rem;">
                        Pesan {{ $room->name }}
                    </h3>
                </div>
                <button type="button" onclick="closeBookingModal()" style="width: 34px; height: 34px; border-radius: 50%; background: #f1f5f9; color: #475569; border: none; font-weight: 800; cursor: pointer;">
                    ✕
                </button>
            </div>

            <!-- FASE 1: Form Data Tamu -->
            <div id="phase1_container" style="text-align: left;">
                <!-- Customer Name & Phone Grid -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;" class="form-group-landing">
                    <div>
                        <label for="modal_customer_name" class="form-label-landing">Nama Pemesan <span style="color: #e11d48;">*</span></label>
                        <input type="text" id="modal_customer_name" class="form-input-landing" placeholder="Masukkan nama lengkap" required>
                    </div>
                    <div>
                        <label for="modal_customer_phone" class="form-label-landing">No. WhatsApp / HP <span style="color: #e11d48;">*</span></label>
                        <input type="tel" id="modal_customer_phone" class="form-input-landing" placeholder="08xxxxxxxxxx" required>
                    </div>
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
                            <input type="checkbox" class="extra-facility-checkbox" value="{{ $ef->id }}" data-price="{{ $ef->price }}" data-name="{{ $ef->name }}" onchange="calculateSummary()" style="accent-color: var(--primary);">
                            <span>{{ $ef->name }} (+Rp {{ number_format($ef->price, 0, ',', '.') }})</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Dynamic Pricing Summary Breakdown -->
                <div style="background: #0f172a; color: #ffffff; padding: 1.35rem; border-radius: 18px; margin-top: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem; margin-bottom: 0.35rem; color: rgba(255,255,255,0.75);">
                        <span>Harga Kamar per Malam:</span>
                        <strong style="color: white;">Rp {{ number_format($room->final_price, 0, ',', '.') }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem; margin-bottom: 0.35rem; color: rgba(255,255,255,0.75);">
                        <span>Durasi Menginap:</span>
                        <strong id="modal_total_nights" style="color: var(--gold-soft);">1 Malam</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem; margin-bottom: 0.35rem; color: rgba(255,255,255,0.75);">
                        <span>Subtotal Kamar:</span>
                        <strong id="modal_room_subtotal" style="color: white;">Rp {{ number_format($room->final_price, 0, ',', '.') }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem; margin-bottom: 0.35rem; color: rgba(255,255,255,0.75);">
                        <span>Subtotal Extra Fasilitas:</span>
                        <strong id="modal_extra_subtotal" style="color: var(--gold-soft);">Rp 0</strong>
                    </div>
                    <div style="padding-top: 0.75rem; margin-top: 0.75rem; border-top: 1px solid rgba(255,255,255,0.15); display: flex; justify-content: space-between; align-items: baseline;">
                        <span style="font-size: 0.85rem; font-weight: 800;">Estimasi Total Biaya:</span>
                        <span id="modal_grand_total" style="font-size: 1.35rem; font-weight: 900; color: var(--gold-soft);">Rp {{ number_format($room->final_price, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Phase 1 Action Buttons -->
                <div style="margin-top: 1.5rem; display: flex; align-items: center; justify-content: flex-end; gap: 0.75rem;">
                    <button type="button" onclick="closeBookingModal()" style="padding: 0.8rem 1.35rem; border-radius: 10px; background: #f1f5f9; color: #475569; font-weight: 800; font-size: 0.75rem; text-transform: uppercase; border: none; cursor: pointer;">
                        Batal
                    </button>
                    <button type="button" id="btn_submit_phase1" onclick="submitPhase1Store()" style="padding: 0.8rem 1.5rem; border-radius: 10px; background: var(--primary); color: white; font-weight: 800; font-size: 0.75rem; text-transform: uppercase; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 0.6rem; box-shadow: 0 4px 15px rgba(194, 65, 12, 0.4); transition: all 0.2s ease;">
                        <span>Konfirmasi Pembayaran</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- FASE 2: Ringkasan Pesanan & Konfirmasi ke WhatsApp -->
            <div id="phase2_container" style="display: none; text-align: left;">
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 14px; padding: 1rem; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.85rem;">
                    <i class="fa-solid fa-circle-check" style="font-size: 1.75rem; color: #16a34a;"></i>
                    <div>
                        <div style="font-size: 0.85rem; font-weight: 800; color: #15803d;">Pesanan Berhasil Dicatat!</div>
                        <div style="font-size: 0.75rem; color: #166534;">Status: <strong style="background: #dcfce7; padding: 0.15rem 0.5rem; border-radius: 6px; color: #166534;">Pending (Menunggu Pembayaran)</strong></div>
                    </div>
                </div>

                <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 16px; padding: 1.25rem; margin-bottom: 1.25rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 0.75rem; margin-bottom: 0.75rem; border-bottom: 1px dashed #cbd5e1;">
                        <span style="font-size: 0.75rem; color: #64748b; font-weight: 700;">KODE BOOKING</span>
                        <strong id="summary_booking_code" style="font-size: 1.1rem; color: var(--primary); font-family: monospace;">-</strong>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; font-size: 0.8rem;">
                        <div>
                            <span style="color: #64748b; font-size: 0.7rem; display: block;">Nama Pemesan</span>
                            <strong id="summary_customer_name" style="color: #1e293b;">-</strong>
                        </div>
                        <div>
                            <span style="color: #64748b; font-size: 0.7rem; display: block;">No. WhatsApp</span>
                            <strong id="summary_customer_phone" style="color: #1e293b;">-</strong>
                        </div>
                        <div>
                            <span style="color: #64748b; font-size: 0.7rem; display: block;">Kamar / Unit</span>
                            <strong id="summary_room_name" style="color: #1e293b;">-</strong>
                        </div>
                        <div>
                            <span style="color: #64748b; font-size: 0.7rem; display: block;">Durasi Menginap</span>
                            <strong id="summary_stay_dates" style="color: #1e293b;">-</strong>
                        </div>
                    </div>

                    <div id="summary_extra_box" style="margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px dashed #cbd5e1; font-size: 0.75rem; display: none;">
                        <span style="color: #64748b;">Extra Fasilitas:</span>
                        <strong id="summary_extra_facilities" style="color: var(--primary-hover);"></strong>
                    </div>

                    <div style="margin-top: 0.85rem; padding-top: 0.85rem; border-top: 1px solid #cbd5e1; display: flex; justify-content: space-between; align-items: baseline;">
                        <span style="font-size: 0.85rem; font-weight: 800; color: #1e293b;">Total Estimasi Biaya:</span>
                        <span id="summary_grand_total" style="font-size: 1.35rem; font-weight: 900; color: var(--primary-hover);">Rp 0</span>
                    </div>
                </div>

                <p style="font-size: 0.75rem; color: #64748b; text-align: center; margin-bottom: 1.25rem;">
                    Silakan klik tombol di bawah untuk mengonfirmasi pesanan Anda dan mendapatkan nomor rekening pembayaran ke Admin via WhatsApp.
                </p>

                <!-- Phase 2 Action Buttons -->
                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 0.75rem;">
                    <button type="button" onclick="closeBookingModal()" style="padding: 0.8rem 1.35rem; border-radius: 10px; background: #f1f5f9; color: #475569; font-weight: 800; font-size: 0.75rem; text-transform: uppercase; border: none; cursor: pointer;">
                        Selesai
                    </button>
                    <button type="button" onclick="redirectToWhatsAppPhase2()" style="padding: 0.85rem 1.6rem; border-radius: 10px; background: var(--emerald-green); color: white; font-weight: 800; font-size: 0.75rem; text-transform: uppercase; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 0.6rem; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);">
                        <i class="fa-brands fa-whatsapp" style="font-size: 1.1rem;"></i>
                        <span>Konfirmasi ke WhatsApp</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const roomPrice = {{ $room->final_price }};
        const roomWeekdayPrice = {{ $room->price }};
        const roomWeekendPrice = {{ $room->weekend_price && $room->weekend_price > 0 ? $room->weekend_price : $room->price }};
        const roomDiscount = {{ $room->discount ?? 0 }};
        const holidayDatesList = @json($holidayDates ?? []);
        const maintenanceDatesList = @json($maintenanceDates ?? []);
        const waPhone = '{{ $waClean }}';
        const roomName = '{{ e($room->name) }}';
        const roomCode = '{{ e($room->code) }}';

        const roomImagesList = @json($imgs);
        let currentGalleryIndex = 1;
        let autoSlideTimer = null;

        function setGalleryIndex(index) {
            if (roomImagesList.length === 0) return;

            if (index < 1) index = roomImagesList.length;
            if (index > roomImagesList.length) index = 1;

            currentGalleryIndex = index;

            const mainImg = document.getElementById('mainGalleryImg');
            if (mainImg) {
                mainImg.style.opacity = '0.3';
                setTimeout(() => {
                    mainImg.src = roomImagesList[currentGalleryIndex - 1];
                    mainImg.style.opacity = '1';
                }, 120);
            }

            document.getElementById('thumbIndex').innerText = currentGalleryIndex;

            const thumbs = document.querySelectorAll('.thumb-item');
            thumbs.forEach((t, i) => {
                if (i + 1 === currentGalleryIndex) {
                    t.classList.add('active');
                    t.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                } else {
                    t.classList.remove('active');
                }
            });
        }

        function prevGalleryImg() {
            setGalleryIndex(currentGalleryIndex - 1);
            restartAutoSlide();
        }

        function nextGalleryImg() {
            setGalleryIndex(currentGalleryIndex + 1);
            restartAutoSlide();
        }

        function startAutoSlide() {
            if (roomImagesList.length > 1) {
                autoSlideTimer = setInterval(() => {
                    setGalleryIndex(currentGalleryIndex + 1);
                }, 5000);
            }
        }

        function restartAutoSlide() {
            if (autoSlideTimer) clearInterval(autoSlideTimer);
            startAutoSlide();
        }

        function openBookingModal() {
            resetModalPhases();
            document.getElementById('bookingModal').classList.add('active');
        }

        function closeBookingModal() {
            document.getElementById('bookingModal').classList.remove('active');
            setTimeout(resetModalPhases, 300);
        }

        function resetModalPhases() {
            document.getElementById('phase1_container').style.display = 'block';
            document.getElementById('phase2_container').style.display = 'none';
            document.getElementById('modal_step_badge').innerText = 'FASE 1: DATA TAMU & RESERVASI';
            document.getElementById('modal_room_title').innerText = 'Pesan {{ e($room->name) }}';
            createdBookingData = null;
        }

        document.addEventListener('DOMContentLoaded', function() {
            startAutoSlide();

            const wrapper = document.getElementById('carouselWrapper');
            if (wrapper) {
                wrapper.addEventListener('mouseenter', () => { if (autoSlideTimer) clearInterval(autoSlideTimer); });
                wrapper.addEventListener('mouseleave', () => { startAutoSlide(); });
            }
            const today = new Date().toISOString().split('T')[0];
            const tomorrowObj = new Date();
            tomorrowObj.setDate(tomorrowObj.getDate() + 1);
            const tomorrow = tomorrowObj.toISOString().split('T')[0];

            const checkInInput = document.getElementById('modal_check_in');
            const checkOutInput = document.getElementById('modal_check_out');

            if (checkInInput && checkOutInput) {
                checkInInput.value = today;
                checkOutInput.value = tomorrow;
                checkInInput.min = today;
                checkOutInput.addEventListener('change', calculateSummary);
                checkInInput.addEventListener('change', function() {
                    checkOutInput.min = this.value;
                    if (checkOutInput.value <= this.value) {
                        const nextDay = new Date(this.value);
                        nextDay.setDate(nextDay.getDate() + 1);
                        checkOutInput.value = nextDay.toISOString().split('T')[0];
                    }
                    calculateSummary();
                });
            }
        });

        function calculateSummary() {
            const checkInVal = document.getElementById('modal_check_in').value;
            const checkOutVal = document.getElementById('modal_check_out').value;
            if (!checkInVal || !checkOutVal) return;

            let checkIn = new Date(checkInVal + 'T00:00:00');
            let checkOut = new Date(checkOutVal + 'T00:00:00');
            if (checkOut <= checkIn) return;

            let weekdayNights = 0;
            let weekendNights = 0;
            let roomSubtotal = 0;

            let curr = new Date(checkIn);
            while (curr < checkOut) {
                let dayOfWeek = curr.getDay(); // 0 = Sun, 5 = Fri, 6 = Sat
                let yyyy = curr.getFullYear();
                let mm = String(curr.getMonth() + 1).padStart(2, '0');
                let dd = String(curr.getDate()).padStart(2, '0');
                let dateStr = `${yyyy}-${mm}-${dd}`;

                let isWeekendOrHoliday = (dayOfWeek === 5 || dayOfWeek === 6) || (Array.isArray(holidayDatesList) && holidayDatesList.includes(dateStr));
                let baseRate = (isWeekendOrHoliday && roomWeekendPrice > 0) ? roomWeekendPrice : roomWeekdayPrice;
                let finalRate = roomDiscount > 0 ? baseRate - (baseRate * (roomDiscount / 100)) : baseRate;

                if (isWeekendOrHoliday) {
                    weekendNights++;
                } else {
                    weekdayNights++;
                }
                roomSubtotal += finalRate;

                curr.setDate(curr.getDate() + 1);
            }

            const totalNights = weekdayNights + weekendNights;

            let extraSubtotal = 0;
            document.querySelectorAll('.extra-facility-checkbox:checked').forEach(cb => {
                extraSubtotal += parseFloat(cb.getAttribute('data-price') || 0);
            });

            const grandTotal = roomSubtotal + extraSubtotal;

            let nightsLabel = `${totalNights} Malam`;
            if (weekdayNights > 0 && weekendNights > 0) {
                nightsLabel += ` (${weekdayNights}x Weekday, ${weekendNights}x Weekend/Libur)`;
            } else if (weekendNights > 0) {
                nightsLabel += ` (${weekendNights}x Weekend/Libur)`;
            } else {
                nightsLabel += ` (${weekdayNights}x Weekday)`;
            }

            document.getElementById('modal_total_nights').innerText = nightsLabel;
            document.getElementById('modal_room_subtotal').innerText = `Rp ${new Intl.NumberFormat('id-ID').format(roomSubtotal)}`;
            document.getElementById('modal_extra_subtotal').innerText = `Rp ${new Intl.NumberFormat('id-ID').format(extraSubtotal)}`;
            document.getElementById('modal_grand_total').innerText = `Rp ${new Intl.NumberFormat('id-ID').format(grandTotal)}`;
        }

        function submitPhase1Store() {
            const customerName = document.getElementById('modal_customer_name').value.trim();
            const customerPhone = document.getElementById('modal_customer_phone').value.trim();
            const checkInDate = document.getElementById('modal_check_in').value;
            const checkOutDate = document.getElementById('modal_check_out').value;

            if (!customerName) {
                Swal.fire({ icon: 'warning', title: 'Nama Wajib Diisi', text: 'Silakan masukkan nama lengkap Anda.', confirmColor: '#8c7355' });
                return;
            }
            if (!customerPhone) {
                Swal.fire({ icon: 'warning', title: 'Nomor WhatsApp Wajib Diisi', text: 'Silakan masukkan nomor WhatsApp Anda.', confirmColor: '#8c7355' });
                return;
            }

            // Check if selected range includes maintenance dates
            if (checkInDate && checkOutDate) {
                let checkCurr = new Date(checkInDate);
                let checkEnd = new Date(checkOutDate);
                let isMaintConflict = false;
                while (checkCurr < checkEnd) {
                    let dStr = checkCurr.toISOString().split('T')[0];
                    if (Array.isArray(maintenanceDatesList) && maintenanceDatesList.includes(dStr)) {
                        isMaintConflict = true;
                        break;
                    }
                    checkCurr.setDate(checkCurr.getDate() + 1);
                }

                if (isMaintConflict) {
                    Swal.fire({ icon: 'error', title: 'Kamar Dalam Pemeliharaan', text: 'Kamar sedang dalam masa pemeliharaan (maintenance) pada tanggal yang dipilih. Silakan pilih tanggal lain.', confirmColor: '#8c7355' });
                    return;
                }
            }

            const extraIds = [];
            document.querySelectorAll('.extra-facility-checkbox:checked').forEach(cb => {
                extraIds.push(cb.value);
            });

            const btnSubmit = document.getElementById('btn_submit_phase1');
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> <span>Menyimpan...</span>`;

            fetch('{{ route("booking.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    room_id: {{ $room->id }},
                    customer_name: customerName,
                    customer_phone: customerPhone,
                    check_in_date: checkInDate,
                    check_out_date: checkOutDate,
                    extra_facility_ids: extraIds
                })
            })
            .then(res => res.json())
            .then(data => {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = `<span>Konfirmasi Pembayaran</span> <i class="fa-solid fa-arrow-right"></i>`;

                if (data.success) {
                    createdBookingData = data.booking;
                    showPhase2Modal(data.booking);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Membuat Pesanan',
                        text: data.message || 'Terjadi kesalahan pada server.',
                        confirmColor: '#8c7355'
                    });
                }
            })
            .catch(err => {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = `<span>Konfirmasi Pembayaran</span> <i class="fa-solid fa-arrow-right"></i>`;
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Sistem',
                    text: 'Gagal terhubung ke server. Silakan coba beberapa saat lagi.',
                    confirmColor: '#8c7355'
                });
            });
        }

        function showPhase2Modal(b) {
            document.getElementById('modal_step_badge').innerText = 'FASE 2: DETAIL PESANAN & KONFIRMASI WA';
            document.getElementById('modal_room_title').innerText = 'Reservasi Dicatat (Pending)';

            document.getElementById('summary_booking_code').innerText = '#' + b.booking_code;
            document.getElementById('summary_customer_name').innerText = b.customer_name;
            document.getElementById('summary_customer_phone').innerText = b.customer_phone;
            document.getElementById('summary_room_name').innerText = `${b.room_name} (${b.room_code})`;

            const formatIndoDate = (dateStr) => {
                const d = new Date(dateStr);
                return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
            };

            document.getElementById('summary_stay_dates').innerText = `${formatIndoDate(b.check_in_date)} - ${formatIndoDate(b.check_out_date)} (${b.total_nights} Malam)`;
            document.getElementById('summary_grand_total').innerText = `Rp ${new Intl.NumberFormat('id-ID').format(b.total_price)}`;

            const extraBox = document.getElementById('summary_extra_box');
            if (b.extra_facilities && b.extra_facilities.length > 0) {
                const names = b.extra_facilities.map(x => x.name).join(', ');
                document.getElementById('summary_extra_facilities').innerText = names;
                extraBox.style.display = 'block';
            } else {
                extraBox.style.display = 'none';
            }

            document.getElementById('phase1_container').style.display = 'none';
            document.getElementById('phase2_container').style.display = 'block';
        }

        function redirectToWhatsAppPhase2() {
            if (!createdBookingData) return;
            const b = createdBookingData;

            const formatIndoDate = (dateStr) => {
                const d = new Date(dateStr);
                return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
            };

            let extrasText = '';
            if (b.extra_facilities && b.extra_facilities.length > 0) {
                const names = b.extra_facilities.map(x => `${x.name} (+Rp ${new Intl.NumberFormat('id-ID').format(x.price)})`).join(', ');
                extrasText = `- *Extra Fasilitas*: ${names}\n`;
            }

            let waText = `Halo Admin {{ $setting->homestay_name ?? 'Gedambaan Glamping' }},\n\n` +
                         `Saya ingin mengonfirmasi *Pemesanan Kamar* dengan data berikut:\n\n` +
                         `- *Kode Booking*: *#${b.booking_code}*\n` +
                         `- *Status*: Pending (Menunggu Pembayaran)\n` +
                         `- *Nama Pemesan*: ${b.customer_name}\n` +
                         `- *No. WhatsApp*: ${b.customer_phone}\n` +
                         `- *Kamar / Unit*: ${b.room_name} (${b.room_code})\n` +
                         `- *Check-In*: ${formatIndoDate(b.check_in_date)}\n` +
                         `- *Check-Out*: ${formatIndoDate(b.check_out_date)} (${b.total_nights} Malam)\n` +
                         extrasText +
                         `\n*Total Biaya*: *Rp ${new Intl.NumberFormat('id-ID').format(b.total_price)}*\n\n` +
                         `Mohon instruksi nomor rekening/pembayaran untuk penyelesaian reservasi ini. Terima kasih!`;

            const waUrl = `https://wa.me/${waPhone}?text=${encodeURIComponent(waText)}`;
            closeBookingModal();
            window.open(waUrl, '_blank');
        }
    </script>
</body>
</html>
