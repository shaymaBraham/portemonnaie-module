<?php

namespace Modules\PorteMonnaie\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Auth;
use Modules\PorteMonnaie\Entities\Item;
use Bavix\Wallet\Models\Transaction;
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




    function buy_product($produit)
    {
        try {


            $user=Auth::user();
            $classe=get_class($produit);
           
            $item=$produit;
            $item->getAmountProduct($user); // 100
            $wallet = $user->getWallet($user->id.'-wallet');
        
            
            $transfer=$wallet->pay($item);

            
           


        } catch (Throwable $e) {
            
                
            
        }
        
    }

    function buy_product_free($produit)
    {
        try {


            $user=Auth::user();
            $classe=get_class($produit);
           
            $item=$produit;
            $item->getAmountProduct($user); // 100
            $wallet = $user->getWallet($user->id.'-wallet');
           
            
            $transfer=$wallet->payFree($item);
           

            


        } catch (Throwable $e) {
            
                
            
        }
        
    }

    function transfer($user_to,$user_from,$amount)
    {
        
        if($user_to->getKey() !== $user_from->getKey()) 
        {

            $user_to->balance; 
            $user_from->balance; 

            if($user_from->balance > $amount){
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
        
        if($user_to->getKey() !== $user_from->getKey()) 
        {

            $user_to->balance; 
            $user_from->balance; 

          
                $user_from->forceTransfer($user_to, $amount); 
            
            
            
          
        }
        else{

            //exception user to equal user from
        }
    }


    public function rembourser($user,$produit)
    {

        try {


            $user=Auth::user();
            $classe=get_class($produit);
           
            $item=$produit;
            $item->getAmountProduct($user); // 100
            $wallet = $user->getWallet($user->id.'-wallet');
          
            
           $wallet->refund($item);
            
            


        } catch (Throwable $e) {
            
             
            
        }
        
    }

    public function offrir($user_to,$user_from,$produit)
    {

        if($user_to->getKey() !== $user_from->getKey()) 
        {

            $user_to->balance; 
            $user_from->balance; 

            if($user_from->balance > $produit->balance)
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

    public function confirm_transaction($id_transaction)
    {

    }
}
