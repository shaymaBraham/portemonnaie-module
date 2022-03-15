<?php

namespace Modules\PorteMonnaie\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Auth;
use Modules\PorteMonnaie\Entities\Item;
use Bavix\Wallet\Models\Transaction;
use App\Models\User;
class PorteMonnaieController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $user=Auth::user();
        if($user){
            if( !$user->hasWallet($user->id.'-wallet')){
                $wallet = $user->createWallet([
                'name' => 'New Wallet',
                'slug' => $user->id.'-wallet',
            ]);
                $wallet->deposit(100);
                $wallet->balance; // 100
    
               
           }
           else{
            $wallet = $user->getWallet($user->id.'-wallet');
                  $wallet->balance; // 100
           }
            $walletTransactions=Transaction::where('wallet_id',$wallet->id)->orderBy('id','desc')->get();
            return view('portemonnaie::index',compact('wallet','walletTransactions'));
        }
        else
        return redirect('/login');
       
    }

    


    function alimentation($user,$amount,?array $meta = null)
    {
        
       
        if( !$user->hasWallet($user->id.'-wallet')){
                $wallet = $user->createWallet([
                'name' => 'New Wallet',
                'slug' => $user->id.'-wallet',
            ]);
               
           }
           else{
               $wallet = $user->getWallet($user->id.'-wallet');
                  
           }

           $transaction=$wallet->deposit($amount,$meta);

           return $transaction->id;



    }


    function retrait($user,$amount)
    {
        
       
        if( !$user->hasWallet($user->id.'-wallet')){
                $wallet = $user->createWallet([
                'name' => 'New Wallet',
                'slug' => $user->id.'-wallet',
            ]);
               
           }
           else{
               $wallet = $user->getWallet($user->id.'-wallet');
           
               
           }


           if($wallet->canWithdraw($amount))
           {
            $transaction=$wallet->withdraw($amount);

           }

           
                 
            

    }

    function retrait_force($user,$amount)
    {
        
       
        if( !$user->hasWallet($user->id.'-wallet')){
                $wallet = $user->createWallet([
                'name' => 'New Wallet',
                'slug' => $user->id.'-wallet',
            ]);
               
           }
           else{
               $wallet = $user->getWallet($user->id.'-wallet');
           
               
           }

           $transaction=$wallet->forcewithdraw($amount);

           
                 
            

    }




    function buy_product($produit,$user_id)
    {
        try {


            
            $user = User::find($user_id);
           
            
            $wallet = $user->getWallet($user->id.'-wallet');
        
            
            $transfer=$wallet->pay($produit);

            
           


        } catch (Throwable $e) {
            
                
            
        }
        
    }

    function buy_product_free($produit,$user_id)
    {
        try {


            $user=Auth::user();
           
            $wallet = $user->getWallet($user->id.'-wallet');
           
            
            $transfer=$wallet->payFree($produit);
           

            


        } catch (Throwable $e) {
            
                
            
        }
        
    }

    function transfer($user_to,$user_from,$amount)
    {
        
        if($user_to->id !== $user_from->id) 
        {

            $wallet_to = $user->getWallet($user_to->id.'-wallet');
            $wallet_from = $user->getWallet($user_from->id.'-wallet');

            if($wallet_from->balance > $amount){

                $user_from->transfer($user_to, $amount); 
            }
            
            
           else{

            //exception solde insuffisant
           }
        }
        else{

            //exception user to equal user from
        }
                
          

    }

    function forceTransfer($user_to,$user_from,$amount)
    {
        
       if($user_to->id !== $user_from->id) 
        {

            $wallet_to = $user->getWallet($user_to->id.'-wallet');
            $wallet_from = $user->getWallet($user_from->id.'-wallet');
            
            if($wallet_from->balance > $amount){

                $user_from->forceTransfer($user_to, $amount); 
            }
          
               
            
            
            
          
        }
        else{

            //exception user to equal user from
        }
    }


    public function rembourser($user,$produit)
    {

        try {


           
            $wallet = $user->getWallet($user->id.'-wallet');
                      
            $wallet->refund($produit);
            
            


        } catch (Throwable $e) {
            
             
            
        }
        
    }

    public function offrir($user_to,$user_from,$produit)
    {

        if($user_to->id !== $user_from->id) 
        {

            $wallet_to = $user->getWallet($user_to->id.'-wallet');
            $wallet_from = $user->getWallet($user_from->id.'-wallet');
          

            if($wallet_from->balance > $produit->getAmountProduct($user_from))
            {

                $user_from->gift($user_to, $produit); 
            }
            
            
           else{

            //exception solde insuffisant
           }
        }
        else{

            //exception user to equal user from
        }
        
    }

    public function get_Transactions($model,$model_id)
    {

           $transactions=Transaction::where('payable_type',$model)
           ->where('payable_id',$model_id)
           ->orderBy('id','desc')
           ->get();

           return $transactions;

    }

    
}
