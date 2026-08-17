@extends('admin.layouts.app')

@section('title', 'Catatan Pengeluaran')
@section('page_title', 'Catatan Pengeluaran Operasional')

@section('styles')
<style>
    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 1.25rem;
        margin-bottom: 1.5rem;
    }

    .stat-card {
        background-color: #ffffff;
        border-radius: 16px;
        padding: 1.25rem 1.5rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        display: flex;
        align-items: center;
        gap: 1.25rem;
    }

    .stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        flex-shrink: 0;
    }

    .icon-rose { background-color: #ffe4e6; color: #e11d48; }
    .icon-indigo { background-color: #e0e7ff; color: #4f46e5; }
    .icon-emerald { background-color: #dcfce7; color: #16a34a; }

    .stat-details h3 {
        font-size: 0.78rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-bottom: 0.25rem;
    }

    .stat-details .value {
        font-size: 1.35rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
    }

    /* Action Buttons Vertical Layout */
    .action-btns {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 0.35rem;
        width: 100%;
        max-width: 90px;
        margin: 0 auto;
    }

    .action-btns form {
        width: 100%;
        margin: 0;
    }

    .btn-edit {
        background-color: #e0e7ff;
        color: #4338ca;
        border: none;
        padding: 0.35rem 0.6rem;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
        transition: all 0.2s;
        width: 100%;
    }

    .btn-edit:hover {
        background-color: #c7d2fe;
    }

    .btn-delete {
        background-color: #fee2e2;
        color: #dc2626;
        border: none;
        padding: 0.35rem 0.6rem;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
        transition: all 0.2s;
        width: 100%;
    }

    .btn-delete:hover {
        background-color: #fca5a5;
    }

    /* Modal Backdrop & Card */
    .modal-backdrop {
        position: fixed;
        inset: 0;
        background-color: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transition: all 0.25s ease;
    }

    .modal-backdrop.show {
        opacity: 1;
        visibility: visible;
    }

    .modal-card {
        background: #ffffff;
        width: 100%;
        max-width: 540px;
        border-radius: 20px;
        padding: 1.75rem;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        transform: translateY(20px);
        transition: transform 0.25s ease;
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-backdrop.show .modal-card {
        transform: translateY(0);
    }

    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.25rem;
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 0.75rem;
    }

    .modal-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #0f172a;
    }

    .btn-close-modal {
        background: none;
        border: none;
        font-size: 1.25rem;
        color: #64748b;
        cursor: pointer;
    }
</style>
@endsection

@section('content')

<!-- Ringkasan Statistik Pengeluaran -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon icon-rose">
            <i class="fa-solid fa-wallet"></i>
        </div>
        <div class="stat-details">
            <h3>Total Pengeluaran (Filter Selected)</h3>
            <div class="value" style="color: #e11d48;">Rp {{ number_format($totalHarga, 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon icon-indigo">
            <i class="fa-solid fa-receipt"></i>
        </div>
        <div class="stat-details">
            <h3>Jumlah Catatan Transaksi</h3>
            <div class="value">{{ $totalCount }} Transaksi</div>
        </div>
    </div>
</div>

<!-- Card Utama Catatan Pengeluaran -->
<div class="card" style="width: 100%;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h2 class="card-title" style="margin-bottom: 0.25rem;">
                <i class="fa-solid fa-wallet" style="color: #e11d48;"></i>
                Daftar Catatan Pengeluaran
            </h2>
            <div style="font-size: 0.83rem; color: #64748b;">Kelola semua catatan biaya operasional & pengeluaran homestay.</div>
        </div>

        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
            <a href="{{ route('admin.expenses.report.pdf', ['month' => $selectedMonth, 'search' => request('search')]) }}" target="_blank" class="btn-submit" style="background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); text-decoration: none;">
                <i class="fa-solid fa-print"></i>
                <span>Cetak PDF</span>
            </a>
            <a href="{{ route('admin.expenses.report.excel', ['month' => $selectedMonth, 'search' => request('search')]) }}" class="btn-submit" style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); text-decoration: none;">
                <i class="fa-solid fa-file-excel"></i>
                <span>Excel / CSV</span>
            </a>
            <button type="button" class="btn-submit" onclick="openCreateExpenseModal()">
                <i class="fa-solid fa-plus-circle"></i>
                <span>Tambah Pengeluaran</span>
            </button>
        </div>
    </div>

    <!-- Filter Bar (Bulan & Pencarian) -->
    <form action="{{ route('admin.expenses.index') }}" method="GET" style="margin-bottom: 1.5rem; background: #f8fafc; padding: 1rem; border-radius: 12px; border: 1px solid #e2e8f0; display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; flex: 1;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <label style="font-size: 0.85rem; font-weight: 600; color: #475569; white-space: nowrap;">Filter Bulan:</label>
                <input type="month" name="month" value="{{ $selectedMonth }}" class="form-input" style="padding: 0.45rem 0.75rem; width: auto;" onchange="this.form.submit()">
            </div>

            <div style="display: flex; align-items: center; gap: 0.5rem; flex: 1; max-width: 320px;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama pengeluaran/keterangan..." class="form-input" style="padding: 0.45rem 0.75rem; width: 100%;">
                <button type="submit" class="btn-submit" style="padding: 0.45rem 0.85rem; background-color: #475569;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </div>
        </div>

        @if(request('month') || request('search'))
            <a href="{{ route('admin.expenses.index') }}" style="font-size: 0.82rem; color: #ef4444; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.3rem;">
                <i class="fa-solid fa-rotate-left"></i> Reset Filter
            </a>
        @endif
    </form>

    <!-- Table Data -->
    <div style="overflow-x: auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Nama Pengeluaran</th>
                    <th style="width: 140px;">Tanggal</th>
                    <th style="width: 160px;">Harga / Nominal</th>
                    <th>Keterangan</th>
                    <th style="text-align: center; width: 100px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $index => $expense)
                <tr>
                    <td>{{ $expenses->firstItem() + $index }}</td>
                    <td>
                        <div style="font-weight: 700; color: #0f172a;">{{ $expense->nama_pengeluaran }}</div>
                    </td>
                    <td>
                        <div style="font-weight: 600; color: #475569; font-size: 0.88rem;">
                            <i class="fa-regular fa-calendar" style="color: #94a3b8; margin-right: 4px;"></i>
                            {{ \Carbon\Carbon::parse($expense->tanggal)->format('d M Y') }}
                        </div>
                    </td>
                    <td>
                        <div style="font-weight: 800; color: #e11d48; font-size: 0.95rem;">
                            Rp {{ number_format($expense->harga, 0, ',', '.') }}
                        </div>
                    </td>
                    <td>
                        <span style="font-size: 0.85rem; color: #64748b;">{{ $expense->keterangan ?: '-' }}</span>
                    </td>
                    <td style="text-align: center;">
                        <div class="action-btns">
                            <button type="button" class="btn-edit" onclick="openEditExpenseModal({{ $expense->id }}, '{{ addslashes($expense->nama_pengeluaran) }}', '{{ $expense->tanggal->format('Y-m-d') }}', {{ $expense->harga }}, '{{ addslashes($expense->keterangan ?? '') }}')">
                                <i class="fa-solid fa-pen-to-square"></i>
                                <span>Edit</span>
                            </button>

                            <form action="{{ route('admin.expenses.destroy', $expense->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn-delete" onclick="confirmDelete(this, 'Apakah Anda yakin ingin menghapus catatan pengeluaran {{ addslashes($expense->nama_pengeluaran) }}?')">
                                    <i class="fa-solid fa-trash-can"></i>
                                    <span>Hapus</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 2.5rem 1rem; color: #94a3b8;">
                        <i class="fa-solid fa-receipt" style="font-size: 2.5rem; margin-bottom: 0.75rem; color: #cbd5e1; display: block;"></i>
                        Belum ada catatan pengeluaran untuk periode ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination Links -->
    @if($expenses->hasPages())
        <div style="margin-top: 1.25rem;">
            {{ $expenses->links() }}
        </div>
    @endif
</div>

<!-- Modal Tambah Catatan Pengeluaran -->
<div class="modal-backdrop" id="createExpenseModal">
    <div class="modal-card">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fa-solid fa-plus-circle" style="color: #e11d48; margin-right: 6px;"></i>
                Tambah Catatan Pengeluaran
            </h3>
            <button type="button" class="btn-close-modal" onclick="closeCreateExpenseModal()">&times;</button>
        </div>

        <form action="{{ route('admin.expenses.store') }}" method="POST">
            @csrf
            <div class="form-group" style="margin-bottom: 1.15rem;">
                <label class="form-label">Nama Pengeluaran <span style="color: #ef4444;">*</span></label>
                <input type="text" name="nama_pengeluaran" class="form-input" placeholder="Contoh: Pembelian Token Listrik Homestay" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.15rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Tanggal <span style="color: #ef4444;">*</span></label>
                    <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" class="form-input" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Harga / Nominal (Rp) <span style="color: #ef4444;">*</span></label>
                    <input type="number" name="harga" min="0" step="1" class="form-input" placeholder="Contoh: 250000" required>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label">Keterangan (Opsional)</label>
                <textarea name="keterangan" rows="3" class="form-input" placeholder="Tuliskan catatan tambahan mengenai pengeluaran ini..."></textarea>
            </div>

            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn-submit" style="background-color: #94a3b8;" onclick="closeCreateExpenseModal()">Batal</button>
                <button type="submit" class="btn-submit" style="background-color: #e11d48;">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Catatan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Catatan Pengeluaran -->
<div class="modal-backdrop" id="editExpenseModal">
    <div class="modal-card">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fa-solid fa-pen-to-square" style="color: #4338ca; margin-right: 6px;"></i>
                Edit Catatan Pengeluaran
            </h3>
            <button type="button" class="btn-close-modal" onclick="closeEditExpenseModal()">&times;</button>
        </div>

        <form id="editExpenseForm" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group" style="margin-bottom: 1.15rem;">
                <label class="form-label">Nama Pengeluaran <span style="color: #ef4444;">*</span></label>
                <input type="text" id="edit_nama_pengeluaran" name="nama_pengeluaran" class="form-input" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.15rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Tanggal <span style="color: #ef4444;">*</span></label>
                    <input type="date" id="edit_tanggal" name="tanggal" class="form-input" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Harga / Nominal (Rp) <span style="color: #ef4444;">*</span></label>
                    <input type="number" id="edit_harga" name="harga" min="0" step="1" class="form-input" required>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label">Keterangan (Opsional)</label>
                <textarea id="edit_keterangan" name="keterangan" rows="3" class="form-input"></textarea>
            </div>

            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn-submit" style="background-color: #94a3b8;" onclick="closeEditExpenseModal()">Batal</button>
                <button type="submit" class="btn-submit" style="background-color: #4338ca;">
                    <i class="fa-solid fa-floppy-disk"></i> Perbarui Catatan
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openCreateExpenseModal() {
        document.getElementById('createExpenseModal').classList.add('show');
    }

    function closeCreateExpenseModal() {
        document.getElementById('createExpenseModal').classList.remove('show');
    }

    function openEditExpenseModal(id, nama, tanggal, harga, keterangan) {
        const form = document.getElementById('editExpenseForm');
        form.action = "{{ url('admin/expenses') }}/" + id;

        document.getElementById('edit_nama_pengeluaran').value = nama;
        document.getElementById('edit_tanggal').value = tanggal;
        document.getElementById('edit_harga').value = harga;
        document.getElementById('edit_keterangan').value = keterangan;

        document.getElementById('editExpenseModal').classList.add('show');
    }

    function closeEditExpenseModal() {
        document.getElementById('editExpenseModal').classList.remove('show');
    }

    function confirmDelete(button, message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    button.closest('form').submit();
                }
            });
        } else {
            if (confirm(message)) {
                button.closest('form').submit();
            }
        }
    }

    // Close modals on backdrop click
    window.addEventListener('click', function(e) {
        const createModal = document.getElementById('createExpenseModal');
        const editModal = document.getElementById('editExpenseModal');
        if (e.target === createModal) closeCreateExpenseModal();
        if (e.target === editModal) closeEditExpenseModal();
    });
</script>
@endsection
