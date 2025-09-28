<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Product;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\WithPagination;

class AddProduct extends Component
{
    public $customer;
    use WithPagination;

    // public $perPage = 8;
    public $selectedValue;
    public $search = '';

    public $sortField = 'products.id';

    public $customer_id;
    public $order;
    public $sortAsc = 'desc';

    //add to Cart
    public $productId, $productName, $productsalePrice, $productSKU;

    public function addCartItem($productId, $name, $salePrice, $sku)
    {
        // dd($productId, $salePrice);
        // Retrieve the order based on the customer ID
        // $orderID = $getorderID->id;
        try {
            // Create a new order detail entry
            // dd($this->order);
            $getorderID = Order::where('id', $this->order->id)->firstOrFail();
            // dd($getorderID->id,$this->customer_id, $productId );
            $addItemToCart = OrderDetails::create([
                'order_id' => $getorderID->id,
                'product_id' => $productId,
                'quantity' => 1,
                'unitcost' => $salePrice,
                'total' => $salePrice,
            ]);
            $addItemSaved = $addItemToCart->save();
            // dd($addItemToCart);
            // If the item is added to the cart, update the order totals
            if ($addItemSaved) {
                $AllOrderDetails = OrderDetails::where('order_id', $getorderID->id)->get();
                $newTotalCost = $AllOrderDetails->sum('total');
                $Duebill = $newTotalCost - $getorderID->pay;

                // Update the order with the new totals
                $getorderID->update([
                    'total' => $newTotalCost,
                    'sub_total' => $newTotalCost,
                    'due' => $Duebill,
                ]);
                $getOrderSaved = $getorderID->save();

                if ($getorderID->org_total != null) {
                    $lastPrice = $salePrice + $getorderID->org_total;
                    $lastSub_total = $lastPrice - ($lastPrice * $getorderID->discount / 100);
                    $lastTotal = $lastPrice - ($lastPrice * $getorderID->discount / 100);
                    $lastDue = $lastTotal - $getorderID->pay;

                    $getorderID->update([
                        'org_total' => $lastPrice,
                        'total' => $lastTotal,
                        'sub_total' =>  $lastSub_total,
                        'due' => $lastDue,
                    ]);
                }
                $lastGetOrderSaved = $getorderID->save();
                // dd($lastGetOrderSaved);

                if ($addItemSaved && $getOrderSaved) {
                    // Dispatch a Livewire event and show success message
                    $this->dispatch('addedTocart');
                    session()->flash('success', 'Product has been added to cart!');
                    // dd($getorderID,OrderDetails::where('order_id', $getorderID->id)
                    // ->where('id', $AllOrderDetails->id)->get());
                }
                if ($lastGetOrderSaved) {
                    session()->flash('success', 'discount Updated!!');
                }
            } else {
                session()->flash('error', 'Failed to add product to cart!');
            }
        } catch (\Exception $e) {
            // Handle any exceptions and show an error message
            session()->flash('error', $e->getMessage());
        }
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
    public function updatingSearch()
    {
        $this->resetPage(); // Reset to the first page when search query changes
    }
    // protected $listeners = ['customerChanged' => 'handleCustomerChanged'];

    // public function handleCustomerChanged($customerId)
    // {
    //     $this->customer_id = $customerId;
    //     // dd($this->customer_id);
    //     // Perform any additional actions, such as updating a list of orders
    // }
    public function render()
    {
        // dd($this->customer_id);
        // Session::put('customer_id', $this->customer_id);
        // $products = Product::search($this->search)
        //     ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
        //     ->paginate(20);
        $products = Cache::remember(
            'product_list_' . $this->search . '_' . $this->sortField . '_' . ($this->sortAsc ? 'asc' : 'desc'),
            60, // cache for 60 seconds
            function () {
                return Product::search($this->search)
                    ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
                    ->paginate(20);
            }
        );
        // ->paginate($this->perPage);
        return view('livewire.add-product', [
            'products' => $products,
            'customer_id' => $this->customer_id,
        ]);
    }
}
