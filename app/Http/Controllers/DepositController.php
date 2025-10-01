<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
 
use Response; 
use App\Model\lokets;

class DepositController extends Controller
{
    public function depositLoket()
    {
      return view('deposit.depositLoket');
    }
    public function ajaxDepositLoket($tipe)
    {
      set_time_limit(0);
      $list= lokets::where('tipe',$tipe)->get();
      return Response::json(array(
        'status' => 'Success',
        'message' => '-',
        'data' => $list
      ),200);
    }
    public function depositMutasi()
    {
      if (session('auth')->id_loket!='0'){
        $loket = explode(',', session('auth')->id_loket);
        if (count($loket)>1){
          $lkt=$loket[1];
        }else{
          $lkt=$loket[0];
        }
   
        $result= lokets::select('pulsa')
              ->where('id',$lkt)
              ->first();
        $saldo=$result->pulsa;              
      }else{
        $saldo=0;
      }

      return view('deposit.depositMutasi',compact('saldo'));
    }
    public function ajaxDepositMutasi($bulan)
    {
      set_time_limit(0);
      $bln=explode("-", $bulan);
      $lokets=explode(",", session('auth')->id_loket);
      $list= DB::table('topups')
            ->select('topups.*','lokets.nama')
            ->leftJoin('lokets','lokets.id','=','topups.loket_id')
            ->whereMonth('topups.topup_date',$bln[1])
            ->whereYear('topups.topup_date',$bln[0])
            ->where('topups.loket_id',$lokets[1])
            ->get();

      return Response::json(array(
        'status' => 'Success',
        'message' => '-',
        'data' => $list
      ),200);
    }
}
