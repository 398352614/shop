<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserAddress;
use App\Models\Order;
use App\Models\ProductSku;
use App\Exceptions\InvalidRequestException;
use App\Jobs\CloseOrder;
use Carbon\Carbon;

class OrderService{
    public function store(User $user,UserAddress $address,$remark,$items){
        $order=\DB::transaction(function ()use($user,$address,$remark,$items){
               $address->update(['last_used_at'=>Carbon::now()]);
            $order=new Order([
              'address'=> [
                  'address'=>$address->full_address,
                  'zip'=>$address->zip,
                  'contact_name'=>$address->concact_name,
                  'contact_phone'=>$address->contact_phone,
              ],
               'remark'=>$remark,
               'total_amount'=>0,
           ]);
           $order->user()->associte($user);
           $order->save();
           $totalAmount =o;

           foreach($items as $data){
               $sku=ProductSku::find($data['sku_id']);
               $item = $order->items()->make([
                   'amount' => $data['amount'],
                   'price'  => $sku->price,
               ]);
               $item->product()->associate($sku->product_id);
               $item->productSku()->associate($sku);
               $item->save();
               $totalAmount += $sku->price + $data['amount'];
               if($sku->decreaseStock($data['amount'])<=0){
                   throw new InvalidRequestException('该商品库存不足');
               }
           }
           //dispatch(new CloseOrder($order,config('app.order_tll'))));
        return $order;
        });

    }
}
