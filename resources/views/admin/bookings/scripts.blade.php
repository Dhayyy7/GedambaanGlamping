<script>
    function openCreateBookingModal() {
        updateSelectedRoomsUI();
        document.getElementById('createBookingModal').classList.add('show');
    }

    function closeCreateBookingModal() {
        document.getElementById('createBookingModal').classList.remove('show');
    }

    function updateSelectedRoomsUI() {
        const checkboxes = document.querySelectorAll('.create-room-checkbox');
        let count = 0;
        checkboxes.forEach(cb => {
            const card = cb.closest('.room-checkbox-card');
            if (cb.checked) {
                count++;
                if (card) {
                    card.style.borderColor = '#4f46e5';
                    card.style.backgroundColor = '#eef2ff';
                }
            } else if (card) {
                card.style.borderColor = '#e2e8f0';
                card.style.backgroundColor = '#ffffff';
            }
        });

        const badge = document.getElementById('selected_rooms_badge');
        if (badge) {
            badge.innerText = `${count} Kamar Dipilih`;
            badge.style.backgroundColor = count > 0 ? '#dcfce7' : '#e0e7ff';
            badge.style.color = count > 0 ? '#166534' : '#4338ca';
        }

        const errEl = document.getElementById('room_selection_error');
        if (errEl && count > 0) {
            errEl.style.display = 'none';
        }
    }

    function toggleAllRooms(checked) {
        document.querySelectorAll('.create-room-checkbox').forEach(cb => {
            cb.checked = checked;
        });
        updateSelectedRoomsUI();
    }

    function validateCreateBookingForm() {
        const checkedCount = document.querySelectorAll('.create-room-checkbox:checked').length;
        const errEl = document.getElementById('room_selection_error');
        if (checkedCount === 0) {
            if (errEl) errEl.style.display = 'block';
            return false;
        }
        if (errEl) errEl.style.display = 'none';
        return true;
    }

    function openEditModal(booking, room) {
        const form = document.getElementById('editForm');
        form.action = `/admin/bookings/${booking.id}`;

        document.getElementById('edit_status').value = booking.status;
        document.getElementById('edit_room_id').value = booking.room_id;
        document.getElementById('edit_customer_name').value = booking.customer_name;
        document.getElementById('edit_customer_phone').value = booking.customer_phone;
        document.getElementById('edit_customer_sosmed').value = booking.customer_sosmed ?? '';
        document.getElementById('edit_customer_address').value = booking.customer_address;
        document.getElementById('edit_admin_discount').value = booking.admin_discount ?? 0;
        
        // Format dates YYYY-MM-DD
        const checkIn = booking.check_in_date.split('T')[0];
        const checkOut = booking.check_out_date.split('T')[0];
        
        document.getElementById('edit_check_in_date').value = checkIn;
        document.getElementById('edit_check_out_date').value = checkOut;

        // Check selected extra facilities
        const selectedExtraIds = (booking.extra_facilities && Array.isArray(booking.extra_facilities))
            ? booking.extra_facilities.map(item => parseInt(item.id))
            : [];
        document.querySelectorAll('.edit-extra-facility-checkbox').forEach(cb => {
            cb.checked = selectedExtraIds.includes(parseInt(cb.value));
        });

        document.getElementById('editModal').classList.add('show');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.remove('show');
    }

    function openPreviewModal(booking, room) {
        document.getElementById('preview_booking_code').innerText = booking.booking_code;
        document.getElementById('preview_customer_name').innerText = booking.customer_name;
        document.getElementById('preview_customer_phone').innerText = booking.customer_phone;
        document.getElementById('preview_customer_sosmed').innerText = booking.customer_sosmed ? booking.customer_sosmed : '-';
        document.getElementById('preview_customer_address').innerText = booking.customer_address;
        document.getElementById('preview_room_name').innerText = room ? `${room.code} - ${room.name}` : '-';

        // WA Direct Link
        const cleanPhone = booking.customer_phone.replace(/[^0-9]/g, '');
        const formattedPhone = cleanPhone.startsWith('0') ? '62' + cleanPhone.substring(1) : cleanPhone;
        const waText = encodeURIComponent(`Halo Kak ${booking.customer_name}, mengenai pemesanan Kamar ${room ? room.name : ''} (${booking.booking_code})...`);
        document.getElementById('preview_wa_link').href = `https://wa.me/${formattedPhone}?text=${waText}`;

        // Print Nota Link
        const notaBtn = document.getElementById('preview_print_nota_btn');
        if (notaBtn) {
            if ([2, 3, 4].includes(booking.status)) {
                notaBtn.href = `/admin/bookings/${booking.id}/receipt`;
                notaBtn.style.display = 'inline-flex';
            } else {
                notaBtn.style.display = 'none';
            }
        }

        // Format dates
        const checkInDate = new Date(booking.check_in_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        const checkOutDate = new Date(booking.check_out_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        
        document.getElementById('preview_check_in').innerText = checkInDate;
        document.getElementById('preview_check_out').innerText = checkOutDate;
        document.getElementById('preview_total_nights').innerText = `${booking.total_nights} Malam`;

        // Render Extra Facilities
        const extraContainer = document.getElementById('preview_extra_facilities_container');
        extraContainer.innerHTML = '';

        let extraSubtotal = 0;
        if (booking.extra_facilities && Array.isArray(booking.extra_facilities) && booking.extra_facilities.length > 0) {
            booking.extra_facilities.forEach(ef => {
                extraSubtotal += parseFloat(ef.price || 0);
                const badge = document.createElement('span');
                badge.style.backgroundColor = '#e0e7ff';
                badge.style.color = '#4338ca';
                badge.style.padding = '0.3rem 0.65rem';
                badge.style.borderRadius = '20px';
                badge.style.fontSize = '0.78rem';
                badge.style.fontWeight = '600';
                badge.innerHTML = `<i class="fa-solid fa-square-plus" style="margin-right: 0.3rem;"></i> ${ef.name} (+Rp ${new Intl.NumberFormat('id-ID').format(ef.price)})`;
                extraContainer.appendChild(badge);
            });
        } else {
            extraContainer.innerHTML = '<span style="color: #94a3b8; font-style: italic; font-size: 0.85rem;">Tidak ada extra fasilitas.</span>';
        }

        // Subtotal calculations
        const roomSubtotal = parseFloat(booking.total_price || 0) - extraSubtotal;

        document.getElementById('preview_room_subtotal').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(roomSubtotal > 0 ? roomSubtotal : 0);
        document.getElementById('preview_extra_subtotal').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(extraSubtotal);
        document.getElementById('preview_discount_percent').innerText = (booking.discount && booking.discount > 0) ? `${booking.discount}% OFF` : 'Tidak Ada';
        document.getElementById('preview_admin_discount_percent').innerText = (booking.admin_discount && booking.admin_discount > 0) ? `${booking.admin_discount}% OFF` : 'Tidak Ada';
        document.getElementById('preview_total_price').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(booking.total_price);

        // Render Status Badge
        const statusBadge = document.getElementById('preview_status_badge');
        if (booking.status === 4) {
            statusBadge.innerHTML = '<span style="background-color: #f3e8ff; color: #7e22ce; padding: 0.25rem 0.65rem; border-radius: 20px; font-weight: 700; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 0.35rem;"><i class="fa-solid fa-coins"></i> DP 50% (Panjar)</span>';
        } else if (booking.status === 3) {
            statusBadge.innerHTML = '<span style="background-color: #dbeafe; color: #1e40af; padding: 0.25rem 0.65rem; border-radius: 20px; font-weight: 700; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 0.35rem;"><i class="fa-solid fa-flag-checkered"></i> Selesai (Completed)</span>';
        } else if (booking.status === 2) {
            statusBadge.innerHTML = '<span style="background-color: #dcfce7; color: #166534; padding: 0.25rem 0.65rem; border-radius: 20px; font-weight: 700; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 0.35rem;"><i class="fa-solid fa-circle-check"></i> Lunas / Terkonfirmasi</span>';
        } else if (booking.status === 1) {
            statusBadge.innerHTML = '<span style="background-color: #fef3c7; color: #b45309; padding: 0.25rem 0.65rem; border-radius: 20px; font-weight: 700; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 0.35rem;"><i class="fa-solid fa-clock"></i> Pending</span>';
        } else {
            statusBadge.innerHTML = '<span style="background-color: #fee2e2; color: #991b1b; padding: 0.25rem 0.65rem; border-radius: 20px; font-weight: 700; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 0.35rem;"><i class="fa-solid fa-circle-xmark"></i> Dibatalkan / Expired</span>';
        }

        document.getElementById('previewModal').classList.add('show');
    }

    function closePreviewModal() {
        document.getElementById('previewModal').classList.remove('show');
    }

    $(document).ready(function() {
        updateSelectedRoomsUI();

        if ($('#status_filter').length) {
            $('#status_filter').select2({
                placeholder: '-- Semua Status --',
                allowClear: true,
                minimumResultsForSearch: Infinity
            }).on('change', function() {
                $('#booking-filter-form').submit();
            });
        }
    });
</script>
