<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Customer;
use App\Models\Product;
use App\Models\user;
use Gloudemans\Shoppingcart\Facades\Cart;

class CreateNewOrder extends Component
{
    public $cartId = [],
        $cartItemquantity = [],
        $cartItemprice = [];
    // public $products, $customers, $carts;
    protected $listeners = ['addedTocart' => 'refreshneworderlist'];
    public $columns = [
        'productslist' => true,
        'orderlist' => true,
    ];
    public $discount = 0;
    public $thistotal = 0;

    public function toggleColumn($column)
    {
        $this->columns[$column] = !$this->columns[$column];
    }

    public function EditQtyPrice($cartid)
    {
        // Check if requested quantity is greater than available stock
        $productId = Cart::get($cartid); // Fetch the product ID from the cart item
        $availableStock = Product::where('id', intval($cartid))->value('quantity');

        // if ($this->cartItemquantity[$cartid] > $availableStock) {
        //     session()->flash('carterror', 'The requested quantity is not available in stock.');
        //     return;
        // }

        try {
            // Update both the quantity and the price
            Cart::update($cartid, $this->cartItemquantity[$cartid], [
                'price' => $this->cartItemprice[$cartid],
            ]);

            session()->flash('cartsuccess', 'The requested quantity has been updated.');
        } catch (\Exception $e) {
            session()->flash('carterror', 'An error occurred while updating the cart.');
        }

        $this->render(); // Refresh the component
    }
    public function RemoveItem($cartid)
    {
        Cart::remove($cartid);
        session()->flash('cartsuccess', 'The item has been removed from the cart.');
    }

    public function refreshneworderlist()
    {
        $this->render();
    }

    public function render()
    {
        $products = Product::with(['category_id'])->get();

        if (auth()->user()->role == 'superAdmin') {
            $customers = Customer::orderBy('name', 'ASC')->get(['id', 'name']);
        } else if (auth()->user()->role == 'admin' && auth()->user()->wearhouse_id == 1) {
            $customers = Customer::with('user')
                ->whereHas('user', function ($query) {
                    $query->where('wearhouse_id', 1);
                })->orderBy('name', 'ASC')
                ->get(['id', 'name']);
        } elseif (auth()->user()->role == 'admin' && auth()->user()->wearhouse_id == 2) {
            $customers = Customer::with('user')
                ->whereHas('user', function ($query) {
                    $query->where('wearhouse_id', 2);
                })->orderBy('name', 'ASC')
                ->get(['id', 'name']);
        }  
        else {
            $customers = Customer::where('user_id', auth()->id())->orderBy('name', 'ASC')->get(['id', 'name']);
            // $customers = Customer::get(['id', 'name']);
         }

        $carts = Cart::content();
        // dd($carts);

        foreach ($carts as $item) {
            $this->cartId[$item->rowId] = $item->rowId;
            $this->cartItemquantity[$item->rowId] = $item->qty;
            $this->cartItemprice[$item->rowId] = $item->price;
        }
        // dd($carts);
        $this->thistotal =  Cart::subtotal() * ((100 - (float)$this->discount) / 100);
        return view('livewire.create-new-order', [
            'products' => $products,
            'allcustomers' => $customers,
            'newcartitem' => $carts,
            'discount' => $this->discount,
            'thistotal' => $this->thistotal,
        ]);
    }
}
