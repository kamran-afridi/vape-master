<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Customer;
use App\Models\User;

class SwapCustomers extends Component
{
    use WithPagination;

    public $userid = '';
    public $targetUserId = null;
    public $selectedCustomers = [];
    public $selectAll = false;

    public $sortField = 'id';
    public $sortAsc = true;
    public $perPage = 15;
    public $search = '';

    public $columns = [
        'payment' => true,
        'payto' => true,
        'customer' => true,
        'status' => true,
        'actions' => true,
    ];

    public function mount()
    {
        $this->userid = session('UserId', '');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedUserid($value)
    {
        $this->userid = $value;
        $this->resetSelection();
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedCustomers = Customer::query()
                ->when($this->userid, fn($query) => $query->where('user_id', $this->userid))
                ->pluck('id')
                ->toArray();
        } else {
            $this->selectedCustomers = [];
        }
    }

    public function moveSelectedCustomers()
    {
        if (!$this->targetUserId || empty($this->selectedCustomers)) {
            session()->flash('error', 'Please select customers and a user to move to.');
            return;
        }

        Customer::whereIn('id', $this->selectedCustomers)->update([
            'user_id' => $this->targetUserId,
        ]);

        $this->resetSelection();
        session()->flash('success', 'Selected customers moved successfully!');
    }

    public function resetSelection()
    {
        $this->selectedCustomers = [];
        $this->selectAll = false;
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortAsc = !$this->sortAsc;
        } else {
            $this->sortField = $field;
            $this->sortAsc = true;
        }
    }

    public function render()
    {
        $users = User::select('id', 'name', 'email')->get();

        $customers = Customer::query()
            ->when($this->userid, fn($query) => $query->where('user_id', $this->userid))
            ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc');

        if ($this->search) {
            $customers = $customers->search($this->search);
        }
        if ($this->perPage > 0) {
            $customers = $customers->paginate($this->perPage);
        }

        return view('livewire.swap-customers', [
            'users' => $users,
            'customers' => $customers,
        ]);
    }
}
