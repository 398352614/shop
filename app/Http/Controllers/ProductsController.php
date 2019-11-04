<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
class ProductsController extends Controller
{
    public function index(Request $request)
    {
        // 创建一个查询构造器
        $builder = Product::query()->where('on_sale', true);
        // 判断是否有提交 search 参数，如果有就赋值给 $search 变量
        // search 参数用来模糊搜索商品
        if ($search = $request->input('search', '')) {
            $like = '%'.$search.'%';
            // 模糊搜索商品标题、商品详情、SKU 标题、SKU描述
            $builder->where(function($query)use($like){
                $query->where('title','like',$like)
                    ->orwhere('description','like',$like)
                    ->orwherehas('skus',function($query)use($like){
                    $query->where('title','like',$like)
                        ->orwhere('description','like',$like);
                });
            });
}





        $products = Product::query()->where('on_sale', true)->paginate(16);

        return view('products.index', ['products' => $products]);
    }
    //
}
