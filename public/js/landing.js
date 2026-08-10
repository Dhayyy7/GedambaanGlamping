/* ==========================================================================
   Faradisa HomeStay Landing Page Interactive Script
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function () {
    // 0. Dynamic Hero Background Image Rotator (Every 5 seconds from uploaded Media Assets)
    const heroSection = document.getElementById('heroSection');
    if (heroSection) {
        let assets = [];
        try {
            assets = JSON.parse(heroSection.getAttribute('data-assets') || '[]');
        } catch (e) {
            assets = [];
        }

        const defaultBg = "https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1920&q=80";
        const overlay = "linear-gradient(135deg, rgba(15, 23, 42, 0.85) 0%, rgba(124, 45, 18, 0.78) 100%)";

        if (assets && assets.length > 0) {
            let currentIndex = 0;

            const updateHeroBg = () => {
                const currentImg = assets[currentIndex];
                heroSection.style.backgroundImage = `${overlay}, url('${currentImg}')`;
                currentIndex = (currentIndex + 1) % assets.length;
            };

            // Set initial bg immediately
            updateHeroBg();

            // Rotate every 5 seconds if more than 1 asset exists
            if (assets.length > 1) {
                setInterval(updateHeroBg, 5000);
            }
        } else {
            heroSection.style.backgroundImage = `${overlay}, url('${defaultBg}')`;
        }
    }

    // 1. Sticky Navbar Scroll Effect
    const navbar = document.getElementById('mainNavbar');
    if (navbar) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 50) {
                navbar.classList.add('navbar-scrolled');
                navbar.classList.remove('navbar-transparent');
            } else {
                navbar.classList.remove('navbar-scrolled');
                navbar.classList.add('navbar-transparent');
            }
        });
    }

    // 2. Smooth Scroll for Anchor Links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId && targetId !== '#') {
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });

    // 3. Set Default Booking Modal Dates
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
        checkOutInput.addEventListener('change', calculateBookingSummary);
        checkInInput.addEventListener('change', function() {
            checkOutInput.min = this.value;
            if (checkOutInput.value <= this.value) {
                const nextDay = new Date(this.value);
                nextDay.setDate(nextDay.getDate() + 1);
                checkOutInput.value = nextDay.toISOString().split('T')[0];
            }
            calculateBookingSummary();
        });
    }

    // 4. Scroll Reveal IntersectionObserver for smooth entrance animations
    const scrollObserverOptions = {
        root: null,
        rootMargin: '0px 0px -40px 0px',
        threshold: 0.1
    };

    const scrollObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, scrollObserverOptions);

    document.querySelectorAll('.animate-on-scroll').forEach(el => {
        scrollObserver.observe(el);
    });

    // 5. Dark Mode Toggle
    const themeBtn = document.getElementById('darkModeToggle');
    if (themeBtn) {
        const savedTheme = localStorage.getItem('faradisaLandingTheme');
        if (savedTheme === 'dark') {
            document.body.classList.add('dark-mode');
            themeBtn.innerHTML = '<i class="fa-solid fa-sun"></i>';
        }

        themeBtn.addEventListener('click', function () {
            document.body.classList.toggle('dark-mode');
            const isDark = document.body.classList.contains('dark-mode');
            localStorage.setItem('faradisaLandingTheme', isDark ? 'dark' : 'light');
            themeBtn.innerHTML = isDark ? '<i class="fa-solid fa-sun"></i>' : '<i class="fa-solid fa-moon"></i>';
        });
    }
});

// Current Booking State
let activeBookingState = {
    roomId: null,
    roomName: '',
    roomCode: '',
    pricePerNight: 0,
    qty: 1,
    waNumber: '6281234567890'
};

/**
 * Increment / Decrement Room Quantity inside card
 */
function updateCardQty(roomId, delta) {
    const qtyElem = document.getElementById(`qty_room_${roomId}`);
    if (qtyElem) {
        let currentQty = parseInt(qtyElem.innerText) || 1;
        currentQty = Math.max(1, currentQty + delta);
        qtyElem.innerText = currentQty;
    }
}

/**
 * Open Direct WhatsApp Booking Modal
 */
function openBookingModal(room, waNumber) {
    activeBookingState.roomId = room.id;
    activeBookingState.roomName = room.name;
    activeBookingState.roomCode = room.code;
    activeBookingState.pricePerNight = parseFloat(room.final_price || room.price);
    activeBookingState.waNumber = waNumber || '6281234567890';
    activeBookingState.qty = 1;

    document.getElementById('modal_room_title').innerText = `${room.name} (${room.code})`;
    document.getElementById('modal_room_price').innerText = `Rp ${new Intl.NumberFormat('id-ID').format(activeBookingState.pricePerNight)} / malam`;

    calculateBookingSummary();

    const modal = document.getElementById('bookingModal');
    if (modal) {
        modal.classList.add('active');
    }
}

/**
 * Close Booking Modal
 */
function closeBookingModal() {
    const modal = document.getElementById('bookingModal');
    if (modal) {
        modal.classList.remove('active');
    }
}

/**
 * Calculate Booking Summary (Total Nights, Extra Facilities Subtotal, Grand Total)
 */
function calculateBookingSummary() {
    const checkInVal = document.getElementById('modal_check_in').value;
    const checkOutVal = document.getElementById('modal_check_out').value;

    if (!checkInVal || !checkOutVal) return;

    const checkIn = new Date(checkInVal);
    const checkOut = new Date(checkOutVal);

    const diffTime = Math.abs(checkOut - checkIn);
    const totalNights = Math.max(1, Math.ceil(diffTime / (1000 * 60 * 60 * 24)));

    const roomSubtotal = activeBookingState.pricePerNight * totalNights * activeBookingState.qty;

    // Calculate selected extra facilities
    let extraSubtotal = 0;
    let selectedExtras = [];
    document.querySelectorAll('.extra-facility-checkbox:checked').forEach(cb => {
        const price = parseFloat(cb.getAttribute('data-price') || 0);
        const name = cb.getAttribute('data-name');
        extraSubtotal += price;
        selectedExtras.push(name);
    });

    const grandTotal = roomSubtotal + extraSubtotal;

    document.getElementById('modal_total_nights').innerText = `${totalNights} Malam`;
    document.getElementById('modal_room_subtotal').innerText = `Rp ${new Intl.NumberFormat('id-ID').format(roomSubtotal)}`;
    document.getElementById('modal_extra_subtotal').innerText = `Rp ${new Intl.NumberFormat('id-ID').format(extraSubtotal)}`;
    document.getElementById('modal_grand_total').innerText = `Rp ${new Intl.NumberFormat('id-ID').format(grandTotal)}`;
}

/**
 * Send Order via WhatsApp
 */
function submitBookingToWA() {
    const customerName = document.getElementById('modal_customer_name').value.trim();
    const checkInDate = document.getElementById('modal_check_in').value;
    const checkOutDate = document.getElementById('modal_check_out').value;

    if (!customerName) {
        Swal.fire({
            icon: 'warning',
            title: 'Nama Wajib Diisi',
            text: 'Silakan masukkan nama lengkap Anda untuk reservasi.',
            confirmColor: '#7c2d12'
        });
        return;
    }

    const checkIn = new Date(checkInDate);
    const checkOut = new Date(checkOutDate);
    const diffTime = Math.abs(checkOut - checkIn);
    const totalNights = Math.max(1, Math.ceil(diffTime / (1000 * 60 * 60 * 24)));

    const roomSubtotal = activeBookingState.pricePerNight * totalNights * activeBookingState.qty;

    let extraSubtotal = 0;
    let selectedExtras = [];
    document.querySelectorAll('.extra-facility-checkbox:checked').forEach(cb => {
        const price = parseFloat(cb.getAttribute('data-price') || 0);
        const name = cb.getAttribute('data-name');
        extraSubtotal += price;
        selectedExtras.push(`${name} (+Rp ${new Intl.NumberFormat('id-ID').format(price)})`);
    });

    const grandTotal = roomSubtotal + extraSubtotal;

    // Format dates to DD Month YYYY
    const formatIndoDate = (dateStr) => {
        const d = new Date(dateStr);
        return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
    };

    let waText = `Halo Admin Gedambaan Glamping,\n\nSaya ingin melakukan *Pemesanan Glamping* dengan detail sebagai berikut:\n\n` +
                 `👤 *Nama Pemesan*: ${customerName}\n` +
                 `⛺ *Unit Glamping*: ${activeBookingState.roomName} (${activeBookingState.roomCode})\n` +
                 `📅 *Check-In*: ${formatIndoDate(checkInDate)}\n` +
                 `📅 *Check-Out*: ${formatIndoDate(checkOutDate)} (${totalNights} Malam)\n`;

    if (selectedExtras.length > 0) {
        waText += `✨ *Extra Fasilitas*: ${selectedExtras.join(', ')}\n`;
    }

    waText += `\n💰 *Total Estimasi Biaya*: *Rp ${new Intl.NumberFormat('id-ID').format(grandTotal)}*\n\n` +
              `Mohon info ketersediaan dan instruksi pembayarannya. Terima kasih!`;

    // Clean WA Number
    let waPhone = activeBookingState.waNumber.replace(/[^0-9]/g, '');
    if (waPhone.startsWith('0')) {
        waPhone = '62' + waPhone.substring(1);
    }

    const waUrl = `https://wa.me/${waPhone}?text=${encodeURIComponent(waText)}`;
    
    closeBookingModal();
    window.open(waUrl, '_blank');
}

/**
 * Open Gallery Lightbox Modal
 */
function openGalleryModal(src, type, title) {
    const modal = document.getElementById('galleryModal');
    const container = document.getElementById('gallery_modal_content');
    document.getElementById('gallery_modal_title').innerText = title || 'Aset Foto Gedambaan Glamping';

    if (type === 'video') {
        container.innerHTML = `<video src="${src}" controls autoplay class="w-full max-h-[75vh] rounded-xl object-contain bg-black"></video>`;
    } else {
        container.innerHTML = `<img src="${src}" alt="${title}" class="w-full max-h-[75vh] rounded-xl object-contain">`;
    }

    if (modal) {
        modal.classList.add('active');
    }
}

/**
 * Close Gallery Modal
 */
function closeGalleryModal() {
    const modal = document.getElementById('galleryModal');
    if (modal) {
        modal.classList.remove('active');
        document.getElementById('gallery_modal_content').innerHTML = '';
    }
}
