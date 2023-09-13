<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Throwable;

class PaypalController extends Controller
{
    public function test($id){
        error_log($id);
        return($this->paypal($id));
    }
    public function paypal($id){
        error_log($id);
        $client = $this->paypalClient();
        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');
        $request->body = [
                     "intent" => "CAPTURE",
                     "purchase_units" => [[
                         "reference_id" => $id,
                         "amount" => [
                             "value" => "650.00",
                             "currency_code" => "USD"
                         ]
                     ]],
                     "application_context" => [
                          "cancel_url" => url(route('paypal.cancel')),
                          "return_url" => url(route('paypal.return'))
                     ] 
                 ];
                 try {
                    // Call API with your client and get a response for your call
                    $response = $client->execute($request);
                    if($response->statusCode == 201){
                        error_log("status 201");
                        session()->put('paypal_id',$response->result->id);
                        session()->put('order_id',$id);
                        foreach($response->result->links as $link){
                            if($link->rel == 'approve'){
                                error_log("if cond");
                                error_log($link->href);
                             return redirect()->away($link->href);
                            }
                        }

                    }
                    error_log("out if 201");
                }catch (Throwable $ex) {
                    return $ex->getMessage();
                }
                return "Unkown Error";
    }

    protected function paypalClient(){
        $config = config('services.paypal');
        $env = new SandboxEnvironment($config['client_id'], $config['client_secret']);
        $client = new PayPalHttpClient($env);
        return $client;
    }

    public function paypalReturn(){
        error_log("return function");
        $paypal_id = session()->get('paypal_id');
        $request = new OrdersCaptureRequest($paypal_id);
        $request->prefer('return=representation');
        try{
            $response = $this->paypalClient()->execute($request);
            if($response->statusCode == 201){
                if(strtoupper($response->result->status) == "COMPLETED"){
                    $user_id = session()->get('order_id');
                    error_log($user_id);
                    $user = User::findOrFail($user_id);
                    $user->VIP = 1;
                    $user->save();
                    session()->forget(['order_id','paypal_id']);
                    return "تم تحويل حسابك بنجاح برجاء العوده الي حسابك للتمتع بالمزايا الجديده" ;
                }

            }
        }
        catch(Throwable $e){
            return $e->getMessage();
        }
        
    }
    public function paypalCancel(){
        return "تم الغاء العمليه" ;
        
    }
}
