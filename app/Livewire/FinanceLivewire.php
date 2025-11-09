<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Models\Finance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FinanceLivewire extends Component
{
    use WithFileUploads, WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $filterJenis = '';
    public $filterTanggalFrom = ''; // Akan digunakan untuk filter tanggal spesifik
    public $auth;

    // Form ADD
    public $addJudul, $addNominal, $addJenis, $addTanggal, $addCover;

    // Form EDIT
    public $editFinanceId, $editJudul, $editNominal, $editJenis, $editTanggal;

    // Form DELETE
    public $deleteFinanceId, $deleteFinanceTitle, $deleteConfirmTitle;

    public $editCoverFinanceFile;

    public function mount()
    {
        $this->auth = Auth::user();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterJenis()
    {
        $this->resetPage();
    }

    public function updatingFilterTanggalFrom()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->filterJenis = '';
        $this->filterTanggalFrom = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = Finance::where('user_id', Auth::id());

        if ($this->search) {
            $query->where('judul', 'like', '%' . $this->search . '%');
        }

        if ($this->filterJenis) {
            $query->where('jenis', $this->filterJenis);
        }

        // Mengubah logika filter tanggal untuk mencari tanggal spesifik
        if ($this->filterTanggalFrom) { 
            $query->whereDate('tanggal', $this->filterTanggalFrom);
        }

        $finances = $query->orderBy('tanggal', 'desc')
                          ->paginate(20);

        $totalPemasukan = Finance::where('user_id', Auth::id())
            ->where('jenis', 'pemasukan')
            ->sum('nominal');

        $totalPengeluaran = Finance::where('user_id', Auth::id())
            ->where('jenis', 'pengeluaran')
            ->sum('nominal');

        $totalSaldo = $totalPemasukan - $totalPengeluaran;

        // Data untuk Grafik Pemasukan & Pengeluaran per Bulan
        $currentYear = date('Y');
        $pemasukanPerBulan = Finance::where('user_id', Auth::id())
            ->where('jenis', 'pemasukan')->whereYear('tanggal', $currentYear)
            ->selectRaw('EXTRACT(MONTH FROM tanggal) as bulan, SUM(nominal) as total')
            ->groupBy('bulan')->pluck('total', 'bulan')->all();

        $pengeluaranPerBulan = Finance::where('user_id', Auth::id())
            ->where('jenis', 'pengeluaran')->whereYear('tanggal', $currentYear)
            ->selectRaw('EXTRACT(MONTH FROM tanggal) as bulan, SUM(nominal) as total')
            ->groupBy('bulan')->pluck('total', 'bulan')->all();

        $chartData = [];
        $chartData['pemasukan'] = array_map(fn($m) => $pemasukanPerBulan[$m] ?? 0, range(1, 12));
        $chartData['pengeluaran'] = array_map(fn($m) => $pengeluaranPerBulan[$m] ?? 0, range(1, 12));

        return view('livewire.finance-livewire', [
            'finances' => $finances,
            'totalPemasukan' => $totalPemasukan,
            'totalPengeluaran' => $totalPengeluaran,
            'totalSaldo' => $totalSaldo,
            'chartData' => $chartData,
        ]);
    }

    // ADD
    public function addFinance()
    {
        $this->validate([
            'addJudul' => 'required|string|max:255',
            'addNominal' => 'required|integer',
            'addJenis' => 'required|in:pemasukan,pengeluaran',
            'addTanggal' => 'required|date',
            'addCover' => 'nullable|image|max:2048',
        ]);

        $path = $this->addCover ? $this->addCover->store('finance_covers', 'public') : null;

        Finance::create([
            'user_id' => $this->auth->id,
            'judul' => $this->addJudul,
            'nominal' => $this->addNominal,
            'jenis' => $this->addJenis,
            'tanggal' => $this->addTanggal,
            'cover' => $path,
        ]);

        $this->reset(['addJudul', 'addNominal', 'addJenis', 'addTanggal', 'addCover']);

        $this->dispatch('closeModal', id: 'addFinanceModal');

        return redirect('/app/home')->with('success', 'Data berhasil ditambahkan!');
    }

    // PREP EDIT
    public function prepareEditFinance($id)
    {
        $finance = Finance::where('id', $id)
            ->where('user_id', $this->auth->id)
            ->first();

        if (!$finance) return;

        $this->editFinanceId = $finance->id;
        $this->editJudul = $finance->judul;
        $this->editNominal = $finance->nominal;
        $this->editJenis = $finance->jenis;
        $this->editTanggal = \Carbon\Carbon::parse($finance->tanggal)->format('Y-m-d');

        $this->dispatch('showModal', id: 'editFinanceModal');
    }

    // EDIT
    public function editFinance()
    {
        $this->validate([
            'editJudul' => 'required|string|max:255',
            'editNominal' => 'required|integer',
            'editJenis' => 'required|in:pemasukan,pengeluaran',
            'editTanggal' => 'required|date',
        ]);

        $finance = Finance::where('id', $this->editFinanceId)
            ->where('user_id', $this->auth->id)
            ->first();
        if (!$finance) return; // Data tidak ditemukan atau bukan milik user

        $finance->update([
            'judul' => $this->editJudul,
            'nominal' => $this->editNominal,
            'jenis' => $this->editJenis,
            'tanggal' => $this->editTanggal,
        ]);

        $this->reset(['editFinanceId', 'editJudul', 'editNominal', 'editJenis', 'editTanggal']);
        $this->dispatch('closeModal', id: 'editFinanceModal');
        $this->dispatch('show-toast', message: 'Data berhasil diubah!');
    }

    // PREP DELETE
    public function prepareDeleteFinance($id)
    {
        $finance = Finance::where('id', $id)
            ->where('user_id', $this->auth->id)
            ->first();
        if (!$finance) return; // Data tidak ditemukan atau bukan milik user

        $this->deleteFinanceId = $finance->id;
        $this->deleteFinanceTitle = $finance->judul;

        $this->dispatch('showModal', id: 'deleteFinanceModal');
    }

    // DELETE
    public function deleteFinance()
    {
        if ($this->deleteConfirmTitle !== $this->deleteFinanceTitle) {
            $this->addError('deleteConfirmTitle', 'Judul konfirmasi tidak sesuai.');
            return;
        }

        $finance = Finance::where('id', $this->deleteFinanceId)
            ->where('user_id', $this->auth->id)
            ->first();

        if ($finance) {
            $finance->delete();
            $this->dispatch('show-toast', message: 'Data berhasil dihapus!');
        } else {
            $this->dispatch('show-toast', message: 'Data gagal dihapus!', error: true);
        }
        $this->reset(['deleteFinanceId', 'deleteFinanceTitle', 'deleteConfirmTitle']);
        $this->dispatch('closeModal', id: 'deleteFinanceModal');
    }

    // EDIT COVER
    public function editFinanceCover()
    {
        $this->validate([
            'editCoverFinanceFile' => 'required|image|max:2048',
        ]);

        $finance = Finance::where('id', $this->editFinanceId)
            ->where('user_id', $this->auth->id)
            ->first();
        if (!$finance) return; // Data tidak ditemukan atau bukan milik user

        if ($finance->cover && Storage::disk('public')->exists($finance->cover)) {
            Storage::disk('public')->delete($finance->cover);
        }

        $path = $this->editCoverFinanceFile->store('finance_covers', 'public');

        $finance->update(['cover' => $path]);

        $this->reset(['editCoverFinanceFile']);
        session()->flash('success', 'Cover berhasil diubah!');
        $this->dispatch('closeModal', id: 'editFinanceCoverModal');
    }
}
