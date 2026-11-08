<!-- Modal Input Pemesanan Baru -->
<div class="modal-backdrop" id="createBookingModal">
    <div class="modal-card" style="max-width: 580px;">
        <div class="modal-header">
            <h3 class="modal-title">Input Pemesanan Baru</h3>
            <button type="button" class="btn-close-modal" onclick="closeCreateBookingModal()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('admin.bookings.store') }}" method="POST" id="createBookingForm" onsubmit="return validateCreateBookingForm()">
            @csrf

            <div class="form-group">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.45rem; flex-wrap: wrap; gap: 0.5rem;">
                    <label class="form-label" style="margin-bottom: 0;">
                        Pilih Kamar / Unit <span style="font-weight: 400; color: #64748b;">(Bisa pilih lebih dari 1 kamar sekaligus)</span>
                    </label>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span id="selected_rooms_badge" style="background-color: #e0e7ff; color: #4338ca; padding: 0.15rem 0.55rem; border-radius: 20px; font-size: 0.75rem; font-weight: 700;">
                            0 Kamar Dipilih
                        </span>
                        <button type="button" onclick="toggleAllRooms(true)" style="background: #f1f5f9; border: 1px solid #cbd5e1; color: #334155; border-radius: 6px; padding: 0.15rem 0.5rem; font-size: 0.72rem; font-weight: 600; cursor: pointer;">
                            Pilih Semua
                        </button>
                        <button type="button" onclick="toggleAllRooms(false)" style="background: #f1f5f9; border: 1px solid #cbd5e1; color: #64748b; border-radius: 6px; padding: 0.15rem 0.5rem; font-size: 0.72rem; font-weight: 600; cursor: pointer;">
                            Reset
                        </button>
                    </div>
                </div>

                @php
                    $oldRoomIds = old('room_ids', old('room_id') ? [old('room_id')] : []);
                @endphp

                <div style="display: flex; flex-direction: column; gap: 0.45rem; background: #f8fafc; padding: 0.75rem; border-radius: 12px; border: 1px solid #e2e8f0; max-height: 210px; overflow-y: auto;">
                    @foreach($rooms as $r)
                        @php
                            $isChecked = in_array($r->id, (array) $oldRoomIds);
                        @endphp
                        <label class="room-checkbox-card" style="display: flex; align-items: center; justify-content: space-between; gap: 0.65rem; padding: 0.55rem 0.75rem; border-radius: 10px; border: 1px solid {{ $isChecked ? '#4f46e5' : '#e2e8f0' }}; background: {{ $isChecked ? '#eef2ff' : '#ffffff' }}; cursor: pointer; transition: all 0.15s ease;">
                            <div style="display: flex; align-items: center; gap: 0.6rem;">
                                <input type="checkbox" name="room_ids[]" value="{{ $r->id }}" class="create-room-checkbox" onchange="updateSelectedRoomsUI()" {{ $isChecked ? 'checked' : '' }} style="accent-color: #4f46e5; width: 17px; height: 17px; cursor: pointer;">
                                <div>
                                    <div style="font-weight: 700; font-size: 0.85rem; color: #0f172a;">
                                        <span class="badge-code" style="font-size: 0.72rem; margin-right: 0.25rem;">{{ $r->code }}</span>
                                        {{ $r->name }}
                                    </div>
                                    <div style="font-size: 0.74rem; color: #64748b; margin-top: 0.1rem;">
                                        Weekday: <strong>Rp {{ number_format($r->final_price, 0, ',', '.') }}</strong>
                                        @if($r->weekend_price && $r->weekend_price > 0)
                                            &bull; Weekend: <strong>Rp {{ number_format($r->final_weekend_price, 0, ',', '.') }}</strong>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
                <div id="room_selection_error" style="display: none; color: #dc2626; font-size: 0.78rem; font-weight: 600; margin-top: 0.35rem;">
                    <i class="fa-solid fa-circle-exclamation"></i> Silakan centang minimal 1 kamar yang ingin dipesan.
                </div>
            </div>

            <div class="form-group">
                <label for="customer_name" class="form-label">Nama Pemesan</label>
                <input type="text" id="customer_name" name="customer_name" class="form-input" placeholder="Masukan Nama Pemesan" value="{{ old('customer_name') }}" required>
            </div>

            <div class="form-group">
                <label for="customer_phone" class="form-label">No. HP / WhatsApp</label>
                <input type="text" id="customer_phone" name="customer_phone" class="form-input" placeholder="Misal: 081234567890" value="{{ old('customer_phone') }}" required>
            </div>

            <div class="form-group">
                <label for="customer_sosmed" class="form-label">Media Sosial Pemesan <span style="font-weight: 400; color: #64748b;">(Opsional)</span></label>
                <input type="text" id="customer_sosmed" name="customer_sosmed" class="form-input" placeholder="Misal: @username (IG/FB/TikTok)" value="{{ old('customer_sosmed') }}">
            </div>

            <div class="form-group">
                <label for="customer_address" class="form-label">Alamat Pemesan</label>
                <textarea id="customer_address" name="customer_address" rows="2" class="form-textarea" placeholder="Masukan Alamat Lengkap Pemesan" required>{{ old('customer_address') }}</textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                <div class="form-group">
                    <label for="check_in_date" class="form-label">Check-In</label>
                    <input type="date" id="check_in_date" name="check_in_date" class="form-input" value="{{ old('check_in_date', date('Y-m-d')) }}" required>
                </div>
                <div class="form-group">
                    <label for="check_out_date" class="form-label">Check-Out</label>
                    <input type="date" id="check_out_date" name="check_out_date" class="form-input" value="{{ old('check_out_date', date('Y-m-d', strtotime('+1 day'))) }}" required>
                </div>
            </div>

            <div class="form-group">
                <label for="admin_discount" class="form-label">Diskon Admin (%) <span style="font-weight: 400; color: #64748b;">(Khusus Potongan Kamar / Opsional)</span></label>
                <input type="number" id="admin_discount" name="admin_discount" class="form-input" placeholder="Misal: 10 (artinya diskon 10% untuk kamar)" min="0" max="100" step="0.1" value="{{ old('admin_discount', 0) }}">
            </div>

            <div class="form-group">
                <label class="form-label">Extra Fasilitas <span style="font-weight: 400; color: #64748b;">(Pilih Banyak / Opsional)</span></label>
                <div class="facility-checkbox-grid">
                    @foreach($extraFacilities as $ef)
                    <label class="facility-checkbox-item">
                        <input type="checkbox" name="extra_facility_ids[]" value="{{ $ef->id }}">
                        <span>{{ $ef->name }} (+Rp {{ number_format($ef->price, 0, ',', '.') }})</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="form-group">
                <label for="status" class="form-label">Status Awal Pemesanan</label>
                <select id="status" name="status" class="form-select">
                    <option value="1" selected>🟡 Pending (Menunggu Bayar WA - Kunci 2 Jam)</option>
                    <option value="4">🟣 DP 50% (Panjar 50% - Terkonfirmasi Kunci)</option>
                    <option value="2">🟢 Lunas (Terkonfirmasi - Langsung Kunci)</option>
                </select>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
                <button type="button" class="btn-edit" style="background-color: #f1f5f9; color: #475569; width: auto; padding: 0.6rem 1.25rem;" onclick="closeCreateBookingModal()">
                    Batal
                </button>
                <button type="submit" class="btn-submit">
                    <i class="fa-solid fa-save"></i>
                    <span>Simpan Pemesanan</span>
                </button>
            </div>
        </form>
    </div>
</div>
