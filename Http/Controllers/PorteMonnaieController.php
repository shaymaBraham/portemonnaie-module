<?php

namespace Modules\PorteMonnaie\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Auth;
use Modules\PorteMonnaie\Entities\Item;
use Bavix\Wallet\Models\Transaction;
use App\Models\User;
use Modules\PaiementGateways\Entities\ModePaiement;


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
                'name' => $user->name.' Wallet',
                'slug' => $user->id.'-wallet',
            ]);
               
    
               
           }
           else{
            $wallet = $user->getWallet($user->id.'-wallet');
                 
           }

           $modes_paiement=ModePaiement::all();
            $walletTransactions=Transaction::where('wallet_id',$wallet->id)->orderBy('id','desc')->get();
            return view('portemonnaie::index',compact('wallet','walletTransactions','modes_paiement'));
        }
        else
        return redirect('/login');
       
    }

    



    protected function alimentation($user,$amount,?array $meta = null)
    {
        
       try{
                
               if( !$user->hasWallet($user->id.'-wallet')){
                    $wallet = $user->createWallet([
                    'name' => $user->name.' Wallet',
                    'slug' => $user->id.'-wallet',
                ]);
                
                }
                else{
                    $wallet = $user->getWallet($user->id.'-wallet');
                        
                }

                $transaction=$wallet->deposit($amount,$meta);

               
                return ['success'=>1,'data'=>$transaction->id,'message' => ""];


       }

       catch (Throwable $e) {
        return ['success'=>0,'data'=>null,'message' => $e.getMessage()];
       }
        


    }


    protected function retrait($user,$amount)
    {
        
       try{
        if( !$user->hasWallet($user->id.'-wallet')){
                $wallet = $user->createWallet([
                'name' => $user->name.' Wallet',
                'slug' => $user->id.'-wallet',
            ]);
               
           }
           else{
               $wallet = $user->getWallet($user->id.'-wallet');
           
               
           }


           if($wallet->canWithdraw($amount))
           {
            $transaction=$wallet->withdraw($amount);

            return ['success'=>1,'data'=>$transaction,'message' => ""];


           }
           else{

           }

        }

        catch (Throwable $e) {
            return ['success'=>0,'data'=>null,'message' => $e.getMessage()];
        } 
                 
            

    }

    protected function retrait_force($user,$amount)
    {
        try{
       
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

           return ['success'=>1,'data'=>$transaction,'message' => ""];

        }

        catch (Throwable $e) {
            return ['success'=>0,'data'=>null,'message' => $e.getMessage()];
        }
                 
            

    }




    protected function buy_product($produit,$user_id)
    {
        try {


            
            $user = User::find($user_id);
           
            
            $wallet = $user->getWallet($user->id.'-wallet');
        
            
                $transfer=$wallet->pay($produit);

                return ['success'=>1,'data'=>$transfer,'message' => ""];
 
           


        } catch (Throwable $e) {
            
                return ['success'=>0,'data'=>null,'message' => $e.getMessage()];
            
        }
        
    }

    protected  function buy_product_free($produit,$user_id)
    {
        try {


            $user=Auth::user();
           
            $wallet = $user->getWallet($user->id.'-wallet');
           
            
            $transfer=$wallet->payFree($produit);
           

            return ['success'=>1,'data'=>$transfer,'message' => ""];


        } catch (Throwable $e) {
            
                
            return ['success'=>0,'data'=>null,'message' => $e.getMessage()];
        }
        
    }

    protected  function transfer($user_to,$user_from,$amount)
    {
        try{
        
            if($user_to->id !== $user_from->id) 
            {

                $wallet_to = $user->getWallet($user_to->id.'-wallet');
                $wallet_from = $user->getWallet($user_from->id.'-wallet');

                if($wallet_from->balance > $amount){

                    $transfer=$user_from->transfer($user_to, $amount); 

                    return ['success'=>1,'data'=>$transfer,'message' => ""];

                }
                
                
            else{

                //exception solde insuffisant
                return ['success'=>0,'data'=>null,'message' => "solde insuffisant"];
            }
            }
            else{

                //exception user to equal user from

                return ['success'=>0,'data'=>null,'message' => "you cannot transfer to yourself"];
            }
                    
      }

      catch (Throwable $e) {
        return ['success'=>0,'data'=>null,'message' => $e.getMessage()];
      }

    }

    protected  function forceTransfer($user_to,$user_from,$amount)
    {
        try{
        
       if($user_to->id !== $user_from->id) 
        {

            $wallet_to = $user->getWallet($user_to->id.'-wallet');
            $wallet_from = $user->getWallet($user_from->id.'-wallet');
            
            if($wallet_from->balance > $amount){

                $transfer=$user_from->forceTransfer($user_to, $amount); 

                return ['success'=>1,'data'=>$transfer,'message' => ""];

            }
          
               
            
            
            
          
        }
        else{

            //exception user to equal user from
            return ['success'=>0,'data'=>null,'message' => "you cannot transfer to yourself"];
        }

    }

    catch (Throwable $e) {
        return ['success'=>0,'data'=>null,'message' => $e.getMessage()];
      }
    }


    protected function rembourser($user,$produit)
    {

        try {


                                             
            $retour=$user->refund($produit);

            //retourne true or false

            return ['success'=>1,'data'=>$retour,'message' => ""];

            


        } catch (Throwable $e) {
            
            return ['success'=>0,'data'=>null,'message' => $e.getMessage()]; 
            
        }
        
    }

    protected function offrir($user_to,$user_from,$produit)
    {

        try{
                if($user_to->id !== $user_from->id) 
                {

                    $wallet_to = $user->getWallet($user_to->id.'-wallet');
                    $wallet_from = $user->getWallet($user_from->id.'-wallet');
                

                    if($wallet_from->balance > $produit->getAmountProduct($user_from))
                    {

                        $transfer=$user_from->gift($wallet_to, $produit); 

                        return ['success'=>1,'data'=>$transfer,'message' => ""];


                    }
                    
                    
                else{
                    return ['success'=>0,'data'=>null,'message' => "solde insuffisant"];
                    //exception solde insuffisant
                }
                }
                else{

                    //exception user to equal user from
                    return ['success'=>0,'data'=>null,'message' => "you cannot gift yourself"];
                }

            }

            catch (Throwable $e) {
                return ['success'=>0,'data'=>null,'message' => $e.getMessage()];
            }
        
    }

    protected function get_Transactions($model,$model_id)
    {

           $transactions=Transaction::where('payable_type',$model)
           ->where('payable_id',$model_id)
           ->orderBy('id','desc')
           ->get();

           return $transactions;

    }

    protected function get_walletTransfers($user)
    {

        $wallet = $user->getWallet($user->id.'-wallet');
        $transfers=$wallet->transfers;

           return $transfers;

    }

    protected function getAllTransactions()
    {

           $allTransactions=Transaction::orderBy('id','desc')->get();

           return $allTransactions;

    }

    
}
