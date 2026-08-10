<!-- Modal Edit Kamar -->
<div class="modal-backdrop" id="editModal">
    <div class="modal-card">
        <div class="modal-header">
            <h3 class="modal-title">Edit Data Kamar / Unit</h3>
            <button type="button" class="btn-close-modal" onclick="closeEditModal()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="editForm" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="edit_code" class="form-label">Kode Kamar</label>
                <input type="text" id="edit_code" name="code" class="form-input" required>
            </div>

            <div class="form-group">
                <label for="edit_name" class="form-label">Nama Kamar / Unit</label>
                <input type="text" id="edit_name" name="name" class="form-input" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="edit_price" class="form-label">Harga Weekday</label>
                    <input type="number" id="edit_price" name="price" class="form-input" min="0" required>
                </div>
                <div class="form-group">
                    <label for="edit_weekend_price" class="form-label">Harga Weekend & Libur</label>
                    <input type="number" id="edit_weekend_price" name="weekend_price" class="form-input" min="0" placeholder="Opsional">
                </div>
            </div>

            <div class="form-group">
                <label for="edit_discount" class="form-label">Diskon (%)</label>
                <input type="number" id="edit_discount" name="discount" class="form-input" min="0" max="100" step="0.1">
            </div>

            <div class="form-group">
                <label for="edit_description" class="form-label">Keterangan / Deskripsi Kamar <span style="font-weight: 400; color: #64748b;">(Opsional)</span></label>
                <textarea id="edit_description" name="description" class="form-input" rows="3" placeholder="Masukan keterangan atau deskripsi rinci kamar/unit..." style="resize: vertical;"></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Fasilitas Kamar (Pilih Banyak)</label>
                <div class="facility-checkbox-grid">
                    @foreach($facilities as $f)
                    <label class="facility-checkbox-item">
                        <input type="checkbox" name="facilities[]" value="{{ $f->id }}" class="edit-facility-checkbox" id="edit_facility_{{ $f->id }}">
                        <span>{{ $f->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <!-- Existing Photos Preview Grid with Delete Buttons -->
            <div class="form-group">
                <label class="form-label">Foto Kamar Saat Ini <span style="font-weight: 400; color: #64748b;">(Klik tombol sampah untuk menghapus foto)</span></label>
                <div id="edit_existing_images_container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(95px, 1fr)); gap: 0.75rem; background: #f8fafc; padding: 0.85rem; border-radius: 12px; border: 1px solid #e2e8f0; min-height: 50px;">
                    <!-- Rendered by JS -->
                </div>
                <div id="edit_deleted_inputs_container">
                    <!-- Hidden inputs for deleted_images[] -->
                </div>
            </div>

            <div class="form-group">
                <label for="edit_images" class="form-label">Unggah Foto Baru <span style="font-weight: 400; color: #64748b;">(Maksimal 10 Foto)</span></label>
                <input type="file" id="edit_images" name="images[]" class="form-input" multiple accept="image/*">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
                <button type="button" class="btn-edit" style="background-color: #f1f5f9; color: #475569; width: auto; padding: 0.6rem 1.25rem;" onclick="closeEditModal()">
                    Batal
                </button>
                <button type="submit" class="btn-submit">
                    <i class="fa-solid fa-save"></i>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</div>
