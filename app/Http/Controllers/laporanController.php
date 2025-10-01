<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;

use DB;
use Excel;
use Response; 
use ZanySoft\LaravelPDF\PDF;
use App\Model\bulanTransaksi;
use App\Model\lokets;
use App\Model\transaksi; 
use App\Model\detailTransaksi;

use PHPExcel_Worksheet_Drawing;

class laporanController extends Controller
{

public function listBulanan()
{
  set_time_limit(0);
  $lokets= lokets::select('id as id', 'nama as text')->get();
  return view('laporan.laporanBulanan',compact('lokets'));
}

public function ajaxListBulanan($tahun,$bulan,$loket_code,$jenis_transaksi,$tipe) {
  set_time_limit(0);
  if  ($loket_code<>"-"){
      $lokets=lokets::where('id',$loket_code)->first(); 
      $loket_code=$lokets->loket_code;
    }

  $list =DB::select('SET @nom=0');  
  $list = bulanTransaksi::select('vw_bulan_transaksi.*','lokets.byadmin','lokets.tipe',
            'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
            'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
            DB::raw(" (@nom := @nom+1) nomor,'' as aksi"))
            ->where('vw_bulan_transaksi.tahun','=',$tahun)
            ->where('vw_bulan_transaksi.bulan','=',$bulan);
  //jenis All, loket isi, jenis trans ALL          
  if  ($loket_code<>"-"&&$jenis_transaksi=="ALL"){
    $list=$list->where('vw_bulan_transaksi.loket_code',$loket_code);
    $list=$list->leftJoin('lokets','lokets.loket_code','=','vw_bulan_transaksi.loket_code');
  }
  //jenis All, loket isi, jenis trans isi
  else if  ($loket_code<>"-"&&$jenis_transaksi<>"ALL"){
    $list=$list->where('vw_bulan_transaksi.loket_code',$loket_code)
              // ->where('vw_bulan_transaksi.jenis_transaksi',$jenis_transaksi); 
               ->where('vw_bulan_transaksi.jenis_transaksi', 'like', '%' .$jenis_transaksi. '%');
    $list=$list->leftJoin('lokets','lokets.loket_code','=','vw_bulan_transaksi.loket_code');
  }
  //jenis All, loket All, jenis trans isi
  else if  ($loket_code=="-"&&$jenis_transaksi<>"ALL"&&$tipe=="ALL"){
    //$list=$list->where('vw_bulan_transaksi.jenis_transaksi',$jenis_transaksi); 
    $list=$list->where('vw_bulan_transaksi.jenis_transaksi', 'like', '%' .$jenis_transaksi. '%');
    $list=$list->leftJoin('lokets','lokets.loket_code','=','vw_bulan_transaksi.loket_code');
  }
  //jenis isi, loket ALL, jenis trans ALL
  else if ($loket_code=="-"&&$tipe<>"ALL"&&$jenis_transaksi=="ALL"){
    $list=$list->Join('lokets',function($join) use ($tipe)
              {
                $join->on('lokets.loket_code','=','vw_bulan_transaksi.loket_code');
                $join->where('lokets.tipe','=',$tipe);
              });
  }
  //jenis isi, loket ALL, jenis trans isi
  else if ($loket_code=="-"&&$tipe<>"ALL"&&$jenis_transaksi<>"ALL"){
    $list=$list->Join('lokets',function($join) use ($tipe)
              {
                $join->on('lokets.loket_code','=','vw_bulan_transaksi.loket_code');
                $join->where('lokets.tipe','=',$tipe);
              });

    //$list=$list->where('vw_bulan_transaksi.jenis_transaksi',$jenis_transaksi); 
    $list=$list->where('vw_bulan_transaksi.jenis_transaksi', 'like', '%' .$jenis_transaksi. '%'); 
  }
  //jenis ALL, loket ALL, jenis trans ALL
  else {
    $list=$list->Join('lokets','lokets.loket_code','=','vw_bulan_transaksi.loket_code');
  }  
  $list=$list->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets');        
  $list=$list->get();

  return Response::json(array(
        'status' => 'Success',
        'message' => '-',
        'data' => $list
      ),200);

}

public function ajaxListBulananNew($tahun,$bulan,$loket_code,$jenis_transaksi,$tipe)
  {
    set_time_limit(0);
    if  ($loket_code<>"-"){
      $lokets=lokets::where('id',$loket_code)->first(); 
      $loket_code=$lokets->loket_code;
    }

    $pdam=DB::table('vw_pdambjm_trans')
              ->select('lokets.byadmin','lokets.tipe',
                'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
                'web_mntr_shareLoket.pln_postpaid_n',
                'web_mntr_shareLoket.pln_prepaid_n','web_mntr_shareLoket.pln_nontaglis_n',

                'vw_pdambjm_trans.loket_code AS loket_code','vw_pdambjm_trans.loket_name AS loket_name',
                'vw_pdambjm_trans.jenis_loket AS jenis_loket','vw_pdambjm_trans.jenis_transaksi AS jenis_transaksi',
   
              DB::raw("sum(vw_pdambjm_trans.tagihan) AS tagihan,sum(vw_pdambjm_trans.admin) AS admin,
                    sum(vw_pdambjm_trans.total) AS total,count(0) AS jumlah,'' as aksi"))
              ->whereMonth('vw_pdambjm_trans.tanggal', $bulan)
              ->whereYear('vw_pdambjm_trans.tanggal', $tahun); 

    $pln=DB::table('vw_transaksi_pln')
            ->select('lokets.byadmin','lokets.tipe',
                'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
                'web_mntr_shareLoket.pln_postpaid_n',
                'web_mntr_shareLoket.pln_prepaid_n','web_mntr_shareLoket.pln_nontaglis_n',

                'vw_transaksi_pln.loket_code AS loket_code','vw_transaksi_pln.loket_name AS loket_name',
                'vw_transaksi_pln.jenis_loket AS jenis_loket','vw_transaksi_pln.jenis_transaksi AS jenis_transaksi',

            DB::raw("sum(vw_transaksi_pln.tagihan) AS tagihan,sum(vw_transaksi_pln.admin) AS admin,
                  sum(vw_transaksi_pln.total) AS total,count(0) AS jumlah,'' as aksi"))
            ->whereMonth('vw_transaksi_pln.tanggal', $bulan)
            ->whereYear('vw_transaksi_pln.tanggal', $tahun);

    $pln_nontaglis=DB::table('vw_transaksi_pln_nontaglis')
            ->select('lokets.byadmin','lokets.tipe',
                'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
                'web_mntr_shareLoket.pln_postpaid_n',
                'web_mntr_shareLoket.pln_prepaid_n','web_mntr_shareLoket.pln_nontaglis_n',

              'vw_transaksi_pln_nontaglis.loket_code AS loket_code','vw_transaksi_pln_nontaglis.loket_name AS loket_name',
              'vw_transaksi_pln_nontaglis.jenis_loket AS jenis_loket','vw_transaksi_pln_nontaglis.jenis_transaksi AS jenis_transaksi',

            DB::raw("sum(vw_transaksi_pln_nontaglis.tagihan) AS tagihan,sum(vw_transaksi_pln_nontaglis.admin) AS admin,
                  sum(vw_transaksi_pln_nontaglis.total) AS total,count(0) AS jumlah,'' as aksi"))
            ->whereMonth('vw_transaksi_pln_nontaglis.tanggal', $bulan)
            ->whereYear('vw_transaksi_pln_nontaglis.tanggal', $tahun);

    $list=DB::table('vw_transaksi_pln_prepaid')
            ->select('lokets.byadmin','lokets.tipe',
              'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
                'web_mntr_shareLoket.pln_postpaid_n',
                'web_mntr_shareLoket.pln_prepaid_n','web_mntr_shareLoket.pln_nontaglis_n',

              'vw_transaksi_pln_prepaid.loket_code AS loket_code','vw_transaksi_pln_prepaid.loket_name AS loket_name',
              'vw_transaksi_pln_prepaid.jenis_loket AS jenis_loket','vw_transaksi_pln_prepaid.jenis_transaksi AS jenis_transaksi',
              
            DB::raw("sum(vw_transaksi_pln_prepaid.tagihan) AS tagihan,sum(vw_transaksi_pln_prepaid.admin) AS admin,
                  sum(vw_transaksi_pln_prepaid.total) AS total,count(0) AS jumlah,'' as aksi"))
            ->whereMonth('vw_transaksi_pln_prepaid.tanggal', $bulan)
            ->whereYear('vw_transaksi_pln_prepaid.tanggal', $tahun);
    //jenis All, loket isi, jenis trans ALL   
    if  ($loket_code<>"-"&&$jenis_transaksi=="ALL"){      
      $pdam=$pdam->where('vw_pdambjm_trans.loket_code',$loket_code);
      $pdam=$pdam->leftJoin('lokets','lokets.loket_code','=','vw_pdambjm_trans.loket_code');

      $pln=$pln->where('vw_transaksi_pln.loket_code',$loket_code);
      $pln=$pln->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln.loket_code');

      $pln_nontaglis=$pln_nontaglis->where('vw_transaksi_pln_nontaglis.loket_code',$loket_code);
      $pln_nontaglis=$pln_nontaglis->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_nontaglis.loket_code');

      $list=$list->where('vw_transaksi_pln_prepaid.loket_code',$loket_code);
      $list=$list->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_prepaid.loket_code');
    } 
    //jenis All, loket isi, jenis trans isi          
    else if  ($loket_code<>"-"&&$jenis_transaksi<>"ALL"){
      $pdam=$pdam->where('vw_pdambjm_trans.loket_code',$loket_code)
                 ->where('vw_pdambjm_trans.jenis_transaksi','like', '%' .$jenis_transaksi. '%'); 
      $pdam=$pdam->leftJoin('lokets','lokets.loket_code','=','vw_pdambjm_trans.loket_code');

      $pln=$pln->where('vw_transaksi_pln.loket_code',$loket_code)
                 ->where('vw_transaksi_pln.jenis_transaksi','like', '%' .$jenis_transaksi. '%'); 
      $pln=$pln->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln.loket_code');

      $pln_nontaglis=$pln_nontaglis->where('vw_transaksi_pln_nontaglis.loket_code',$loket_code)
                 ->where('vw_transaksi_pln_nontaglis.jenis_transaksi','like', '%' .$jenis_transaksi. '%'); 
      $pln_nontaglis=$pln_nontaglis->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_nontaglis.loket_code');

      $list=$list->where('vw_transaksi_pln_prepaid.loket_code',$loket_code)
                 ->where('vw_transaksi_pln_prepaid.jenis_transaksi','like', '%' .$jenis_transaksi. '%'); 
      $list=$list->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_prepaid.loket_code');           
    }
    //jenis All, loket All, jenis trans isi          
    else if  ($loket_code=="-"&&$jenis_transaksi<>"ALL"&&$tipe=="ALL"){
      $pdam=$pdam->where('vw_pdambjm_trans.jenis_transaksi','like', '%' .$jenis_transaksi. '%');
      $pdam=$pdam->leftJoin('lokets','lokets.loket_code','=','vw_pdambjm_trans.loket_code');

      $pln=$pln->where('vw_transaksi_pln.jenis_transaksi','like', '%' .$jenis_transaksi. '%'); 
      $pln=$pln->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln.loket_code');

      $pln_nontaglis=$pln_nontaglis->where('vw_transaksi_pln_nontaglis.jenis_transaksi','like', '%' .$jenis_transaksi. '%'); 
      $pln_nontaglis=$pln_nontaglis->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_nontaglis.loket_code');

      $list=$list->where('vw_transaksi_pln_prepaid.jenis_transaksi','like', '%' .$jenis_transaksi. '%'); 
      $list=$list->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_prepaid.loket_code');
    }
    //jenis isi, loket ALL, jenis trans ALL
    else if ($loket_code=="-"&&$tipe<>"ALL"&&$jenis_transaksi=="ALL"){
      $pdam=$pdam->Join('lokets',function($join) use ($tipe)
                {
                  $join->on('lokets.loket_code','=','vw_pdambjm_trans.loket_code');
                  $join->where('lokets.tipe','=',$tipe);
                });
      $pln=$pln->Join('lokets',function($join) use ($tipe)
                {
                  $join->on('lokets.loket_code','=','vw_transaksi_pln.loket_code');
                  $join->where('lokets.tipe','=',$tipe);
                });
      $pln_nontaglis=$pln_nontaglis->Join('lokets',function($join) use ($tipe)
                {
                  $join->on('lokets.loket_code','=','vw_transaksi_pln_nontaglis.loket_code');
                  $join->where('lokets.tipe','=',$tipe);
                });
      $list=$list->Join('lokets',function($join) use ($tipe)
                {
                  $join->on('lokets.loket_code','=','vw_transaksi_pln_prepaid.loket_code');
                  $join->where('lokets.tipe','=',$tipe);
                });
    }
    //jenis isi, loket ALL, jenis trans isi
    else if ($loket_code=="-"&&$tipe<>"ALL"&&$jenis_transaksi<>"ALL"){
      $pdam=$pdam->where('vw_pdambjm_trans.jenis_transaksi','like', '%' .$jenis_transaksi. '%') 
              ->Join('lokets',function($join) use ($tipe)
                {
                  $join->on('lokets.loket_code','=','vw_pdambjm_trans.loket_code');
                  $join->where('lokets.tipe','=',$tipe);
                });
      $pln=$pln->where('vw_transaksi_pln.jenis_transaksi','like', '%' .$jenis_transaksi. '%') 
              ->Join('lokets',function($join) use ($tipe)
                {
                  $join->on('lokets.loket_code','=','vw_transaksi_pln.loket_code');
                  $join->where('lokets.tipe','=',$tipe);
                });
      $pln_nontaglis=$pln_nontaglis->where('vw_transaksi_pln_nontaglis.jenis_transaksi','like', '%' .$jenis_transaksi. '%') 
              ->Join('lokets',function($join) use ($tipe)
                {
                  $join->on('lokets.loket_code','=','vw_transaksi_pln_nontaglis.loket_code');
                  $join->where('lokets.tipe','=',$tipe);
                });
      $list=$list->where('vw_transaksi_pln_prepaid.jenis_transaksi','like', '%' .$jenis_transaksi. '%') 
              ->Join('lokets',function($join) use ($tipe)
                {
                  $join->on('lokets.loket_code','=','vw_transaksi_pln_prepaid.loket_code');
                  $join->where('lokets.tipe','=',$tipe);
                });        
    }
    //jenis ALL, loket ALL, jenis trans ALL
    else{
      $pdam=$pdam->leftJoin('lokets','lokets.loket_code','=','vw_pdambjm_trans.loket_code');
      $pln=$pln->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln.loket_code');
      $pln_nontaglis=$pln_nontaglis->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_nontaglis.loket_code');
      $list=$list->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_prepaid.loket_code');
    }
    //groupby
      $pdam=$pdam->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')
                  ->groupBy('lokets.byadmin','lokets.tipe',
                      'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                      'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
                      'web_mntr_shareLoket.pln_postpaid_n',
                      'web_mntr_shareLoket.pln_prepaid_n','web_mntr_shareLoket.pln_nontaglis_n',
                      'vw_pdambjm_trans.loket_code','vw_pdambjm_trans.loket_name',
                      'vw_pdambjm_trans.jenis_loket','vw_pdambjm_trans.jenis_transaksi'); 

      $pln=$pln->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')
                  ->groupBy('lokets.byadmin','lokets.tipe',
                      'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                      'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
                      'web_mntr_shareLoket.pln_postpaid_n',
                      'web_mntr_shareLoket.pln_prepaid_n','web_mntr_shareLoket.pln_nontaglis_n',
                      'vw_transaksi_pln.loket_code','vw_transaksi_pln.loket_name',
                      'vw_transaksi_pln.jenis_loket','vw_transaksi_pln.jenis_transaksi');

      $pln_nontaglis=$pln_nontaglis->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')
                  ->groupBy('lokets.byadmin','lokets.tipe',
                      'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                      'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
                      'web_mntr_shareLoket.pln_postpaid_n',
                      'web_mntr_shareLoket.pln_prepaid_n','web_mntr_shareLoket.pln_nontaglis_n',
                      'vw_transaksi_pln_nontaglis.loket_code','vw_transaksi_pln_nontaglis.loket_name',
                      'vw_transaksi_pln_nontaglis.jenis_loket','vw_transaksi_pln_nontaglis.jenis_transaksi');

      $list=$list->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')
                  ->groupBy('lokets.byadmin','lokets.tipe',
                      'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                      'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
                      'web_mntr_shareLoket.pln_postpaid_n',
                      'web_mntr_shareLoket.pln_prepaid_n','web_mntr_shareLoket.pln_nontaglis_n',
                      'vw_transaksi_pln_prepaid.loket_code','vw_transaksi_pln_prepaid.loket_name',
                      'vw_transaksi_pln_prepaid.jenis_loket','vw_transaksi_pln_prepaid.jenis_transaksi')
              ->union($pdam)
              ->union($pln)
              ->union($pln_nontaglis)
              ->get();
    
    return Response::json(array(
      'status' => 'Success',
      'message' => '-',
      'data' => $list
    ),200);
  }
  //

//
public function rekapFee()
{
  set_time_limit(0);
  return view('laporan.rekapFee');
}

public function rekapKasirBulanan($bln)
{
    set_time_limit(0);
    $bln_explode=explode("-", $bln);
    $tahun=$bln_explode[0];
    $bulan=$bln_explode[1];

    $pdam=DB::table('vw_pdambjm_trans')
              ->select('lokets.byadmin','lokets.tipe',
                'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
                'web_mntr_shareLoket.pln_postpaid_n',
                'web_mntr_shareLoket.pln_prepaid_n','web_mntr_shareLoket.pln_nontaglis_n',

                'vw_pdambjm_trans.loket_code AS loket_code','vw_pdambjm_trans.loket_name AS loket_name',
                'vw_pdambjm_trans.jenis_loket AS jenis_loket','vw_pdambjm_trans.jenis_transaksi AS jenis_transaksi',
   
              DB::raw("sum(vw_pdambjm_trans.tagihan) AS tagihan,sum(vw_pdambjm_trans.admin) AS admin,
                    sum(vw_pdambjm_trans.total) AS total,count(0) AS jumlah,'' as aksi"))
              ->whereMonth('vw_pdambjm_trans.tanggal', $bulan)
              ->whereYear('vw_pdambjm_trans.tanggal', $tahun); 

    $pln=DB::table('vw_transaksi_pln')
            ->select('lokets.byadmin','lokets.tipe',
                'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
                'web_mntr_shareLoket.pln_postpaid_n',
                'web_mntr_shareLoket.pln_prepaid_n','web_mntr_shareLoket.pln_nontaglis_n',

                'vw_transaksi_pln.loket_code AS loket_code','vw_transaksi_pln.loket_name AS loket_name',
                'vw_transaksi_pln.jenis_loket AS jenis_loket','vw_transaksi_pln.jenis_transaksi AS jenis_transaksi',

            DB::raw("sum(vw_transaksi_pln.tagihan) AS tagihan,sum(vw_transaksi_pln.admin) AS admin,
                  sum(vw_transaksi_pln.total) AS total,count(0) AS jumlah,'' as aksi"))
            ->whereMonth('vw_transaksi_pln.tanggal', $bulan)
            ->whereYear('vw_transaksi_pln.tanggal', $tahun);

    $pln_nontaglis=DB::table('vw_transaksi_pln_nontaglis')
            ->select('lokets.byadmin','lokets.tipe',
                'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
                'web_mntr_shareLoket.pln_postpaid_n',
                'web_mntr_shareLoket.pln_prepaid_n','web_mntr_shareLoket.pln_nontaglis_n',

                'vw_transaksi_pln_nontaglis.loket_code AS loket_code','vw_transaksi_pln_nontaglis.loket_name AS loket_name',
                'vw_transaksi_pln_nontaglis.jenis_loket AS jenis_loket','vw_transaksi_pln_nontaglis.jenis_transaksi AS jenis_transaksi',

            DB::raw("sum(vw_transaksi_pln_nontaglis.tagihan) AS tagihan,sum(vw_transaksi_pln_nontaglis.admin) AS admin,
                  sum(vw_transaksi_pln_nontaglis.total) AS total,count(0) AS jumlah,'' as aksi"))
            ->whereMonth('vw_transaksi_pln_nontaglis.tanggal', $bulan)
            ->whereYear('vw_transaksi_pln_nontaglis.tanggal', $tahun);

    $list=DB::table('vw_transaksi_pln_prepaid')
            ->select('lokets.byadmin','lokets.tipe',
              'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
              'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
              'web_mntr_shareLoket.pln_postpaid_n',
              'web_mntr_shareLoket.pln_prepaid_n','web_mntr_shareLoket.pln_nontaglis_n',

              'vw_transaksi_pln_prepaid.loket_code AS loket_code','vw_transaksi_pln_prepaid.loket_name AS loket_name',
              'vw_transaksi_pln_prepaid.jenis_loket AS jenis_loket','vw_transaksi_pln_prepaid.jenis_transaksi AS jenis_transaksi',
              
            DB::raw("sum(vw_transaksi_pln_prepaid.tagihan) AS tagihan,sum(vw_transaksi_pln_prepaid.admin) AS admin,
                  sum(vw_transaksi_pln_prepaid.total) AS total,count(0) AS jumlah,'' as aksi"))
            ->whereMonth('vw_transaksi_pln_prepaid.tanggal', $bulan)
            ->whereYear('vw_transaksi_pln_prepaid.tanggal', $tahun);

    //filter
    //loket pedami, jenis trans ALL
      $pdam=$pdam->Join('lokets',function($join)
                {
                  $join->on('lokets.loket_code','=','vw_pdambjm_trans.loket_code');
                  $join->where('lokets.tipe','=',1);
                });
      $pln=$pln->Join('lokets',function($join)
                {
                  $join->on('lokets.loket_code','=','vw_transaksi_pln.loket_code');
                  $join->where('lokets.tipe','=',1);
                });
      $pln_nontaglis=$pln_nontaglis->Join('lokets',function($join)
                {
                  $join->on('lokets.loket_code','=','vw_transaksi_pln_nontaglis.loket_code');
                  $join->where('lokets.tipe','=',1);
                });
      $list=$list->Join('lokets',function($join)
                {
                  $join->on('lokets.loket_code','=','vw_transaksi_pln_prepaid.loket_code');
                  $join->where('lokets.tipe','=',1);
                });

    //groupby
      $pdam=$pdam->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')
                  ->groupBy('lokets.byadmin','lokets.tipe',
                      'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                      'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
                      'web_mntr_shareLoket.pln_postpaid_n',
                      'web_mntr_shareLoket.pln_prepaid_n','web_mntr_shareLoket.pln_nontaglis_n',
                      'vw_pdambjm_trans.loket_code','vw_pdambjm_trans.loket_name',
                      'vw_pdambjm_trans.jenis_loket','vw_pdambjm_trans.jenis_transaksi'); 

      $pln=$pln->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')
                  ->groupBy('lokets.byadmin','lokets.tipe',
                      'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                      'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
                      'web_mntr_shareLoket.pln_postpaid_n',
                      'web_mntr_shareLoket.pln_prepaid_n','web_mntr_shareLoket.pln_nontaglis_n',
                      'vw_transaksi_pln.loket_code','vw_transaksi_pln.loket_name',
                      'vw_transaksi_pln.jenis_loket','vw_transaksi_pln.jenis_transaksi');

      $pln_nontaglis=$pln_nontaglis->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')
                  ->groupBy('lokets.byadmin','lokets.tipe',
                      'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                      'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
                      'web_mntr_shareLoket.pln_postpaid_n',
                      'web_mntr_shareLoket.pln_prepaid_n','web_mntr_shareLoket.pln_nontaglis_n',
                      'vw_transaksi_pln_nontaglis.loket_code','vw_transaksi_pln_nontaglis.loket_name',
                      'vw_transaksi_pln_nontaglis.jenis_loket','vw_transaksi_pln_nontaglis.jenis_transaksi');

      $list=$list->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')
                  ->groupBy('lokets.byadmin','lokets.tipe',
                      'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                      'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
                      'web_mntr_shareLoket.pln_postpaid_n',
                      'web_mntr_shareLoket.pln_prepaid_n','web_mntr_shareLoket.pln_nontaglis_n',
                      'vw_transaksi_pln_prepaid.loket_code','vw_transaksi_pln_prepaid.loket_name',
                      'vw_transaksi_pln_prepaid.jenis_loket','vw_transaksi_pln_prepaid.jenis_transaksi')
              ->union($pdam)
              ->union($pln)
              ->union($pln_nontaglis)
              ->get();
    

    function BulanIndo($bulan)
    {
      $BulanIndo = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");            
      $bln=explode("-", $bulan);
      $result = $BulanIndo[(int)$bln[1]-1]." ".$bln[0];     
      return($result);
    }

    $data = json_decode(json_encode($list), True);

    $lokets=lokets::where('tipe',1)->get();

    Excel::create('Rekap Kasir Payment '.BulanIndo($bln), function($excel) use($data,$bln,$lokets) 
    {
      //sheet1
      $excel->sheet(BulanIndo($bln), function($sheet)  use($data,$bln,$lokets)
      {
          $sheet->setStyle(array(
              'font' => array('name' =>  'Calibri')
          )); 
          $sheet->setWidth(array('A'=>5,'B'=>35,'C'=>10,'D'=>10,'E'=>10,'F'=>10,'G'=>10,'H'=>10,'I'=>10,'J'=>10,
                                 'K'=>10,'L'=>10,'M'=>10,'N'=>10));

          $sheet->mergeCells('A1:N1');
          $sheet->cell('A1', 'LAPORAN LEMBAR TRANSAKSI DAN FEE');

          $sheet->mergeCells('A2:N2');
          $sheet->cell('A2', 'APLIKASI PEDAMI PAYMENT');

          $sheet->mergeCells('A3:N3');
          $sheet->cell('A3', 'BULAN '.strtoupper(BulanIndo($bln)));        

          //header tabel
          $sheet->row(5, ['NO.','LOKET','PDAM BANDARMASIH','','','PLN','','','LUNASIN','','','TOTAL','','']);
          $sheet->mergeCells('A5:A6');
          $sheet->mergeCells('B5:B6');
          $sheet->mergeCells('C5:E5');
          $sheet->mergeCells('F5:H5');
          $sheet->mergeCells('I5:K5');
          $sheet->mergeCells('L5:N5');

          $sheet->cells('A1:N5', function($cells) {
              $cells->setAlignment('center');
              $cells->setValignment('center');
              $cells->setFontWeight('bold');
          });

          $sheet->row(6, ['','','Lembar','Jumlah Trx','Jumlah Fee','Lembar','Jumlah Trx','Jumlah Fee',
                              'Lembar','Jumlah Trx','Jumlah Fee','Lembar','Jumlah Trx','Jumlah Fee']);
          $sheet->cells('A6:N6', function($cells) {
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
          });
          //isi
          $y=7;  

          foreach ($lokets as $no => $lokets) 
          {
            $data_per_loket = array_values(array_filter($data, function($obj)use($lokets){
              if ($obj['loket_code']==$lokets->loket_code) {
                      return true;
              }
              return false;
            })); 

            //$jangka_pendek=array_sum(array_column($filterjangkaPendek, 'jumlah'));
            //pdam
            $data_pdam = array_values(array_filter($data_per_loket, function($obj){
              if ($obj['jenis_transaksi']=="PDAM_BANDARMASIH") {
                      return true;
              }
              return false;
            }));

            $jumlah_lembar_pdam=0;
            $jumlah_trx_pdam=0;
            $jumlah_fee_pdam=0;

            if (count($data_pdam)>0){
              $jumlah_lembar_pdam=$data_pdam[0]['jumlah'];
              $jumlah_trx_pdam=$data_pdam[0]['total'];
              $jumlah_fee_pdam=$data_pdam[0]['pdam']*$jumlah_lembar_pdam;
            }

            //
            //PLN
            $data_pln = array_values(array_filter($data_per_loket, function($obj){
              if ($obj['jenis_transaksi']!="PDAM_BANDARMASIH") {
                      return true;
              }
              return false;
            }));

            $jumlah_lembar_pln=0;
            $jumlah_trx_pln=0;
            $jumlah_fee_pln=0;

            if (count($data_pln)>0)
            {
              foreach ($data_pln as $data_pln) 
              {
                $jumlah_lembar_pln=$jumlah_lembar_pln+$data_pln['jumlah'];

                $jumlah_trx_pln=$jumlah_trx_pln+$data_pln['total'];

                switch ($data_pln['jenis_transaksi']) {
                  case 'PLN_POSTPAID':
                    $share=$data_pln['pln_postpaid'];
                  break;
                  case 'PLN_PREPAID':
                    $share=$data_pln['pln_prepaid'];
                  break;
                  case 'PLN_NONTAGLIS':
                    $share=$data_pln['pln_nontaglis'];
                  break;
                  case 'PLN_POSTPAID_N':
                    $share=$data_pln['pln_postpaid_n'];
                  break;
                  case 'PLN_PREPAID_N':
                    $share=$data_pln['pln_prepaid_n'];
                  break;
                  case 'PLN_NONTAGLIS_N':
                    $share=$data_pln['pln_prepaid_n'];
                  break;
                }

                $jumlah_fee_pln=$jumlah_fee_pln+($share*$data_pln['jumlah']);
              }
            }

            //
            $sheet->row($y, [$no+1,$lokets->nama,$jumlah_lembar_pdam,$jumlah_trx_pdam,$jumlah_fee_pdam,
                        $jumlah_lembar_pln,$jumlah_trx_pln,$jumlah_fee_pln,'0','0','0',
                        '=C'.$y.'+F'.$y.'+I'.$y,
                        '=D'.$y.'+G'.$y.'+J'.$y,
                        '=E'.$y.'+H'.$y.'+K'.$y]);
            $y++;
          }  

          $endrow=$y-1;

          $sheet->row($y, ['','JUMLAH','=SUM(C7:C'.$endrow.')','=SUM(D7:D'.$endrow.')',
                          '=SUM(E7:E'.$endrow.')','=SUM(F7:F'.$endrow.')',
                          '=SUM(G7:G'.$endrow.')','=SUM(H7:H'.$endrow.')',
                          '=SUM(I7:I'.$endrow.')','=SUM(J7:J'.$endrow.')',
                          '=SUM(K7:K'.$endrow.')','=SUM(L7:L'.$endrow.')',
                          '=SUM(M7:M'.$endrow.')','=SUM(N7:N'.$endrow.')']);  
                              
          $sheet->cells('A'.$y.':N'.$y, function($cells) {
              $cells->setFontWeight('bold');
          });
          $sheet->cells('B'.$y, function($cells) {
              $cells->setFontWeight('bold');
          });
          //border
          $sheet->setBorder('A5:N'.$y, 'thin');
          $sheet->getStyle('C5:N'.$y)->getNumberFormat()->setFormatCode('#,##0');  
                  
      });
    })->export('xls');
    
    return back();   
}

public function rekapFeeBulanan($bln)
{
    set_time_limit(0);
    $bln_explode=explode("-", $bln);
    $tahun=$bln_explode[0];
    $bulan=$bln_explode[1];

    $pdam=DB::table('vw_pdambjm_trans')
              ->select('lokets.byadmin','lokets.tipe',
                'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
                'web_mntr_shareLoket.pln_postpaid_n',
                'web_mntr_shareLoket.pln_prepaid_n','web_mntr_shareLoket.pln_nontaglis_n',

                'vw_pdambjm_trans.loket_code AS loket_code','vw_pdambjm_trans.loket_name AS loket_name',
                'vw_pdambjm_trans.jenis_loket AS jenis_loket','vw_pdambjm_trans.jenis_transaksi AS jenis_transaksi',
   
              DB::raw("sum(vw_pdambjm_trans.tagihan) AS tagihan,sum(vw_pdambjm_trans.admin) AS admin,
                    sum(vw_pdambjm_trans.total) AS total,count(0) AS jumlah,'' as aksi"))
              ->whereMonth('vw_pdambjm_trans.tanggal', $bulan)
              ->whereYear('vw_pdambjm_trans.tanggal', $tahun); 

    $pln=DB::table('vw_transaksi_pln')
            ->select('lokets.byadmin','lokets.tipe',
                'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
                'web_mntr_shareLoket.pln_postpaid_n',
                'web_mntr_shareLoket.pln_prepaid_n','web_mntr_shareLoket.pln_nontaglis_n',

                'vw_transaksi_pln.loket_code AS loket_code','vw_transaksi_pln.loket_name AS loket_name',
                'vw_transaksi_pln.jenis_loket AS jenis_loket','vw_transaksi_pln.jenis_transaksi AS jenis_transaksi',

            DB::raw("sum(vw_transaksi_pln.tagihan) AS tagihan,sum(vw_transaksi_pln.admin) AS admin,
                  sum(vw_transaksi_pln.total) AS total,count(0) AS jumlah,'' as aksi"))
            ->whereMonth('vw_transaksi_pln.tanggal', $bulan)
            ->whereYear('vw_transaksi_pln.tanggal', $tahun);

    $pln_nontaglis=DB::table('vw_transaksi_pln_nontaglis')
            ->select('lokets.byadmin','lokets.tipe',
                'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
                'web_mntr_shareLoket.pln_postpaid_n',
                'web_mntr_shareLoket.pln_prepaid_n','web_mntr_shareLoket.pln_nontaglis_n',

                'vw_transaksi_pln_nontaglis.loket_code AS loket_code','vw_transaksi_pln_nontaglis.loket_name AS loket_name',
                'vw_transaksi_pln_nontaglis.jenis_loket AS jenis_loket','vw_transaksi_pln_nontaglis.jenis_transaksi AS jenis_transaksi',

            DB::raw("sum(vw_transaksi_pln_nontaglis.tagihan) AS tagihan,sum(vw_transaksi_pln_nontaglis.admin) AS admin,
                  sum(vw_transaksi_pln_nontaglis.total) AS total,count(0) AS jumlah,'' as aksi"))
            ->whereMonth('vw_transaksi_pln_nontaglis.tanggal', $bulan)
            ->whereYear('vw_transaksi_pln_nontaglis.tanggal', $tahun);

    $list=DB::table('vw_transaksi_pln_prepaid')
            ->select('lokets.byadmin','lokets.tipe',
              'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
              'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
              'web_mntr_shareLoket.pln_postpaid_n',
              'web_mntr_shareLoket.pln_prepaid_n','web_mntr_shareLoket.pln_nontaglis_n',

              'vw_transaksi_pln_prepaid.loket_code AS loket_code','vw_transaksi_pln_prepaid.loket_name AS loket_name',
              'vw_transaksi_pln_prepaid.jenis_loket AS jenis_loket','vw_transaksi_pln_prepaid.jenis_transaksi AS jenis_transaksi',
              
            DB::raw("sum(vw_transaksi_pln_prepaid.tagihan) AS tagihan,sum(vw_transaksi_pln_prepaid.admin) AS admin,
                  sum(vw_transaksi_pln_prepaid.total) AS total,count(0) AS jumlah,'' as aksi"))
            ->whereMonth('vw_transaksi_pln_prepaid.tanggal', $bulan)
            ->whereYear('vw_transaksi_pln_prepaid.tanggal', $tahun);

    //left join
    //loket pedami, jenis trans ALL
      $pdam=$pdam->LeftJoin('lokets','lokets.loket_code','=','vw_pdambjm_trans.loket_code');

      $pln=$pln->LeftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln.loket_code');

      $pln_nontaglis=$pln_nontaglis->LeftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_nontaglis.loket_code');

      $list=$list->LeftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_prepaid.loket_code');

    //groupby
      $pdam=$pdam->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')
                  ->groupBy('lokets.byadmin','lokets.tipe',
                      'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                      'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
                      'web_mntr_shareLoket.pln_postpaid_n',
                      'web_mntr_shareLoket.pln_prepaid_n','web_mntr_shareLoket.pln_nontaglis_n',
                      'vw_pdambjm_trans.loket_code','vw_pdambjm_trans.loket_name',
                      'vw_pdambjm_trans.jenis_loket','vw_pdambjm_trans.jenis_transaksi'); 

      $pln=$pln->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')
                  ->groupBy('lokets.byadmin','lokets.tipe',
                      'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                      'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
                      'web_mntr_shareLoket.pln_postpaid_n',
                      'web_mntr_shareLoket.pln_prepaid_n','web_mntr_shareLoket.pln_nontaglis_n',
                      'vw_transaksi_pln.loket_code','vw_transaksi_pln.loket_name',
                      'vw_transaksi_pln.jenis_loket','vw_transaksi_pln.jenis_transaksi');

      $pln_nontaglis=$pln_nontaglis->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')
                  ->groupBy('lokets.byadmin','lokets.tipe',
                      'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                      'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
                      'web_mntr_shareLoket.pln_postpaid_n',
                      'web_mntr_shareLoket.pln_prepaid_n','web_mntr_shareLoket.pln_nontaglis_n',
                      'vw_transaksi_pln_nontaglis.loket_code','vw_transaksi_pln_nontaglis.loket_name',
                      'vw_transaksi_pln_nontaglis.jenis_loket','vw_transaksi_pln_nontaglis.jenis_transaksi');

      $list=$list->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')
                  ->groupBy('lokets.byadmin','lokets.tipe',
                      'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                      'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
                      'web_mntr_shareLoket.pln_postpaid_n',
                      'web_mntr_shareLoket.pln_prepaid_n','web_mntr_shareLoket.pln_nontaglis_n',
                      'vw_transaksi_pln_prepaid.loket_code','vw_transaksi_pln_prepaid.loket_name',
                      'vw_transaksi_pln_prepaid.jenis_loket','vw_transaksi_pln_prepaid.jenis_transaksi')
              ->union($pdam)
              ->union($pln)
              ->union($pln_nontaglis)
              ->get();
    

    function BulanIndo($bulan)
    {
      $BulanIndo = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");            
      $bln=explode("-", $bulan);
      $result = $BulanIndo[(int)$bln[1]-1]." ".$bln[0];     
      return($result);
    }

    function TanggalIndo($tanggal)
    {
        $tgl=explode("-",$tanggal);
        $BulanIndo = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", 
                            "September", "Oktober", "November", "Desember");            
        $result = $tgl[2]." ".$BulanIndo[(int)$tgl[1]-1]." ".$tgl[0];    
        return($result);
    }

    $data = json_decode(json_encode($list), True);

    $lokets=lokets::where('tipe',1)->get();
    $loket_switching=lokets::where('jenis','SWITCHING')->get();

    Excel::create('Rekap Fee '.BulanIndo($bln), function($excel) use($data,$bln,$lokets,$loket_switching) 
    {
      //sheet1
      $excel->sheet(BulanIndo($bln), function($sheet)  use($data,$bln,$lokets,$loket_switching)
      {
          $sheet->setStyle(array(
              'font' => array('name' =>  'Calibri')
          )); 
          $sheet->setWidth(array('A'=>5,'B'=>35,'C'=>10,'D'=>10,'E'=>10,'F'=>10,'G'=>10,'H'=>10,'I'=>10,'J'=>10,'K'=>10,'L'=>10,'M'=>10,'N'=>15));

          $sheet->mergeCells('A1:N1');
          $sheet->cell('A1', 'LAPORAN LEMBAR TRANSAKSI DAN FEE');

          $sheet->mergeCells('A2:N2');
          $sheet->cell('A2', 'APLIKASI PEDAMI PAYMENT');

          $sheet->mergeCells('A3:N3');
          $sheet->cell('A3', 'BULAN '.strtoupper(BulanIndo($bln)));        

          //header tabel
          $sheet->row(5, ['NO.','LOKET / USER ','PRODUK','','','','','','','','','','','TOTAL FEE']);
          $sheet->mergeCells('C5:M5');
          $sheet->row(6, ['','','PDAM BANDARMASIH','','','','','','PLN','','','','','']);
          $sheet->mergeCells('C6:H6');
          $sheet->mergeCells('I6:M6');
          $sheet->row(7, ['','','LOKET KASIR @ 2.500','','ANDROID PM @ 1.250','','SWITCHING @ 300','',
                          'LOKET KASIR @ 2.500','','','ANDROID PM @ 1.000','','']);
          $sheet->mergeCells('C7:D7');
          $sheet->mergeCells('E7:F7');
          $sheet->mergeCells('G7:H7');
          $sheet->mergeCells('I7:K7');
          $sheet->mergeCells('L7:M7');

          $sheet->row(8, ['','','Lembar','Rp.','Lembar','Rp.','Lembar','Rp.',
                          '2.500/Lbr','2.400/Lbr','Rp.','Lembar','Rp.','']);

          $sheet->mergeCells('A5:A8');
          $sheet->mergeCells('B5:B8');
          $sheet->mergeCells('N5:N8');

          $sheet->cells('A1:N8', function($cells) {
              $cells->setAlignment('center');
              $cells->setValignment('center');

          });
          $sheet->cells('A5:N8', function($cells) {
              $cells->setBackground('#F2F2F2');
          });

          $sheet->cells('A1:N7', function($cells) {
              $cells->setFontWeight('bold');
          });

          //isi
          //array jenis
          $jenis_loket=array('1','2','3','4'); //1=pedami,2=petugas lapangan, 3= loket luar, 4=switching

          $y=9;  

          for ($i=0; $i <count($jenis_loket) ; $i++) 
          { 
            //jika loket pedami
            if ($jenis_loket[$i]=='1')
            {
              foreach ($lokets as $no => $lokets) 
              {
                $data_per_loket = array_values(array_filter($data, function($obj)use($lokets){
                  if ($obj['loket_code']==$lokets->loket_code) {
                          return true;
                  }
                  return false;
                })); 

                //PDAM
                  $data_pdam = array_values(array_filter($data_per_loket, function($obj){
                    if ($obj['jenis_transaksi']=="PDAM_BANDARMASIH") {
                            return true;
                    }
                    return false;
                  }));

                  $jumlah_lembar_pdam=0;
                  if (count($data_pdam)>0){
                    $jumlah_lembar_pdam=$data_pdam[0]['jumlah'];
                  }
                //
                //PLN 
                //PLN OLD (adzikru)
                  $data_pln_old = array_values(array_filter($data_per_loket, function($obj){
                    if ($obj['jenis_transaksi']=="PLN_POSTPAID"||$obj['jenis_transaksi']=="PLN_PREPAID"||
                      $obj['jenis_transaksi']=="PLN_NONTAGLIS") {
                            return true;
                    }
                    return false;
                  }));

                  $jumlah_lembar_pln=0;
                  if (count($data_pln_old)>0)
                  {
                    $jumlah_lembar_pln=array_sum(array_column($data_pln_old, 'jumlah'));
                  }
                  
                //
                //PLN NEW (lunasin)
                  $data_pln_new = array_values(array_filter($data_per_loket, function($obj){
                    if ($obj['jenis_transaksi']=="PLN_POSTPAID_N"||$obj['jenis_transaksi']=="PLN_PREPAID_N"||
                      $obj['jenis_transaksi']=="PLN_NONTAGLIS_N") {
                            return true;
                    }
                    return false;
                  }));

                  $jumlah_lembar_pln_new=0;
                  if (count($data_pln_new)>0)
                  {
                    $jumlah_lembar_pln_new=array_sum(array_column($data_pln_new, 'jumlah'));
                  }
                //
                //add to row
                $sheet->row($y, [$y-8,strtoupper($lokets->nama),$jumlah_lembar_pdam,'=C'.$y.'*2500',
                                '','','','',
                                $jumlah_lembar_pln,$jumlah_lembar_pln_new,'=(I'.$y.'*2500)+(J'.$y.'*2400)',
                                '','',
                                '=D'.$y.'+F'.$y.'+H'.$y.'+K'.$y.'+M'.$y]);
                $y++;
              }//end foreach
            }//endif loket pedami
            //jika petugas lapangan dan loket luar
            else if ($jenis_loket[$i]=='2'||$jenis_loket[$i]=='3')
            {
              //filter
              //jika petugas lapangan
              if ($jenis_loket[$i]=='2')
              {
                $data_per_loket = array_values(array_filter($data, function($obj){
                  if ($obj['tipe']=='3') {
                          return true;
                  }
                  return false;
                })); 

                $nama_loket='Petugas Lapangan';
              }
              //jika loket luar
              else
              {
                $data_per_loket = array_values(array_filter($data, function($obj){
                  if ($obj['tipe']=='2'||$obj['tipe']=='5') {
                          return true;
                  }
                  return false;
                })); 

                $nama_loket='Loket Luar';
              }

              //PDAM
                $data_pdam = array_values(array_filter($data_per_loket, function($obj){
                      if ($obj['jenis_transaksi']=="PDAM_BANDARMASIH") {
                              return true;
                      }
                      return false;
                }));

                $jumlah_lembar_pdam=0;
                if (count($data_pdam)>0)
                {
                  $jumlah_lembar_pdam=array_sum(array_column($data_pdam, 'jumlah'));
                }
              //
              //PLN
                $data_pln = array_values(array_filter($data_per_loket, function($obj){
                      if ($obj['jenis_transaksi']!="PDAM_BANDARMASIH") {
                              return true;
                      }
                      return false;
                }));

                $jumlah_lembar_pln=0;
                if (count($data_pln)>0)
                {
                  $jumlah_lembar_pln=array_sum(array_column($data_pln, 'jumlah'));
                }
              //
              //add to row
                $sheet->row($y, [$y-8,strtoupper($nama_loket),'','',
                                $jumlah_lembar_pdam,'=E'.$y.'*1250','','',
                                '','','',
                                $jumlah_lembar_pln,'=L'.$y.'*1000',
                                '=D'.$y.'+F'.$y.'+H'.$y.'+K'.$y.'+M'.$y]);
                $y++;
              //
            }//end if petugas lapangan dan loket luar
            else //jika loket switching
            if ($jenis_loket[$i]=='4')
            {
              foreach ($loket_switching as $no => $lokets) 
              {
                $data_per_loket = array_values(array_filter($data, function($obj)use($lokets){
                  if ($obj['loket_code']==$lokets->loket_code) {
                          return true;
                  }
                  return false;
                })); 

                //PDAM
                  $data_pdam = array_values(array_filter($data_per_loket, function($obj){
                    if ($obj['jenis_transaksi']=="PDAM_BANDARMASIH") {
                            return true;
                    }
                    return false;
                  }));

                  $jumlah_lembar_pdam=0;
                  if (count($data_pdam)>0)
                  {
                    $jumlah_lembar_pdam=$data_pdam[0]['jumlah'];
                    //add to row
                    $sheet->row($y, [$y-8,strtoupper($lokets->nama),'','',
                                '','',$jumlah_lembar_pdam,'=G'.$y.'*300',
                                '','','','','',
                                '=D'.$y.'+F'.$y.'+H'.$y.'+K'.$y.'+M'.$y]);
                    $y++;
                    
                  }
                //                
              }//end foreach
            }//endif loket switching
          }// end for jenis loket


          //end isi
          $endrow=$y-1;

          $sheet->row($y, ['','JUMLAH','=SUM(C9:C'.$endrow.')','=SUM(D9:D'.$endrow.')',
                          '=SUM(E9:E'.$endrow.')','=SUM(F9:F'.$endrow.')',
                          '=SUM(G9:G'.$endrow.')','=SUM(H9:H'.$endrow.')',
                          '=SUM(I9:I'.$endrow.')','=SUM(J9:J'.$endrow.')',
                          '=SUM(K9:K'.$endrow.')','=SUM(L9:L'.$endrow.')',
                          '=SUM(M9:M'.$endrow.')','=SUM(N9:N'.$endrow.')']);  
                              
          $sheet->cells('A'.$y.':N'.$y, function($cells) {
              $cells->setFontWeight('bold');
              $cells->setBackground('#F2F2F2');
          });

          $sheet->getStyle('A1:N'.$y)->getAlignment()->setWrapText(true);
          //border
          $sheet->setBorder('A5:N'.$y, 'thin');
          $sheet->getStyle('C5:N'.$y)->getNumberFormat()->setFormatCode('#,##0');  

          //footer
            $y++;$y++;
            $baris=$y;
            $sheet->mergeCells('L'.$y.':N'.$y);
            $tanggal=date('Y-m-d',strtotime('+8 hours'));
            $sheet->cell('L'.$y, 'Banjarmasin, '.TanggalIndo($tanggal)); 

            $y++;
            $sheet->mergeCells('B'.$y.':C'.$y);
            $sheet->cell('B'.$y, 'Mengetahui,');
            $sheet->mergeCells('L'.$y.':N'.$y);
            $sheet->cell('L'.$y, 'Dibuat Oleh,'); 

            $y++;
            $sheet->mergeCells('B'.$y.':C'.$y);
            $sheet->cell('B'.$y, 'Manager Kopkar PEDAMI');
            $sheet->mergeCells('L'.$y.':N'.$y);
            $sheet->cell('L'.$y, 'Koordinator Kasir'); 

            $y++;$y++;$y++;$y++;
            $sheet->mergeCells('B'.$y.':C'.$y);
            $sheet->cell('B'.$y, 'Ahmad Supiani, S.Ag');
            $sheet->mergeCells('L'.$y.':N'.$y);
            $sheet->cell('L'.$y, 'Nurul Latifah');

            $baris++;
            $sheet->cell('B'.$y.':N'.$y, function($cells){
                //$cells->setFontSize(14);
                // $cells->setAlignment('center');
                // $cells->setValignment('center');
                $cells->setFontWeight('bold');
            });
                  
      });
    })->export('xls');
    
    return back();   
}

public function laporanPendapatan($bln)
{
    set_time_limit(0);
    $bln_explode=explode("-", $bln);
    $tahun=$bln_explode[0];
    $bulan=$bln_explode[1];

    $pdam=DB::table('vw_pdambjm_trans')
              ->select('lokets.byadmin','lokets.tipe',
                'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
                'web_mntr_shareLoket.pln_postpaid_n',
                'web_mntr_shareLoket.pln_prepaid_n','web_mntr_shareLoket.pln_nontaglis_n',

                'vw_pdambjm_trans.loket_code AS loket_code','vw_pdambjm_trans.loket_name AS loket_name',
                'vw_pdambjm_trans.jenis_loket AS jenis_loket','vw_pdambjm_trans.jenis_transaksi AS jenis_transaksi',
   
              DB::raw("sum(vw_pdambjm_trans.tagihan) AS tagihan,sum(vw_pdambjm_trans.admin) AS admin,
                    sum(vw_pdambjm_trans.total) AS total,count(0) AS jumlah,'' as aksi"))
              ->whereMonth('vw_pdambjm_trans.tanggal', $bulan)
              ->whereYear('vw_pdambjm_trans.tanggal', $tahun); 

    $pln=DB::table('vw_transaksi_pln')
            ->select('lokets.byadmin','lokets.tipe',
                'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
                'web_mntr_shareLoket.pln_postpaid_n',
                'web_mntr_shareLoket.pln_prepaid_n','web_mntr_shareLoket.pln_nontaglis_n',

                'vw_transaksi_pln.loket_code AS loket_code','vw_transaksi_pln.loket_name AS loket_name',
                'vw_transaksi_pln.jenis_loket AS jenis_loket','vw_transaksi_pln.jenis_transaksi AS jenis_transaksi',

            DB::raw("sum(vw_transaksi_pln.tagihan) AS tagihan,sum(vw_transaksi_pln.admin) AS admin,
                  sum(vw_transaksi_pln.total) AS total,count(0) AS jumlah,'' as aksi"))
            ->whereMonth('vw_transaksi_pln.tanggal', $bulan)
            ->whereYear('vw_transaksi_pln.tanggal', $tahun);

    $pln_nontaglis=DB::table('vw_transaksi_pln_nontaglis')
            ->select('lokets.byadmin','lokets.tipe',
                'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
                'web_mntr_shareLoket.pln_postpaid_n',
                'web_mntr_shareLoket.pln_prepaid_n','web_mntr_shareLoket.pln_nontaglis_n',

                'vw_transaksi_pln_nontaglis.loket_code AS loket_code','vw_transaksi_pln_nontaglis.loket_name AS loket_name',
                'vw_transaksi_pln_nontaglis.jenis_loket AS jenis_loket','vw_transaksi_pln_nontaglis.jenis_transaksi AS jenis_transaksi',

            DB::raw("sum(vw_transaksi_pln_nontaglis.tagihan) AS tagihan,sum(vw_transaksi_pln_nontaglis.admin) AS admin,
                  sum(vw_transaksi_pln_nontaglis.total) AS total,count(0) AS jumlah,'' as aksi"))
            ->whereMonth('vw_transaksi_pln_nontaglis.tanggal', $bulan)
            ->whereYear('vw_transaksi_pln_nontaglis.tanggal', $tahun);

    $list=DB::table('vw_transaksi_pln_prepaid')
            ->select('lokets.byadmin','lokets.tipe',
              'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
              'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
              'web_mntr_shareLoket.pln_postpaid_n',
              'web_mntr_shareLoket.pln_prepaid_n','web_mntr_shareLoket.pln_nontaglis_n',

              'vw_transaksi_pln_prepaid.loket_code AS loket_code','vw_transaksi_pln_prepaid.loket_name AS loket_name',
              'vw_transaksi_pln_prepaid.jenis_loket AS jenis_loket','vw_transaksi_pln_prepaid.jenis_transaksi AS jenis_transaksi',
              
            DB::raw("sum(vw_transaksi_pln_prepaid.tagihan) AS tagihan,sum(vw_transaksi_pln_prepaid.admin) AS admin,
                  sum(vw_transaksi_pln_prepaid.total) AS total,count(0) AS jumlah,'' as aksi"))
            ->whereMonth('vw_transaksi_pln_prepaid.tanggal', $bulan)
            ->whereYear('vw_transaksi_pln_prepaid.tanggal', $tahun);

    //left join
    //loket pedami, jenis trans ALL
      $pdam=$pdam->LeftJoin('lokets','lokets.loket_code','=','vw_pdambjm_trans.loket_code');

      $pln=$pln->LeftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln.loket_code');

      $pln_nontaglis=$pln_nontaglis->LeftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_nontaglis.loket_code');

      $list=$list->LeftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_prepaid.loket_code');

    //groupby
      $pdam=$pdam->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')
                  ->groupBy('lokets.byadmin','lokets.tipe',
                      'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                      'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
                      'web_mntr_shareLoket.pln_postpaid_n',
                      'web_mntr_shareLoket.pln_prepaid_n','web_mntr_shareLoket.pln_nontaglis_n',
                      'vw_pdambjm_trans.loket_code','vw_pdambjm_trans.loket_name',
                      'vw_pdambjm_trans.jenis_loket','vw_pdambjm_trans.jenis_transaksi'); 

      $pln=$pln->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')
                  ->groupBy('lokets.byadmin','lokets.tipe',
                      'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                      'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
                      'web_mntr_shareLoket.pln_postpaid_n',
                      'web_mntr_shareLoket.pln_prepaid_n','web_mntr_shareLoket.pln_nontaglis_n',
                      'vw_transaksi_pln.loket_code','vw_transaksi_pln.loket_name',
                      'vw_transaksi_pln.jenis_loket','vw_transaksi_pln.jenis_transaksi');

      $pln_nontaglis=$pln_nontaglis->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')
                  ->groupBy('lokets.byadmin','lokets.tipe',
                      'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                      'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
                      'web_mntr_shareLoket.pln_postpaid_n',
                      'web_mntr_shareLoket.pln_prepaid_n','web_mntr_shareLoket.pln_nontaglis_n',
                      'vw_transaksi_pln_nontaglis.loket_code','vw_transaksi_pln_nontaglis.loket_name',
                      'vw_transaksi_pln_nontaglis.jenis_loket','vw_transaksi_pln_nontaglis.jenis_transaksi');

      $list=$list->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')
                  ->groupBy('lokets.byadmin','lokets.tipe',
                      'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                      'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
                      'web_mntr_shareLoket.pln_postpaid_n',
                      'web_mntr_shareLoket.pln_prepaid_n','web_mntr_shareLoket.pln_nontaglis_n',
                      'vw_transaksi_pln_prepaid.loket_code','vw_transaksi_pln_prepaid.loket_name',
                      'vw_transaksi_pln_prepaid.jenis_loket','vw_transaksi_pln_prepaid.jenis_transaksi')
              ->union($pdam)
              ->union($pln)
              ->union($pln_nontaglis)
              ->get();
    

    function BulanIndo($bulan)
    {
      $BulanIndo = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");            
      $bln=explode("-", $bulan);
      $result = $BulanIndo[(int)$bln[1]-1]." ".$bln[0];     
      return($result);
    }

    function TanggalIndo($tanggal)
    {
        $tgl=explode("-",$tanggal);
        $BulanIndo = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", 
                            "September", "Oktober", "November", "Desember");            
        $result = $tgl[2]." ".$BulanIndo[(int)$tgl[1]-1]." ".$tgl[0];    
        return($result);
    }

    $data = json_decode(json_encode($list), True);

    $lokets=lokets::where('tipe',1)->get();
    $loket_switching=lokets::where('jenis','SWITCHING')->get();

    Excel::create('Laporan Pendapatan '.BulanIndo($bln), function($excel) use($data,$bln,$lokets,$loket_switching) 
    {
      //sheet1
      $excel->sheet('PEDAMI INCOME', function($sheet)  use($data,$bln,$lokets,$loket_switching)
      {
          $sheet->setStyle(array(
              'font' => array('name' =>  'Calibri')
          )); 
          $sheet->setWidth(array('A'=>5,'B'=>35,'C'=>15,'D'=>15,'E'=>20));

          $sheet->mergeCells('A1:E1');
          $sheet->cell('A1', 'KOPERASI KARYAWAN');

          $sheet->mergeCells('A2:E2');
          $sheet->cell('A2', '"PERUSAHAAN DAERAH AIR MINUM KOTA BANJRMASIN"');

          $sheet->mergeCells('A3:E3');
          $sheet->cell('A3', 'Jl. Jenderal A. Yani KM 2,5 Telp. (0511) 3253544 Fax (0511) 3251722 Banjarmasin');        

          $objDrawing = new PHPExcel_Worksheet_Drawing;
          $objDrawing->setPath(public_path()."/image/pedami.png"); //your image path
          $objDrawing->setCoordinates('A1');
          //$objDrawing->setWidthAndHeight(100,40);
          $objDrawing->setResizeProportional(false);
          $objDrawing->setHeight(70);
          $objDrawing->setWidth(70);
          $objDrawing->setWorksheet($sheet);

          $objDrawing = new PHPExcel_Worksheet_Drawing;
          $objDrawing->setPath(public_path()."/image/logo.png"); //your image path
          $objDrawing->setCoordinates('E1');
          //$objDrawing->setWidthAndHeight(100,40);
          $objDrawing->setResizeProportional(false);
          $objDrawing->setHeight(70);
          $objDrawing->setWidth(70);
          //center
              $colWidth = $sheet->getColumnDimension('E')->getWidth();
              if ($colWidth == -1) { 
                  $colWidthPixels = 44; 
              } else {                 
                  $colWidthPixels = $colWidth * 7.0017094; 
              }
              $offsetX = $colWidthPixels - $objDrawing->getWidth(); 
              $objDrawing->setOffsetX($offsetX); 
            //end center
          $objDrawing->setWorksheet($sheet);


          $sheet->mergeCells('A5:E5');
          $sheet->cell('A5', 'MONTHLY REPORT');
          $sheet->mergeCells('A6:E6');
          $sheet->cell('A6', 'PAYMENT POINT ONLINE BANKING (PPOB)');
          $sheet->mergeCells('A7:E7');
          $sheet->cell('A7', 'KOPERASI KARYAWAN PEDAMI BANJARMASIN');  
          $sheet->mergeCells('A8:E8');
          $sheet->cell('A8', strtoupper(BulanIndo($bln)));  

          $sheet->cells('A1:E8', function($cells) {
              $cells->setAlignment('center');
              $cells->setValignment('center');

          });

          $sheet->mergeCells('A10:B10');
          $sheet->cell('A10', '1. PENDAPATAN');

          //header tabel
          $sheet->row(12, ['No.','Rekanan','Fee/lembar','Kuantitas','Jumlah']);

          $sheet->cells('A12:E12', function($cells) {
              $cells->setAlignment('center');
              $cells->setValignment('center');

          });

          $sheet->cells('A1:E12', function($cells) {
              $cells->setFontWeight('bold');
          });

          //isi
          //array jenis
          $jenis_loket=array('1','2','3','4','5'); 
          //1=pedami,2=petugas lapangan, 3= loket luar, 4=loket luar H+1, 5=switching

          $y=13;  

          for ($i=0; $i <count($jenis_loket) ; $i++) 
          { 
            //jika loket pedami
            if ($jenis_loket[$i]=='1')
            {
              //PDAM
              $data_pdam = array_values(array_filter($data, function($obj){
                if ($obj['jenis_transaksi']=="PDAM_BANDARMASIH"&&$obj['tipe']=="1") {
                        return true;
                }
                return false;
              }));

              $jumlah_lembar_pdam=0;
              if (count($data_pdam)>0)
              {
                $jumlah_lembar_pdam=array_sum(array_column($data_pdam, 'jumlah'));
              }

              //add to row
              $sheet->row($y, [$y-12,'Kasir Bantu',2500,$jumlah_lembar_pdam,'=C'.$y.'*D'.$y]);
              $y++;
            }//endif loket pedami
            //jika petugas lapangan dan loket luar
            else if ($jenis_loket[$i]=='2'||$jenis_loket[$i]=='3'||$jenis_loket[$i]=='4')
            {
              //filter
              //jika petugas lapangan
              if ($jenis_loket[$i]=='2')
              {
                $data_pdam = array_values(array_filter($data, function($obj){
                  if ($obj['tipe']=='3'&&$obj['jenis_transaksi']=="PDAM_BANDARMASIH") {
                          return true;
                  }
                  return false;
                })); 

                $nama_loket='Loket Petugas Lapangan';
              }
              //jika loket luar
              else  if ($jenis_loket[$i]=='3')
              {
                $data_pdam = array_values(array_filter($data, function($obj){
                  if ($obj['tipe']=='2'&&$obj['jenis_transaksi']=="PDAM_BANDARMASIH") {
                          return true;
                  }
                  return false;
                })); 

                $nama_loket='Loket Luar';
              }
              //jika loket luar H + 1
              else
              {
                $data_pdam = array_values(array_filter($data, function($obj){
                  if ($obj['tipe']=='5'&&$obj['jenis_transaksi']=="PDAM_BANDARMASIH") {
                          return true;
                  }
                  return false;
                })); 

                $nama_loket='Loket Luar H+1';
              }

              $jumlah_lembar_pdam=0;
              if (count($data_pdam)>0)
              {
                $jumlah_lembar_pdam=array_sum(array_column($data_pdam, 'jumlah'));
              }
              //add to row
              $sheet->row($y, [$y-12,$nama_loket,2500,$jumlah_lembar_pdam,'=C'.$y.'*D'.$y]);
              $y++;
              //
            }//end if petugas lapangan dan loket luar
            else //jika loket switching
            if ($jenis_loket[$i]=='5')
            {
              foreach ($loket_switching as $no => $lokets) 
              {
                $data_per_loket = array_values(array_filter($data, function($obj)use($lokets){
                  if ($obj['loket_code']==$lokets->loket_code&&$obj['jenis_transaksi']=="PDAM_BANDARMASIH") {
                          return true;
                  }
                  return false;
                })); 

                $jumlah_lembar_pdam=0;
                if (count($data_per_loket)>0)
                {
                  $jumlah_lembar_pdam=$data_per_loket[0]['jumlah'];
                  //add to row
                  $sheet->row($y, [$y-12,$lokets->nama,300,$jumlah_lembar_pdam,'=C'.$y.'*D'.$y]);
                  $y++;
                }
                //                
              }//end foreach
            }//endif loket switching
          }// end for jenis loket
          $sheet->row($y, [$y-12,"Lunasin",1200,0,'=C'.$y.'*D'.$y]);
          $y++;
          //end isi
          $endrow=$y-1;

          $sheet->row($y, ['Total Keseluruhan','','','=SUM(D12:D'.$endrow.')','=SUM(E12:E'.$endrow.')']);  
          $sheet->mergeCells('A'.$y.':C'.$y);

          $sheet->cells('A'.$y.':E'.$y, function($cells) {
              $cells->setFontWeight('bold');
              // $cells->setBackground('#F2F2F2');
          });

          $sheet->getStyle('B12:B'.$y)->getAlignment()->setWrapText(true);
          //border
          $sheet->setBorder('A12:E'.$y, 'thin');
          $sheet->getStyle('C13:C'.$y)->getNumberFormat()->setFormatCode('Rp* #,##0');  
          $sheet->getStyle('D13:D'.$y)->getNumberFormat()->setFormatCode('#,##0'); 
          $sheet->getStyle('E13:E'.$y)->getNumberFormat()->setFormatCode('Rp* #,##0'); 

          $y=$y+2;
          $awalttd=$y;
          $sheet->mergeCells('A'.$y.':E'.$y);
          $sheet->cell('A'.$y, 'Dilaporkan oleh,');

          $y=$y+5;
          $sheet->cell('B'.$y, 'Nurul Latifah');
          $sheet->mergeCells('C'.$y.':D'.$y);
          $sheet->cell('C'.$y, "A'an Syaputra");
          $sheet->cell('E'.$y, "Nur Halimah");
          $sheet->cells('A'.$y.':E'.$y, function($cells) {
              $cells->setFontWeight('bold');
          });
          $sheet->getStyle('A'.$y.':E'.$y)->getFont()->setUnderline(true);

          $y++;
          $sheet->cell('B'.$y, 'Koordinator Kasir');
          $sheet->mergeCells('C'.$y.':D'.$y);
          $sheet->cell('C'.$y, "IT");
          $sheet->cell('E'.$y, "Keuangan");

          $y=$y+2;
          $sheet->mergeCells('A'.$y.':E'.$y);
          $sheet->cell('A'.$y, 'Diketahui oleh,');

          $y=$y+5;
          $sheet->mergeCells('B'.$y.':C'.$y);
          $sheet->cell('B'.$y, "H. Taufiqurrahman, SE");
          $sheet->mergeCells('D'.$y.':E'.$y);
          $sheet->cell('D'.$y, "Ahmad Supiani, S. Ag");
          $sheet->cells('A'.$y.':E'.$y, function($cells) {
              $cells->setFontWeight('bold');
          });
          $sheet->getStyle('A'.$y.':E'.$y)->getFont()->setUnderline(true);

          $y++;
          $sheet->mergeCells('B'.$y.':C'.$y);
          $sheet->cell('B'.$y, "Ketua");
          $sheet->mergeCells('D'.$y.':E'.$y);
          $sheet->cell('D'.$y, "Manager");

          $sheet->cells('A'.$awalttd.':E'.$y, function($cells) {
              $cells->setAlignment('center');
              $cells->setValignment('center');
          });
                  
      });

      //sheet 2
      $excel->sheet('TBB INCOME', function($sheet)  use($data,$bln)
      {
          $sheet->setStyle(array(
              'font' => array('name' =>  'Calibri')
          )); 
          $sheet->setWidth(array('A'=>5,'B'=>35,'C'=>15,'D'=>15,'E'=>20));

          $sheet->mergeCells('A1:E1');
          $sheet->cell('A1', 'PT TIRTA BANUA BUNGAS');

          $sheet->mergeCells('A2:E2');
          $sheet->cell('A2', '');

          $sheet->mergeCells('A3:E3');
          $sheet->cell('A3', 'Jl. Sultan Adam No 16 B Kota Banjarmasin Telp (0511) 3251722');        

          $objDrawing = new PHPExcel_Worksheet_Drawing;
          $objDrawing->setPath(public_path()."/image/index.jpg"); //your image path
          $objDrawing->setCoordinates('A1');
          //$objDrawing->setWidthAndHeight(100,40);
          $objDrawing->setResizeProportional(false);
          $objDrawing->setHeight(70);
          $objDrawing->setWidth(70);
          $objDrawing->setWorksheet($sheet);

          $sheet->mergeCells('A5:E5');
          $sheet->cell('A5', 'MONTHLY REPORT');
          $sheet->mergeCells('A6:E6');
          $sheet->cell('A6', 'PAYMENT POINT ONLINE BANKING (PPOB)');
          $sheet->mergeCells('A7:E7');
          $sheet->cell('A7', 'PT TIRTA BANUA BUNGAS');  
          $sheet->mergeCells('A8:E8');
          $sheet->cell('A8', strtoupper(BulanIndo($bln)));  

          $sheet->cells('A1:E8', function($cells) {
              $cells->setAlignment('center');
              $cells->setValignment('center');

          });

          $sheet->mergeCells('A10:B10');
          $sheet->cell('A10', '1. PENDAPATAN');

          $sheet->mergeCells('A12:B12');
          $sheet->cell('A12', '1.1 PENDAPATAN PPOB PLN');

          //header tabel
          $sheet->row(14, ['No.','Rekanan','Fee/lembar','Kuantitas','Jumlah']);

          $sheet->cells('A14:E14', function($cells) {
              $cells->setAlignment('center');
              $cells->setValignment('center');

          });

          $sheet->cells('A1:E14', function($cells) {
              $cells->setFontWeight('bold');
          });

          //isi
          //array jenis
          $jenis_loket=array('1','2','3','4'); 
          //1=pedami,2=petugas lapangan, 3= loket luar, 4= lunasin

          $y=15;  

          for ($i=0; $i <count($jenis_loket) ; $i++) 
          { 
            //jika loket pedami
            if ($jenis_loket[$i]=='1')
            {
              //PLN ADZIKRU
              $data_pln = array_values(array_filter($data, function($obj){
                if (($obj['jenis_transaksi']=="PLN_POSTPAID"||$obj['jenis_transaksi']=="PLN_PREPAID"||
                      $obj['jenis_transaksi']=="PLN_NONTAGLIS")&&$obj['tipe']=="1") {
                        return true;
                }
                return false;
              }));

              $jumlah_lembar_pln=0;
              if (count($data_pln)>0)
              {
                $jumlah_lembar_pln=array_sum(array_column($data_pln, 'jumlah'));
              }

              //add to row
              $sheet->row($y, [$y-12,'Kasir Bantu',2500,$jumlah_lembar_pln,'=C'.$y.'*D'.$y]);
              $y++;
            }//endif loket pedami
            //jika petugas lapangan dan loket luar
            else if ($jenis_loket[$i]=='2'||$jenis_loket[$i]=='3')
            {
              //filter
              //jika petugas lapangan
              if ($jenis_loket[$i]=='2')
              {
                $data_pln = array_values(array_filter($data, function($obj){
                  if ($obj['tipe']=='3'&&($obj['jenis_transaksi']=="PLN_POSTPAID"
                    ||$obj['jenis_transaksi']=="PLN_PREPAID"||
                      $obj['jenis_transaksi']=="PLN_NONTAGLIS")) {
                          return true;
                  }
                  return false;
                })); 

                $nama_loket='Petugas Lapangan';
              }
              //jika loket luar
              else  if ($jenis_loket[$i]=='3')
              {
                $data_pln = array_values(array_filter($data, function($obj){
                  if (($obj['tipe']=='2'||$obj['tipe']=='5')&&
                    ($obj['jenis_transaksi']=="PLN_POSTPAID"||
                      $obj['jenis_transaksi']=="PLN_PREPAID"||
                      $obj['jenis_transaksi']=="PLN_NONTAGLIS")) 
                  {
                          return true;
                  }
                  return false;
                })); 

                $nama_loket='Loket Luar';
              }
              $jumlah_lembar_pln=0;
              if (count($data_pln)>0)
              {
                $jumlah_lembar_pln=array_sum(array_column($data_pln, 'jumlah'));
              }
              //add to row
              $sheet->row($y, [$y-12,$nama_loket,2500,$jumlah_lembar_pln,'=C'.$y.'*D'.$y]);
              $y++;
              //
            }//end if petugas lapangan dan loket luar
            else //jika Lunasin
            if ($jenis_loket[$i]=='4')
            {
              //PLN LUNASIN
              $data_pln = array_values(array_filter($data, function($obj){
                if ($obj['jenis_transaksi']=="PLN_POSTPAID_N"||$obj['jenis_transaksi']=="PLN_PREPAID_N"||
                      $obj['jenis_transaksi']=="PLN_NONTAGLIS_N") {
                        return true;
                }
                return false;
              }));

              $jumlah_lembar_pln=0;
              if (count($data_pln)>0)
              {
                $jumlah_lembar_pln=array_sum(array_column($data_pln, 'jumlah'));
              }

              //add to row
              $sheet->row($y, [$y-12,'H2H Cakrawala',2400,$jumlah_lembar_pln,'=C'.$y.'*D'.$y]);
              $y++;
            }//endif loket switching
          }// end for jenis loket
          $sheet->row($y, [$y-12,"Lunasin",2300,0,'=C'.$y.'*D'.$y]);
          $y++;
          //end isi
          $endrow=$y-1;

          $sheet->row($y, ['Total Keseluruhan','','','=SUM(D12:D'.$endrow.')','=SUM(E12:E'.$endrow.')']);  
          $sheet->mergeCells('A'.$y.':C'.$y);

          $sheet->cells('A'.$y.':E'.$y, function($cells) {
              $cells->setFontWeight('bold');
              // $cells->setBackground('#F2F2F2');
          });

          $sheet->getStyle('B16:B'.$y)->getAlignment()->setWrapText(true);
          //border
          $sheet->setBorder('A14:E'.$y, 'thin');
          $sheet->getStyle('C15:C'.$y)->getNumberFormat()->setFormatCode('Rp* #,##0');  
          $sheet->getStyle('D15:D'.$y)->getNumberFormat()->setFormatCode('#,##0'); 
          $sheet->getStyle('E15:E'.$y)->getNumberFormat()->setFormatCode('Rp* #,##0'); 
          $y++;
          $y++;

          //tabel 2
          $awalbaris=$y;
          $sheet->mergeCells('A'.$y.':B'.$y);
          $sheet->cell('A'.$y, '1.2 PENDAPATAN PPOB LAIN-LAIN');
          $y++;
          //header tabel
          $sheet->row($y, ['No.','Produk','Fee/lembar','Kuantitas','Jumlah']);
          $sheet->cells('A'.$y.':E'.$y, function($cells) {
              $cells->setAlignment('center');
              $cells->setValignment('center');

          });

          $sheet->cells('A'.$awalbaris.':E'.$y, function($cells) {
              $cells->setFontWeight('bold');
          });
          $y++;

          $sheet->row($y, ['1','Lunasin','','','']);
          $sheet->mergeCells('B'.$y.':E'.$y);
          $y++;
          $awaltabel2=$y;
          $sheet->row($y, ['','PDAM Intan Banjar dan BPJS','0','0','=C'.$y.'*D'.$y]);
          $y++;
          $sheet->row($y, ['','Telkom','0','0','=C'.$y.'*D'.$y]);
          $y++;
          $sheet->row($y, ['','Kartu Halo ','0','0','=C'.$y.'*D'.$y]);
          $y++;
          $sheet->row($y, ['','Transaksi Pembaca Meter','0','0','=C'.$y.'*D'.$y]);
          $y++;
          $endrow2=$y-1;
          $sheet->row($y, ['Total Keseluruhan','','','=SUM(D'.$awaltabel2.':D'.$endrow2.')','=SUM(E'.$awaltabel2.':E'.$endrow2.')']);  
          $sheet->mergeCells('A'.$y.':C'.$y);
          $sheet->cells('A'.$y.':E'.$y, function($cells) {
              $cells->setFontWeight('bold');
          });
          //border
          $sheet->setBorder('A'.$awalbaris.':E'.$y, 'thin');
          $sheet->getStyle('C'.$awalbaris.':C'.$y)->getNumberFormat()->setFormatCode('Rp* #,##0');  
          $sheet->getStyle('D'.$awalbaris.':D'.$y)->getNumberFormat()->setFormatCode('#,##0'); 
          $sheet->getStyle('E'.$awalbaris.':E'.$y)->getNumberFormat()->setFormatCode('Rp* #,##0'); 


          $y=$y+2;
          $awalttd=$y;
          $sheet->mergeCells('A'.$y.':E'.$y);
          $sheet->cell('A'.$y, 'Dilaporkan oleh,');

          $y=$y+5;
          $sheet->cell('B'.$y, 'Nurul Latifah');
          $sheet->mergeCells('C'.$y.':D'.$y);
          $sheet->cell('C'.$y, "A'an Syaputra");
          $sheet->cell('E'.$y, "Nur Halimah");
          $sheet->cells('A'.$y.':E'.$y, function($cells) {
              $cells->setFontWeight('bold');
          });
          $sheet->getStyle('A'.$y.':E'.$y)->getFont()->setUnderline(true);

          $y++;
          $sheet->cell('B'.$y, 'Koordinator Kasir');
          $sheet->mergeCells('C'.$y.':D'.$y);
          $sheet->cell('C'.$y, "IT");
          $sheet->cell('E'.$y, "Keuangan");

          $y=$y+2;
          $sheet->mergeCells('A'.$y.':E'.$y);
          $sheet->cell('A'.$y, 'Diketahui oleh,');

          $y=$y+5;
          $sheet->mergeCells('B'.$y.':C'.$y);
          $sheet->cell('B'.$y, "H. Taufiqurrahman, SE");
          $sheet->mergeCells('D'.$y.':E'.$y);
          $sheet->cell('D'.$y, "Ahmad Supiani, S. Ag");
          $sheet->cells('A'.$y.':E'.$y, function($cells) {
              $cells->setFontWeight('bold');
          });
          $sheet->getStyle('A'.$y.':E'.$y)->getFont()->setUnderline(true);

          $y++;
          $sheet->mergeCells('B'.$y.':C'.$y);
          $sheet->cell('B'.$y, "Ketua");
          $sheet->mergeCells('D'.$y.':E'.$y);
          $sheet->cell('D'.$y, "Manager");

          $sheet->cells('A'.$awalttd.':E'.$y, function($cells) {
              $cells->setAlignment('center');
              $cells->setValignment('center');
          });

      });
    })->export('xls');
    
    return back();   
}
//

public function testpdf() {
  $list = transaksi::select('vw_rekap_transaksi.*','lokets.byadmin')
      ->whereMonth('tanggal','5')
      ->whereYear('tanggal','2017')
      ->where('vw_rekap_transaksi.loket_code','LBERUNTUNG')
      //->where('vw_rekap_transaksi.jenis_transaksi','PDAM_BANDARMASIH') 
      ->leftJoin('lokets','lokets.loket_code','=','vw_rekap_transaksi.loket_code')
      ->get();

  $list= $list->toArray();   

 return view('laporan.bulanan')->with("list",$list);
}

public function pdfBulananNew($tahun,$bulan,$loket_code,$jenis_transaksi) {
  set_time_limit(0);
  switch ($jenis_transaksi)
  {
  case 'PDAM_BANDARMASIH'   :  
    $list=DB::table('vw_pdambjm_trans')
              ->select( DB::raw('count(0) AS jumlah, lokets.byadmin'),
                        'vw_pdambjm_trans.tanggal','vw_pdambjm_trans.loket_name',
                        'vw_pdambjm_trans.jenis_transaksi','lokets.tipe',
                        'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                        'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis')
              ->whereMonth('vw_pdambjm_trans.tanggal',$bulan)
              ->whereYear('vw_pdambjm_trans.tanggal',$tahun)
              ->where('vw_pdambjm_trans.loket_code',$loket_code)
              //->where('vw_pdambjm_trans.jenis_transaksi', 'like', '%' .$jenis_transaksi. '%')
              ->leftJoin('lokets','lokets.loket_code','=','vw_pdambjm_trans.loket_code') 
              ->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')
              ->groupBy('vw_pdambjm_trans.tanggal','vw_pdambjm_trans.jenis_transaksi',
                'vw_pdambjm_trans.loket_name','lokets.byadmin','lokets.tipe',
                'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis')
              ->get();
  break;
  case 'PLN_POSTPAID'   :        
    $list=DB::table('vw_transaksi_pln')
              ->select( DB::raw('count(0) AS jumlah, lokets.byadmin'),
                        'vw_transaksi_pln.tanggal','vw_transaksi_pln.loket_name',
                        'vw_transaksi_pln.jenis_transaksi','lokets.tipe',
                        'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                        'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis')
              ->whereMonth('vw_transaksi_pln.tanggal',$bulan)
              ->whereYear('vw_transaksi_pln.tanggal',$tahun)
              ->where('vw_transaksi_pln.loket_code',$loket_code)
              //->where('vw_transaksi_pln.jenis_transaksi', 'like', '%' .$jenis_transaksi. '%')
              ->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln.loket_code') 
              ->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')
              ->groupBy('vw_transaksi_pln.tanggal','vw_transaksi_pln.jenis_transaksi',
                'vw_transaksi_pln.loket_name','lokets.byadmin','lokets.tipe',
                'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis')
              ->get();
  break;            
  case 'PLN_NONTAGLIS'   :
    $list=DB::table('vw_transaksi_pln_nontaglis')
              ->select( DB::raw('count(0) AS jumlah, lokets.byadmin'),
                        'vw_transaksi_pln_nontaglis.tanggal','vw_transaksi_pln_nontaglis.loket_name',
                        'vw_transaksi_pln_nontaglis.jenis_transaksi','lokets.tipe',
                        'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                        'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis')
              ->whereMonth('vw_transaksi_pln_nontaglis.tanggal',$bulan)
              ->whereYear('vw_transaksi_pln_nontaglis.tanggal',$tahun)
              ->where('vw_transaksi_pln_nontaglis.loket_code',$loket_code)
              //->where('vw_transaksi_pln_nontaglis.jenis_transaksi', 'like', '%' .$jenis_transaksi. '%')
              ->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_nontaglis.loket_code') 
              ->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')
              ->groupBy('vw_transaksi_pln_nontaglis.tanggal','vw_transaksi_pln_nontaglis.jenis_transaksi',
                'vw_transaksi_pln_nontaglis.loket_name','lokets.byadmin','lokets.tipe',
                'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis')
              ->get();
  break;  
  case 'PLN_PREPAID'   :
    $list=DB::table('vw_transaksi_pln_prepaid')
              ->select( DB::raw('count(0) AS jumlah, lokets.byadmin'),
                        'vw_transaksi_pln_prepaid.tanggal','vw_transaksi_pln_prepaid.loket_name',
                        'vw_transaksi_pln_prepaid.jenis_transaksi','lokets.tipe',
                        'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                        'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis')
              ->whereMonth('vw_transaksi_pln_prepaid.tanggal',$bulan)
              ->whereYear('vw_transaksi_pln_prepaid.tanggal',$tahun)
              ->where('vw_transaksi_pln_prepaid.loket_code',$loket_code)
              //->where('vw_transaksi_pln_prepaid.jenis_transaksi', 'like', '%' .$jenis_transaksi. '%')
              ->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_prepaid.loket_code') 
              ->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')
              ->groupBy('vw_transaksi_pln_prepaid.tanggal','vw_transaksi_pln_prepaid.jenis_transaksi',
                'vw_transaksi_pln_prepaid.loket_name','lokets.byadmin','lokets.tipe',
                'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis')           
              ->get();  
  break;
  }              

  $list=$list->toArray();

      function BulanIndo($bulan){
        $BulanIndo = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");            
        $result = $BulanIndo[(int)$bulan-1];;     
        return($result);
      } 
  $bulan=BulanIndo($bulan);    
  $content = view('laporan.bulanan')
          ->with('list',$list)
          ->with('bulan',$bulan)
          ->with('tahun',$tahun)
          ->render();     

  $pdf = new PDF();
  $pdf->loadHTML($content);
  return $pdf->Stream('document.pdf');
}

public function excellBulananNew($tahun,$bulan,$loket_code,$jenis_transaksi) {
  set_time_limit(0);  
  function BulanIndo($bulan){
    $BulanIndo = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");            
    $result = $BulanIndo[(int)$bulan-1];;     
    return($result);
  }

  switch ($jenis_transaksi)
  {
  case 'PDAM_BANDARMASIH'   :

    $list=DB::table('vw_pdambjm_trans')
                ->select('idpel', 'nama','periode','tanggal','tagihan','admin','total','user_',
                          'loket_name','loket_code','jenis_loket','jenis_transaksi')
                ->whereMonth('tanggal',$bulan)
                ->whereYear('tanggal',$tahun)
                ->where('loket_code',$loket_code)
                ->orderBy('tanggal')
                ->get();  
  break;
  case 'PLN_POSTPAID'   :

    $list=DB::table('vw_transaksi_pln')
            ->select('idpel', 'nama','periode','tanggal','tagihan','admin','total','user_',
                        'loket_name','loket_code','jenis_loket','jenis_transaksi')
            ->whereMonth('tanggal',$bulan)
            ->whereYear('tanggal',$tahun)
            ->where('loket_code',$loket_code)
            ->orderBy('tanggal')
            ->get();
  break;
  case 'PLN_NONTAGLIS'   :          

    $list=DB::table('vw_transaksi_pln_nontaglis')
            ->select('idpel', 'nama','periode','tanggal','tagihan','admin','total','user_',
                        'loket_name','loket_code','jenis_loket','jenis_transaksi')
            ->whereMonth('tanggal',$bulan)
            ->whereYear('tanggal',$tahun)
            ->where('loket_code',$loket_code)
            ->where('jenis_transaksi',$jenis_transaksi)
            ->orderBy('tanggal')
            ->get();
  break;  
  case 'PLN_PREPAID'   :        

    $list=DB::table('vw_transaksi_pln_prepaid')
            ->select('idpel', 'nama','periode','tanggal','tagihan','admin','total','user_',
                        'loket_name','loket_code','jenis_loket','jenis_transaksi')
            ->whereMonth('tanggal',$bulan)
            ->whereYear('tanggal',$tahun)
            ->where('loket_code',$loket_code)
            ->orderBy('tanggal')
            ->get();  
  break;
  }                                  

    $data = json_decode(json_encode($list), True);
        
    Excel::create('Data Detail Transaksi Bulan '.BulanIndo($bulan).' '.$tahun, function($excel) use($data,$tahun,$bulan) {
    //sheet1
    $excel->sheet('Detail', function($sheet)  use($data,$tahun,$bulan){
        $sheet->setStyle(array(
            'font' => array('name' =>  'Times New Roman')
        )); 
        $sheet->setAutoSize(true);
        $sheet->mergeCells('A1:L1');
        $sheet->cell('A1', function($cells) use($data,$tahun,$bulan){
            $cells->setAlignment('center');
            $cells->setValignment('center');
            $cells->setFontWeight('bold');
            $cells->setValue('Detail Transaksi Bulan : '.BulanIndo($bulan).' '.$tahun);
        });
        //header tabel
        $sheet->row(3, ['ID Pelanggan','Nama','Bulan Tahun','Tanggal','Tagihan','Admin','Total','User','Loket','Kode Loket','Jenis Loket','Jenis Transaksi']);

        //isi
        $y=4;   
        for ($x = 0; $x < count($data); $x++){
            $sheet->row($y, $data[$x]);
            $y+=1;
        }        
        //border
        $kolom="A";
        for ($x = 0; $x < 11; $x++){
            $kolom++;
        }
        $sheet->setBorder('A3:'.$kolom.''.$y, 'thin');
                
        });
    })->export('xls');
    return back();
}

public function pdfNew($tahun,$bulan,$loket_code,$jenis_transaksi,$tipe) {
  set_time_limit(0);
    if  ($loket_code<>"-"){
      $lokets=lokets::where('id',$loket_code)->first(); 
      $loket_code=$lokets->loket_code;
    }

    $pdam=DB::table('vw_pdambjm_trans')
              ->select('lokets.byadmin','lokets.tipe',
                'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',

                'vw_pdambjm_trans.loket_code AS loket_code','vw_pdambjm_trans.loket_name AS loket_name',
                'vw_pdambjm_trans.jenis_loket AS jenis_loket','vw_pdambjm_trans.jenis_transaksi AS jenis_transaksi',
   
              DB::raw("sum(vw_pdambjm_trans.tagihan) AS tagihan,sum(vw_pdambjm_trans.admin) AS admin,
                    sum(vw_pdambjm_trans.total) AS total,count(0) AS jumlah,'' as aksi"))
              ->whereMonth('vw_pdambjm_trans.tanggal', $bulan)
              ->whereYear('vw_pdambjm_trans.tanggal', $tahun); 

    $pln=DB::table('vw_transaksi_pln')
            ->select('lokets.byadmin','lokets.tipe',
                'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',

                'vw_transaksi_pln.loket_code AS loket_code','vw_transaksi_pln.loket_name AS loket_name',
                'vw_transaksi_pln.jenis_loket AS jenis_loket','vw_transaksi_pln.jenis_transaksi AS jenis_transaksi',

            DB::raw("sum(vw_transaksi_pln.tagihan) AS tagihan,sum(vw_transaksi_pln.admin) AS admin,
                  sum(vw_transaksi_pln.total) AS total,count(0) AS jumlah,'' as aksi"))
            ->whereMonth('vw_transaksi_pln.tanggal', $bulan)
            ->whereYear('vw_transaksi_pln.tanggal', $tahun);

    $pln_nontaglis=DB::table('vw_transaksi_pln_nontaglis')
            ->select('lokets.byadmin','lokets.tipe',
                'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',

              'vw_transaksi_pln_nontaglis.loket_code AS loket_code','vw_transaksi_pln_nontaglis.loket_name AS loket_name',
              'vw_transaksi_pln_nontaglis.jenis_loket AS jenis_loket','vw_transaksi_pln_nontaglis.jenis_transaksi AS jenis_transaksi',

            DB::raw("sum(vw_transaksi_pln_nontaglis.tagihan) AS tagihan,sum(vw_transaksi_pln_nontaglis.admin) AS admin,
                  sum(vw_transaksi_pln_nontaglis.total) AS total,count(0) AS jumlah,'' as aksi"))
            ->whereMonth('vw_transaksi_pln_nontaglis.tanggal', $bulan)
            ->whereYear('vw_transaksi_pln_nontaglis.tanggal', $tahun);

    $list=DB::table('vw_transaksi_pln_prepaid')
            ->select('lokets.byadmin','lokets.tipe',
              'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',

              'vw_transaksi_pln_prepaid.loket_code AS loket_code','vw_transaksi_pln_prepaid.loket_name AS loket_name',
              'vw_transaksi_pln_prepaid.jenis_loket AS jenis_loket','vw_transaksi_pln_prepaid.jenis_transaksi AS jenis_transaksi',
              
            DB::raw("sum(vw_transaksi_pln_prepaid.tagihan) AS tagihan,sum(vw_transaksi_pln_prepaid.admin) AS admin,
                  sum(vw_transaksi_pln_prepaid.total) AS total,count(0) AS jumlah,'' as aksi"))
            ->whereMonth('vw_transaksi_pln_prepaid.tanggal', $bulan)
            ->whereYear('vw_transaksi_pln_prepaid.tanggal', $tahun);
    //jenis All, loket isi, jenis trans ALL   
    if  ($loket_code<>"-"&&$jenis_transaksi=="ALL"){      
      $pdam=$pdam->where('vw_pdambjm_trans.loket_code',$loket_code);
      $pdam=$pdam->leftJoin('lokets','lokets.loket_code','=','vw_pdambjm_trans.loket_code');

      $pln=$pln->where('vw_transaksi_pln.loket_code',$loket_code);
      $pln=$pln->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln.loket_code');

      $pln_nontaglis=$pln_nontaglis->where('vw_transaksi_pln_nontaglis.loket_code',$loket_code);
      $pln_nontaglis=$pln_nontaglis->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_nontaglis.loket_code');

      $list=$list->where('vw_transaksi_pln_prepaid.loket_code',$loket_code);
      $list=$list->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_prepaid.loket_code');
    } 
    //jenis All, loket isi, jenis trans isi          
    else if  ($loket_code<>"-"&&$jenis_transaksi<>"ALL"){
      $pdam=$pdam->where('vw_pdambjm_trans.loket_code',$loket_code)
                 ->where('vw_pdambjm_trans.jenis_transaksi','like', '%' .$jenis_transaksi. '%'); 
      $pdam=$pdam->leftJoin('lokets','lokets.loket_code','=','vw_pdambjm_trans.loket_code');

      $pln=$pln->where('vw_transaksi_pln.loket_code',$loket_code)
                 ->where('vw_transaksi_pln.jenis_transaksi','like', '%' .$jenis_transaksi. '%'); 
      $pln=$pln->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln.loket_code');

      $pln_nontaglis=$pln_nontaglis->where('vw_transaksi_pln_nontaglis.loket_code',$loket_code)
                 ->where('vw_transaksi_pln_nontaglis.jenis_transaksi','like', '%' .$jenis_transaksi. '%'); 
      $pln_nontaglis=$pln_nontaglis->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_nontaglis.loket_code');

      $list=$list->where('vw_transaksi_pln_prepaid.loket_code',$loket_code)
                 ->where('vw_transaksi_pln_prepaid.jenis_transaksi','like', '%' .$jenis_transaksi. '%'); 
      $list=$list->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_prepaid.loket_code');           
    }
    //jenis All, loket All, jenis trans isi          
    else if  ($loket_code=="-"&&$jenis_transaksi<>"ALL"&&$tipe=="ALL"){
      $pdam=$pdam->where('vw_pdambjm_trans.jenis_transaksi','like', '%' .$jenis_transaksi. '%');
      $pdam=$pdam->leftJoin('lokets','lokets.loket_code','=','vw_pdambjm_trans.loket_code');

      $pln=$pln->where('vw_transaksi_pln.jenis_transaksi','like', '%' .$jenis_transaksi. '%'); 
      $pln=$pln->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln.loket_code');

      $pln_nontaglis=$pln_nontaglis->where('vw_transaksi_pln_nontaglis.jenis_transaksi','like', '%' .$jenis_transaksi. '%'); 
      $pln_nontaglis=$pln_nontaglis->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_nontaglis.loket_code');

      $list=$list->where('vw_transaksi_pln_prepaid.jenis_transaksi','like', '%' .$jenis_transaksi. '%'); 
      $list=$list->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_prepaid.loket_code');
    }
    //jenis isi, loket ALL, jenis trans ALL
    else if ($loket_code=="-"&&$tipe<>"ALL"&&$jenis_transaksi=="ALL"){
      $pdam=$pdam->Join('lokets',function($join) use ($tipe)
                {
                  $join->on('lokets.loket_code','=','vw_pdambjm_trans.loket_code');
                  $join->where('lokets.tipe','=',$tipe);
                });
      $pln=$pln->Join('lokets',function($join) use ($tipe)
                {
                  $join->on('lokets.loket_code','=','vw_transaksi_pln.loket_code');
                  $join->where('lokets.tipe','=',$tipe);
                });
      $pln_nontaglis=$pln_nontaglis->Join('lokets',function($join) use ($tipe)
                {
                  $join->on('lokets.loket_code','=','vw_transaksi_pln_nontaglis.loket_code');
                  $join->where('lokets.tipe','=',$tipe);
                });
      $list=$list->Join('lokets',function($join) use ($tipe)
                {
                  $join->on('lokets.loket_code','=','vw_transaksi_pln_prepaid.loket_code');
                  $join->where('lokets.tipe','=',$tipe);
                });
    }
    //jenis isi, loket ALL, jenis trans isi
    else if ($loket_code=="-"&&$tipe<>"ALL"&&$jenis_transaksi<>"ALL"){
      $pdam=$pdam->where('vw_pdambjm_trans.jenis_transaksi','like', '%' .$jenis_transaksi. '%') 
              ->Join('lokets',function($join) use ($tipe)
                {
                  $join->on('lokets.loket_code','=','vw_pdambjm_trans.loket_code');
                  $join->where('lokets.tipe','=',$tipe);
                });
      $pln=$pln->where('vw_transaksi_pln.jenis_transaksi','like', '%' .$jenis_transaksi. '%') 
              ->Join('lokets',function($join) use ($tipe)
                {
                  $join->on('lokets.loket_code','=','vw_transaksi_pln.loket_code');
                  $join->where('lokets.tipe','=',$tipe);
                });
      $pln_nontaglis=$pln_nontaglis->where('vw_transaksi_pln_nontaglis.jenis_transaksi','like', '%' .$jenis_transaksi. '%') 
              ->Join('lokets',function($join) use ($tipe)
                {
                  $join->on('lokets.loket_code','=','vw_transaksi_pln_nontaglis.loket_code');
                  $join->where('lokets.tipe','=',$tipe);
                });
      $list=$list->where('vw_transaksi_pln_prepaid.jenis_transaksi','like', '%' .$jenis_transaksi. '%') 
              ->Join('lokets',function($join) use ($tipe)
                {
                  $join->on('lokets.loket_code','=','vw_transaksi_pln_prepaid.loket_code');
                  $join->where('lokets.tipe','=',$tipe);
                });        
    }
    //jenis ALL, loket ALL, jenis trans ALL
    else{
      $pdam=$pdam->leftJoin('lokets','lokets.loket_code','=','vw_pdambjm_trans.loket_code');
      $pln=$pln->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln.loket_code');
      $pln_nontaglis=$pln_nontaglis->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_nontaglis.loket_code');
      $list=$list->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_prepaid.loket_code');
    }
    //groupby
      $pdam=$pdam->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')
                  ->groupBy('lokets.byadmin','lokets.tipe',
                      'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                      'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
                      'vw_pdambjm_trans.loket_code','vw_pdambjm_trans.loket_name',
                      'vw_pdambjm_trans.jenis_loket','vw_pdambjm_trans.jenis_transaksi'); 

      $pln=$pln->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')
                  ->groupBy('lokets.byadmin','lokets.tipe',
                      'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                      'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
                      'vw_transaksi_pln.loket_code','vw_transaksi_pln.loket_name',
                      'vw_transaksi_pln.jenis_loket','vw_transaksi_pln.jenis_transaksi');

      $pln_nontaglis=$pln_nontaglis->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')
                  ->groupBy('lokets.byadmin','lokets.tipe',
                      'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                      'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
                      'vw_transaksi_pln_nontaglis.loket_code','vw_transaksi_pln_nontaglis.loket_name',
                      'vw_transaksi_pln_nontaglis.jenis_loket','vw_transaksi_pln_nontaglis.jenis_transaksi');

      $list=$list->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')
                  ->groupBy('lokets.byadmin','lokets.tipe',
                      'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_postpaid',
                      'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
                      'vw_transaksi_pln_prepaid.loket_code','vw_transaksi_pln_prepaid.loket_name',
                      'vw_transaksi_pln_prepaid.jenis_loket','vw_transaksi_pln_prepaid.jenis_transaksi')
              ->union($pdam)
              ->union($pln)
              ->union($pln_nontaglis)
              ->get();  

  $list=$list->toArray();

  function BulanIndo($bulan){
        $BulanIndo = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");            
        $result = $BulanIndo[(int)$bulan-1];;     
        return($result);
      } 
  $bulan=BulanIndo($bulan);
  $tahun=$tahun;
    
  $content = view('laporan.bulananpdf')
          ->with('list',$list)
          ->with('bulan',$bulan)
          ->with('tahun',$tahun)
          ->render();     

  $pdf = new PDF();
  $pdf->loadHTML($content);
  return $pdf->Stream('document.pdf');
}

}
