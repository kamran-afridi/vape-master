<?php

namespace App\Livewire\Tables;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderTable extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $perPage = 15;
    public $changeEvents;
    public $search = '';
    public $customerid = '';

    public $sortField = 'id';

    public $sortAsc = false;
    public $userid;

    public function mount()
    {
        $this->userid = session('UserId', default: '');
        $this->customerid = session('customerId', default: ''); 
    }

    public function updatedUserid($value)
    {
        // Session::put('UserId', $value); 
        $this->resetPage();
        Session::put('UserId', $value);
        $this->userid = $value;
    }

    public function updatedCustomerid($value)
    {
        $this->resetPage(); 
        Session::put('customerId', $value);
        $this->customerid = $value;
    }
    public function sortBy($field): void
    {
        if ($this->sortField === $field) {
            $this->sortAsc = !$this->sortAsc;
        } else {
            $this->sortAsc = true;
        }

        $this->sortField = $field;
    }

    public function exportCsv(): StreamedResponse
    {
        $fileName = 'orders_' . now()->format('Ymd_His') . '.csv';

        $orders = $this->getExportOrders();

        return response()->streamDownload(function () use ($orders) {
            $handle = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($handle, ['Invoice No', 'Customer', 'Date', 'Payment Type', 'Total', 'Paid', 'Pay To', 'User', 'Status']);

            foreach ($orders as $order) {
                fputcsv($handle, [$order->invoice_no, $order->customer->name ?? '', optional($order->order_date)->format('d-m-Y'), $order->payment_type, $order->total, $order->pay, $order->payto, $order->user->name ?? '', $order->order_status->label() ?? '']);
            }

            fclose($handle);
        }, $fileName);
    }

    protected function getExportOrders()
    {
        $query = Order::with(['customer', 'details', 'user']);

        if (auth()->user()->role === 'admin') {
            $query->whereHas('user', function ($q) {
                $q->where('wearhouse_id', auth()->user()->wearhouse_id);
            });
        }

        if ($this->userid && $this->userid !== 'all') {
            $query->where('user_id', $this->userid);
        }

        if ($this->customerid && $this->customerid !== 'all') {
            $query->where('customer_id', $this->customerid);
        }

        if (auth()->user()->role === 'user') {
            $query->where('user_id', operator: auth()->id());
        }

        if ($this->search) {
            $query->search($this->search); // Assuming you have a `search` scope
        }

        return $query->orderBy('name', 'asc')->get();
    }

    public function updatingSearch()
    {
        $this->resetPage(); // Reset to the first page when search query changes
    }

    public function render()
    {
        if (auth()->user()->role === 'admin' || auth()->user()->role == 'superAdmin' || auth()->user()->role === 'supplier') {
            // $query = Order::with(['customer', 'details', 'user'])
            //     ->search($this->search)
            //     ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc');
            if (auth()->user()->role === 'admin') {
                $query = Order::with(['customer', 'details', 'user'])
                    ->whereHas('user', function ($q) {
                        $q->where('wearhouse_id', auth()->user()->wearhouse_id);
                    })
                    ->search($this->search)
                    ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc');
            } else {
                $query = Order::with(['customer', 'details', 'user'])
                    ->search($this->search)
                    ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc');
            }

            if ($this->userid && $this->userid !== 'all') {
                $query->where('user_id', $this->userid);
            }

            if ($this->customerid && $this->customerid !== 'all') {
                $query->where('customer_id', $this->customerid);
            }

            $orders = $query->paginate($this->perPage);
        } else {
            $orders = Order::where('user_id', auth()->id())
                ->with(['customer', 'details', 'user'])
                ->search($this->search)
                ->orderBy($this->sortField, $this->sortAsc ? 'desc' : 'asc')
                ->paginate($this->perPage);
        }

        // $users = User::get(['id', 'name']);
        if (auth()->user()->role == 'admin' || auth()->user()->role == 'supplier' || auth()->user()->role == 'user') {
            $users = User::where('wearhouse_id', auth()->user()->wearhouse_id)->orderBy('name', 'ASC')->get(['id', 'name']);
        } elseif (auth()->user()->role == 'superAdmin') {
            // dd($this->sortAsc);
            $users = User::orderBy('name', 'ASC')->get(['id', 'name']);
        }

        if (auth()->user()->role == 'customer') {
            $users = user::where('id', auth()->user()->id)->orderBy('name', 'ASC')->get(['id', 'name']);
        }
        // Get customers based on the user role
        if (auth()->user()->role == 'superAdmin') {
            // dd($this->sortAsc);
            $customers = Customer::orderBy('name', 'ASC')->get(['id', 'name']);
        } elseif (auth()->user()->role == 'admin' && auth()->user()->wearhouse_id == 1) {
            $customers = Customer::with('user')
                ->whereHas('user', function ($query) {
                    $query->where('wearhouse_id', 1);
                })
                ->orderBy('name', 'ASC')->get(['id', 'name']);
        } elseif (auth()->user()->role == 'admin' && auth()->user()->wearhouse_id == 2) {

            $customers = Customer::with('user', 'orders', 'quotations')
                ->whereHas('user', function ($query) {
                    $query->where('wearhouse_id', 2);
                })->orderBy('name', 'ASC')->get(['id', 'name']);
        } else {
            $customers = Customer::with('user')
                ->where('user_id', auth()->user()->id)
                ->orderBy('name', 'ASC')->get(['id', 'name']);
        }

        return view('livewire.tables.order-table', [
            'orders' => $orders,
            'users' => $users,
            'customers' => $customers,
        ]);
    }
}
