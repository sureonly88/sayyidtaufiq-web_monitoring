<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use DB;
use App\Model\transaksi; 

class LoginController extends Controller
{
   public function loginPage()
    {
      return view('login');
    }

    public function login(Request $request)
    {
      set_time_limit(0);
      $result = DB::select('
        SELECT *
        FROM web_mntr_users u
        LEFT JOIN lokets l on u.id_loket = l.id
        WHERE u.user = ? AND u.password = MD5(?)
      ', [
        $request->input('login'),
        $request->input('password')
      ]);
      if (count($result) == 1) {
        $session = $request->session();
        $session->put('auth', $result[0]);
        $b = DB::select('
          SELECT *
          FROM web_mntr_menu 
          LEFT JOIN web_mntr_role
          ON web_mntr_menu.id = web_mntr_role.menu_id
          WHERE web_mntr_role.level = ? order by web_mntr_menu.parent_id, web_mntr_menu.id',
        [
          $result[0]->level
        ]); 
        $session->put('permision', $b);
        //$homepage = $result[0]->homepage;
        //if($session->pull('redirect'))
        //  $homepage = $session->pull('redirect');
          
        //return redirect('/'.$homepage);
        return redirect('/');
        //return $result;
      }

      return back()->with('alerts', [
        ['type' => 'danger', 'text' => 'Login Gagal']
      ]);
    }

    public function home()
    {
    if (session('auth')->id_loket!=0){
      $loket = session('auth')->id_loket;
      $result = DB::select('
        SELECT lokets.pulsa,web_mntr_shareLoket.limit 
        from lokets left join web_mntr_shareLoket
        on lokets.id=web_mntr_shareLoket.id_lokets
        WHERE lokets.id = ?', [$loket]);

        $saldo=$result[0]->pulsa;
        $limit=$result[0]->limit;

        $tanggal = date('Y-m-d', strtotime('+8 hours'));
        //$tanggal='2017-05-26';
        $list = transaksi::where('vw_rekap_transaksi.tanggal',$tanggal)
                ->where('vw_rekap_transaksi.loket_code',session('auth')->loket_code)
                ->first();
        if (isset($list)){$hariini=$list['total']; } else{$hariini=0;}       
               
      }else{
        $saldo=0;
        $limit=0;
        $hariini=0; 
      }

      return view('home',compact('saldo','limit','hariini'));
    }

     public function logout(Request $request)
    {
      $request->session()->forget('auth');

      return redirect('/login');
    }

    public function homeAdmin()
    {
    set_time_limit(0);  
    if (session('auth')->id_loket!=0){
      $loket = session('auth')->id_loket;
      $result = DB::select('
        SELECT lokets.pulsa,web_mntr_shareLoket.limit 
        from lokets left join web_mntr_shareLoket
        on lokets.id=web_mntr_shareLoket.id_lokets
        WHERE lokets.id = ?', [$loket]);

        $saldo=$result[0]->pulsa;
        $limit=$result[0]->limit;

        $tanggal = date('Y-m-d', strtotime('+8 hours'));
        //$tanggal='2017-05-26';
        $list = transaksi::where('vw_rekap_transaksi.tanggal',$tanggal)
                ->where('vw_rekap_transaksi.loket_code',session('auth')->loket_code)
                ->first();
        if (isset($list)){$hariini=$list['total']; } else{$hariini=0;}       
               
      }else{
        $saldo=0;
        $limit=0;
        $hariini=0; 
      }

      return view('home',compact('saldo','limit','hariini'));
    }
}
