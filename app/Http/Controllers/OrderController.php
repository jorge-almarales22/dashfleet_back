<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function getOrder(Request $request){

        $order_code = $request->order_code;
        $ID = $request->ID;
        $ID_type = $request->ID_type;

        //TODO: Validar que el usuario existe
        $user = User::first()->where('identification', $ID)->where('ID_type', $ID_type)->first();

        if(!$user){
            return response()->json(['status' => false, 'message' => 'Usuario no encontrado'], 404);
        }

        //TODO: Validar que el usuario tiene un pedido
        $order = $user->orders()->where('code', $order_code)->get();

        if(sizeof($order) == 0){
            return response()->json(['status' => false, 'message' => 'El usuario no tiene un pedido valido, por favor verifique sus datos'], 404);
        }

        $products = Order::where('code', $order_code)->with('products')->first();

        return $order->map(function($ord) use($user, $products){
            $order = new \stdClass();
            $order->code = $ord->code;
            $order->user = $ord->user->name;
            $order->address = $ord->user->address;
            $order->status = $ord->status;
            $order->deliverate_date = date('d/m/Y', strtotime($ord->deliverate_date));
            $order->products = $products->products->map(function($prod){
                $product = new \stdClass();
                $product->name = $prod->name;
                $product->reference = $prod->reference;
                $product->quantity = $prod->pivot->quantity;
                return $product;
            });
            return $order;
        });
    }
}
