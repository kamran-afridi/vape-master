<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserAuthLedger extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $perPage = 15;
    public $search = '';
    public $userid = '';
    public $customerid = '';
    public $paymentStatus = '';
    public $paymentMethod = '';
    public $sub_total = '';
    public $total_due = '';
    public $total_payedamt = '';
    public $datefrom = null;
    public $dateto = null;

    public $totalOrders = 0;

    public $sortField = 'id';
    public $sortAsc = false;
    public $columns = [
        'payment' => true,
        'payto' => true,
        'user' => true,
        'status' => true,
        'actions' => true,
    ];

    public function exportCsv(): StreamedResponse
    {
        $orders = $this->buildOrdersQuery()->get(); // Extracted query logic for reuse

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="ledger_export.csv"',
        ];

        $callback = function () use ($orders) {
            $handle = fopen('php://output', 'w');

            // Define CSV columns
            fputcsv($handle, ['Invoice No', 'Customer Name', 'Order Date', 'Payment Type', 'Total', 'Paid Amount', 'Pay To', 'Status']);

            foreach ($orders as $order) {
                fputcsv($handle, [$order->invoice_no, $order->customer->name ?? '', $order->order_date->format('d-m-Y'), $order->payment_type, $order->total, $order->pay, $order->payto, $order->order_status->label()]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function buildOrdersQuery()
    {
        $query = Order::with(['customer', 'details', 'user'])
            ->whereNot('order_status', '2')
            ->where('user_id', auth()->id());

        if (auth()->user()->role === 'user') {
            if ($this->customerid) {
                $query->where('customer_id', $this->customerid);
            }
            if ($this->paymentStatus && $this->paymentStatus != 'allstatus') {
                $query->where('order_status', $this->paymentStatus);
            }
            if ($this->paymentMethod && $this->paymentMethod != 'allpayment') {
                $query->where('payment_type', $this->paymentMethod);
            }
            if ($this->datefrom && $this->dateto) {
                $query->whereBetween(DB::raw('DATE(created_at)'), [$this->datefrom, $this->dateto]);
            }
        }

        return $query;
    }

    public function toggleColumn($column)
    {
        $this->columns[$column] = !$this->columns[$column];
    }
    public function mount()
    {
        $this->userid = session('UserId', '');
        $this->customerid = '';
    }

    public function updatedUserid($value)
    {
        $this->userid = $value;
    }

    public function updatedCustomerid($value)
    {
        $this->customerid = $value;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        // dd(auth()->user()->id);
        $ordersQuery = Order::with(['customer', 'details', 'user'])
            ->whereNot('order_status', '2')
            ->where('user_id', auth()->user()->id);

        // Apply filters for admin or supplier roles
        if (auth()->user()->role === 'user') {
            // Filter by user ID if provided
            // if ($this->userid) {
            //     $ordersQuery->where('user_id', $this->userid);
            // }

            // Filter by customer ID if provided
            if ($this->customerid) {
                $ordersQuery->where('customer_id', $this->customerid)->whereNot('order_status', '2');
            }
            if ($this->paymentStatus) {
                if ($this->paymentStatus == 'allstatus') {
                    $ordersQuery->whereNot('order_status', '2');
                } else {
                    $ordersQuery->where('order_status', $this->paymentStatus)->whereNot('order_status', '2');
                }
            }
            if ($this->paymentMethod) {
                if ($this->paymentMethod == 'allpayment') {
                    $ordersQuery->whereNot('order_status', '2');
                } else {
                    $ordersQuery->where('payment_type', $this->paymentMethod)->whereNot('order_status', '2');
                }
            }
            // Apply date range filter if both dates are selected
            if ($this->datefrom && $this->dateto) {
                $ordersQuery->whereBetween(DB::raw('DATE(created_at)'), [$this->datefrom, $this->dateto])->whereNot('order_status', '2');
            }
        } else {
            // For regular users, filter only by their user ID
            $ordersQuery->where('user_id', auth()->id())->whereNot('order_status', '2');
        }

        // Apply search, sorting, and pagination
        $this->sub_total = $ordersQuery->sum('sub_total');
        $this->total_due = $ordersQuery->sum('total') - $ordersQuery->sum('pay');
        $this->total_payedamt = $ordersQuery->sum('pay');
        $orders = $ordersQuery
            ->search($this->search)
            ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
            ->paginate($this->perPage);

        $users = User::get(['id', 'name'])->where('id', auth()->user()->id);
        
        if (auth()->user()->role === 'admin' || auth()->user()->role === 'superAdmin') {  
        $customers = Customer::get(['id', 'name']); 
        } else {
            $customers = Customer::where('user_id', auth()->user()->id)->get(['id', 'name']);
        }
        // ->where ('user_id', auth()->user()->id);

        $this->totalOrders = $orders->total();

        return view('livewire.user-auth-ledger', [
            'orders' => $orders,
            'users' => $users,
            'customers' => $customers,
            'ordersQuery' => $ordersQuery,
            'sub_total' => $this->sub_total,
            'total_due' => $this->total_due,
            'total_payedamt' => $this->total_payedamt,
            'totalOrders' => $this->totalOrders,
        ]);
    }
}
