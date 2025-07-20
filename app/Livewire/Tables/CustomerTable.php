<?php

namespace App\Livewire\Tables;

use App\Models\Customer;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerTable extends Component
{
    use WithPagination;
    // protected $paginationTheme = 'bootstrap';

    public $perPage = 15;

    public $search = '';

    public $sortField = 'name';

    public $sortAsc = 'desc';

    public function sortBy($field): void
    {
        if ($this->sortField === $field) {
            $this->sortAsc = !$this->sortAsc;
        } else {
            $this->sortAsc = true;
        }

        $this->sortField = $field;
    }

    public function updatingSearch()
    {
        $this->resetPage(); // Reset to the first page when search query changes
    }
    public function render()
    {
        if (auth()->user()->role == 'superAdmin') { 
            return view('livewire.tables.customer-table', [
                'customers' => Customer::with('orders', 'quotations', 'user')
                    ->search($this->search)
                    ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
                    ->paginate($this->perPage)
            ]);
        } elseif (auth()->user()->role == 'admin' and auth()->user()->wearhouse_id == 1) {
            return view('livewire.tables.customer-table', [
                'customers' => Customer::with('orders', 'quotations', 'user')
                    ->whereHas('user', function ($query) {
                        $query->where('wearhouse_id', 1);
                    })
                    ->search($this->search)
                    ->orderBy($this->sortField ?? 'created_at', $this->sortAsc ? 'asc' : 'desc')
                    ->paginate($this->perPage)
            ]);
        } elseif (auth()->user()->role == 'admin' and auth()->user()->wearhouse_id == 2) {
            return view('livewire.tables.customer-table', [
                'customers' => Customer::with('orders', 'quotations', 'user')
                    ->whereHas('user', function ($query) {
                        $query->where('wearhouse_id', 2);
                    })
                    ->search($this->search)
                    ->orderBy($this->sortField ?? 'created_at', $this->sortAsc ? 'asc' : 'desc')
                    ->paginate($this->perPage)
            ]);
        } else {
            return view('livewire.tables.customer-table', [
                'customers' => Customer::with('orders', 'quotations', 'user')
                    ->where('user_id', auth()->user()->id)
                    ->search($this->search)
                    ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
                    ->paginate($this->perPage)
            ]);
        }
    }
}
