<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Order;
use App\Models\Customer;

class SearchCustomerOrders extends Component
{
    use WithPagination;

    public $customerid = '';
    public $search = '';
    public $totalOrders = 0;

    public $orderUuid, $orderInvoiceNo, $orderCudtomerId;
    // protected $queryString = ['search', 'customerid']; //if you want to keep the search and customerid in the URL

    public function OderSelected($orderUuid, $orderInvoiceNo, $orderCudtomerId)
    {
        // Dispatch to other components
        $this->dispatch('order-selected', [
            'orderUuid' => $orderUuid,
            'orderInvoiceNo' => $orderInvoiceNo,
            'orderCudtomerId' => $orderCudtomerId
        ]);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCustomerid()
    {
        $this->resetPage();
    }

    public function render()
    {
        if (auth()->user()->role === 'user') {
            $customers = Customer::select('id', 'name')
                ->where('user_id', auth()->user()->id)->get();
        } elseif (auth()->user()->role === 'admin' and auth()->user()->wearhouse_id == 1) {
            // For admin, superAdmin, and supplier roles, we can filter customers based on the wearhouse_id
            // This assumes that the 'user' relationship is defined in the Customer model
            // and that it has a 'wearhouse_id' field.
            // Adjust the wearhouse_id as needed based on your application's logic. 
            $customers = Customer::select('id', 'name')
                ->with('user') // eager load the user relationship
                ->whereHas('user', function ($query) {
                    $query->where('wearhouse_id', 1);
                })
                ->get();
        } elseif (auth()->user()->role === 'admin' and auth()->user()->wearhouse_id == 2) {
            // For admin, superAdmin, and supplier roles, we can filter customers based on the wearhouse_id
            // This assumes that the 'user' relationship is defined in the Customer model
            // and that it has a 'wearhouse_id' field.
            // Adjust the wearhouse_id as needed based on your application's logic.
            // dd(auth()->user()->wearhouse_id);
            $customers = Customer::select('id', 'name')
                ->with('user') // eager load the user relationship
                ->whereHas('user', function ($query) {
                    $query->where('wearhouse_id', 2);
                })
                ->get();
        } else {
            // For other roles, we can simply get all customers
            // This assumes that the 'Customer' model has a 'name' field.
            // Adjust the query as needed based on your application's logic.
            // dd(auth()->user()->wearhouse_id);
            $customers = Customer::select('id', 'name')->get();
        }
        $orders = collect(); // Empty collection by default
        $this->totalOrders = 0;

        if (auth()->user()->role) {
            if ($this->customerid) {
                $ordersQuery = Order::with(['customer', 'details', 'user'])
                    ->where('order_status', '!=', 2)
                    ->where('customer_id', $this->customerid);

                if ($this->search) {
                    $ordersQuery->where(function ($query) {
                        $query->where('invoice_no', 'like', '%' . $this->search . '%');
                    });
                }

                $orders = $ordersQuery
                    ->orderBy('created_at', 'desc')
                    ->get();

                $this->totalOrders = $orders->count();
            }

            return view('livewire.search-customer-orders', [
                'orders' => $orders,
                'customers' => $customers,
                'selectedCustomer' => $this->customerid,
                'totalOrders' => $this->totalOrders,
            ]);
        }

        // abort(403); // Unauthorized
    }
}
