<div class="container-fluid py-4 bg-light min-vh-100">
    <!-- Success/Error Messages -->
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Header -->
    <div class="bg-primary text-white shadow rounded-3 p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-1">💰 Catatan Keuangan</h4>
                <p class="mb-0 opacity-75">Kelola keuangan Anda dengan mudah dan efisien</p>
            </div>
            <div class="d-flex gap-2">
                <a href="/app/statistics" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-chart-line"></i> Statistik
                </a>
                <button class="btn btn-warning btn-lg text-dark fw-bold" data-bs-toggle="modal" data-bs-target="#addFinanceModal">
                    <i class="fas fa-plus-circle me-2"></i> Tambah Catatan
                </button>
            </div>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="row mb-4 g-3">
        <div class="col-md-6">
            <div class="input-group input-group-lg shadow-sm">
                <span class="input-group-text bg-white border-end-0">
                    <i class="fas fa-search text-primary"></i>
                </span>
                <input type="text" class="form-control border-start-0 ps-0" placeholder="Cari catatan keuangan..." wire:model.live="search">
            </div>
        </div>
        <div class="col-md-3">
            <select class="form-select form-select-lg shadow-sm" wire:model.live="filterJenis">
                <option value=""> Semua Transaksi</option>
                <option value="pemasukan">💰 Pemasukan</option>
                <option value="pengeluaran"> Pengeluaran</option>
            </select>
        </div>
        <div class="col-md-2">
            <input type="date" class="form-control form-control-lg shadow-sm" wire:model.live="filterTanggalFrom" title="Filter berdasarkan tanggal">
        </div>
        <div class="col-md-1">
            <button class="btn btn-secondary btn-lg shadow-sm w-100" wire:click="resetFilters" title="Reset Filter">
                <i class="fas fa-refresh"></i>
            </button>
        </div>
    </div>

    <!-- Statistik Cards -->
    <div class="row mb-4 g-4">
        <div class="col-md-4">
            <div class="card border-0 bg-success text-white shadow h-100 overflow-hidden position-relative">
                <div class="position-absolute top-0 end-0 opacity-25 mt-2 me-2">
                    <i class="fas fa-arrow-up fa-4x"></i>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex flex-column">
                        <h6 class="text-white-50 text-uppercase mb-2">Total Pemasukan</h6>
                        <h3 class="fw-bold mb-0">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</h3>
                        <div class="mt-3 text-white-50">
                            <i class="fas fa-chart-line me-1"></i> Pendapatan
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 bg-danger text-white shadow h-100 overflow-hidden position-relative">
                <div class="position-absolute top-0 end-0 opacity-25 mt-2 me-2">
                    <i class="fas fa-arrow-down fa-4x"></i>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex flex-column">
                        <h6 class="text-white-50 text-uppercase mb-2">Total Pengeluaran</h6>
                        <h3 class="fw-bold mb-0">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</h3>
                        <div class="mt-3 text-white-50">
                            <i class="fas fa-shopping-cart me-1"></i> Pengeluaran
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 bg-primary text-white shadow h-100 overflow-hidden position-relative">
                <div class="position-absolute top-0 end-0 opacity-25 mt-2 me-2">
                    <i class="fas fa-wallet fa-4x"></i>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex flex-column">
                        <h6 class="text-white-50 text-uppercase mb-2">Saldo Saat Ini</h6>
                        <h3 class="fw-bold mb-0">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</h3>
                        <div class="mt-3 text-white-50">
                            <i class="fas fa-chart-pie me-1"></i> Total Saldo
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Transaksi -->
    <div class="card border-0 shadow">
        <div class="card-header bg-white p-4 border-0">
            <h5 class="mb-0 fw-bold">📋 Daftar Transaksi</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 px-4 py-3 fw-bold">Judul Transaksi</th>
                            <th class="border-0 py-3 fw-bold">Jenis</th>
                            <th class="border-0 py-3 fw-bold">Nominal</th>
                            <th class="border-0 py-3 fw-bold">Tanggal</th>
                            <th class="border-0 py-3 fw-bold text-center" style="width:160px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($finances as $finance)
                        <tr class="align-middle">
                            <td class="px-4 py-3">
                                <div class="fw-bold">{{ $finance->judul }}</div>
                            </td>
                            <td class="py-3">
                                @if($finance->jenis === 'pemasukan')
                                    <span class="badge bg-success px-3 py-2">
                                        <i class="fas fa-arrow-up me-1"></i> Pemasukan
                                    </span>
                                @else
                                    <span class="badge bg-danger px-3 py-2">
                                        <i class="fas fa-arrow-down me-1"></i> Pengeluaran
                                    </span>
                                @endif
                            </td>
                            <td class="py-3">
                                <span class="fw-bold {{ $finance->jenis === 'pemasukan' ? 'text-success' : 'text-danger' }}">
                                    Rp {{ number_format($finance->nominal, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="py-3">
                                <i class="fas fa-calendar-alt text-muted me-1"></i>
                                {{ \Carbon\Carbon::parse($finance->tanggal)->format('d M Y') }}
                            </td>
                            <td class="py-3 text-center">
                                <div class="btn-group">
                                    <button class="btn btn-warning btn-sm"
                                        wire:click="prepareEditFinance({{ $finance->id }})"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editFinanceModal">
                                        <i class="fas fa-edit me-1"></i> Edit
                                    </button>
                                    <a href="/app/finance/{{ $finance->id }}"
                                        class="btn btn-info btn-sm">
                                        <i class="fas fa-eye me-1"></i> Detail
                                    </a>
                                    <button class="btn btn-danger btn-sm"
                                        wire:click="prepareDeleteFinance({{ $finance->id }})"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteFinanceModal">
                                        <i class="fas fa-trash me-1"></i> Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="py-5">
                                    <i class="fas fa-receipt fa-4x text-muted mb-3"></i>
                                    <h5 class="fw-bold text-muted">Belum Ada Transaksi</h5>
                                    <p class="text-muted mb-0">Mulai catat transaksi keuangan Anda sekarang!</p>
                                    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addFinanceModal">
                                        <i class="fas fa-plus-circle me-2"></i> Tambah Transaksi Baru
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3">
            {{ $finances->links() }}
        </div>
    </div>

    <!-- ✅ Modal Add / Edit / Delete / Edit Cover -->
    <div>
        @include('components.modals.finances.add')
        @include('components.modals.finances.edit')
        @include('components.modals.finances.delete')
        @include('components.modals.finances.edit-cover')
    </div>
</div>
