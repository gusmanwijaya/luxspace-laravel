<?php

namespace App\Http\Controllers;

use Exception;
use Midtrans\Snap;
use Midtrans\Config;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CheckoutRequest;

class FrontendController extends Controller
{
    public function index()
    {
        $product = Product::with(['galleries'])->latest()->get();
        return view('pages.frontend.index', compact('product'));
    }

    public function details($slug)
    {
        $product = Product::with(['galleries'])->where('slug', $slug)->firstOrFail();
        $recommendation = Product::with(['galleries'])->inRandomOrder()->limit(4)->get();

        return view('pages.frontend.details', compact(['product', 'recommendation']));
    }

    public function cart()
    {
        $cart = Cart::with(['products.galleries'])->where('users_id', Auth::user()->id)->get();
        return view('pages.frontend.cart', compact('cart'));
    }

    public function cartAdd($id)
    {
        Cart::create([
            'users_id' => Auth::user()->id,
            'products_id' => $id
        ]);

        return redirect()->route('cart')->with('toast_success', 'Product has been successfully add to cart!');
    }

    public function cartDelete(Cart $cart)
    {
        $cart->delete();

        return redirect()->route('cart')->with('toast_success', 'Cart has been deleted!');
    }

    public function checkout(CheckoutRequest $request)
    {
        $data = $request->all();

        // Get carts data
        $carts = Cart::with(['products'])->where('users_id', Auth::user()->id)->get();

        // Add to Transaction data
        $data['users_id'] = Auth::user()->id;
        $data['total_price'] = $carts->sum('products.price');

        // Create transaction
        $transaction = Transaction::create($data);

        // Create transaction item
        foreach ($carts as $cart) {
            $items[] = TransactionItem::create([
                'transactions_id' => $transaction->id,
                'users_id' => $cart->users_id,
                'products_id' => $cart->products_id,
            ]);
        }

        // Delete cart after transaction
        Cart::where('users_id', Auth::user()->id)->delete();

        // Konfigurasi midtrans
        Config::$serverKey = config('services.midtrans.serverKey');
        Config::$isProduction = config('services.midtrans.isProduction');
        Config::$isSanitized = config('services.midtrans.isSanitized');
        Config::$is3ds = config('services.midtrans.is3ds');

        // Setup variables midtrans
        $midtrans = [
            'transaction_details' => [
                'order_id' => 'LUX-' . $transaction->id,
                'gross_amount' => (int) $transaction->total_price
            ],
            'customer_details' => [
                'first_name' => $transaction->name,
                'email' => $transaction->email
            ],
            'enabled_payments' => ['gopay', 'bank_transfer'],
            'vtweb' => [],
        ];

        // Payment process
        try {
            // Get snap payment page url
            $paymentUrl = Snap::createTransaction($midtrans)->redirect_url;

            $transaction->payment_url = $paymentUrl;
            $transaction->save();

            // Redirect to Snap Payment Page
            return redirect($paymentUrl);
            
        } catch (Exception $e) {
            echo $e->getMessage();
        }

    }

    public function success()
    {
        return view('pages.frontend.success');
    }
}
