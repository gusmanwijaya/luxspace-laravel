<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(request()->ajax()) {
            $query = Product::query();

            return DataTables::of($query)
                ->addColumn('action', function($item) {
                    return '
                        <a 
                            class="inline-block bg-green-700 border border-green-700 text-white rounded-md px-2 py-1 m-1 transition duration-500 ease select-none hover:bg-green-800 focus:outline-none focus:shadow-outline" 
                            href="' . route('dashboard.product.gallery.index', $item->id) . '">
                                Gallery
                        </a>

                        <a 
                            class="inline-block border border-yellow-700 bg-yellow-700 text-white rounded-md px-2 py-1 m-1 transition duration-500 ease select-none hover:bg-yellow-800 focus:outline-none focus:shadow-outline" 
                            href="' . route('dashboard.product.edit', $item->id) . '">
                                Edit
                        </a>

                        <form action="'. route('dashboard.product.destroy', $item->id) .'" method="POST" class="inline-block">
                            '. method_field("DELETE") . csrf_field() .'

                            <button type="submit" class="inline-block border border-red-700 bg-red-700 text-white rounded-md px-2 py-1 m-1 transition duration-500 ease select-none hover:bg-red-800 focus:outline-none focus:shadow-outline">
                                Delete
                            </button>
                        </form>
                    ';
                })
                ->editColumn('price', function($item) {
                    return number_format($item->price);
                })
                ->rawColumns(['action'])
                ->make();
        }

        return view('pages.dashboard.product.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pages.dashboard.product.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductRequest $request)
    {
        $data = $request->all();
        $data['slug'] = Str::slug($request->name);

        Product::create($data);

        return redirect()->route('dashboard.product.index')->with('toast_success', 'Data has been created!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        return view('pages.dashboard.product.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProductRequest $request, Product $product)
    {
        $data = $request->all();
        $data['slug'] = Str::slug($request->name);

        $product->update($data);

        return redirect()->route('dashboard.product.index')->with('toast_success', 'Data has been updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $product->delete();
    
        return redirect()->route('dashboard.product.index')->with('toast_success', 'Data has been deleted!');
    }
}
