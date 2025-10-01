<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use DB;
use Excel;
 
use Response; 
use ZanySoft\LaravelPDF\PDF;
use App\Model\transaksi; 
use App\Model\detailTransaksi;
use App\Model\lokets;
use App\Model\status_rekon;

class TransaksiController extends Controller
{
    public function listTrans()
    {
      $list = new \StdClass;
      $lokets= lokets::select('id as id', 'nama as text')->get();
      return view('transaksi.TransaksiPdam',compact('list','lokets'));
    }

    public function ajaxTransaksiPost($tipe,$jenis,$id_loket,$dari,$sampai)
    {
      set_time_limit(0);
      $loket='';
      $array_loket=array();
      $kode_loket='';

      if (session('auth')->level!=2&&session('auth')->level!=6){
        if  ($id_loket!="-"){
          $lokets=lokets::where('id',$id_loket)->first(); 
          $loket=$lokets->loket_code;
        }
      }else {
         $loket =explode(",", session('auth')->id_loket); 
         $lokets=lokets::whereIn('id',$loket)->get()->toArray(); 

         for ($a=0;$a<count($lokets);$a++){
          array_push($array_loket,$lokets[$a]['loket_code']);
         }
      } 

      $list =DB::select('SET @nom=0');

      if (session('auth')->level!=2&&session('auth')->level!=6){
      $list = transaksi::select('vw_rekap_transaksi.*','web_status_rekon.status','web_mntr_shareLoket.pdam as pdam',
                DB::raw(" (@nom := @nom+1) nomor,'' as aksi"))
                ->whereBetween('vw_rekap_transaksi.tanggal', [$dari,$sampai]);
      //jenis All, loket isi, jenis trans ALL          
      if  ($id_loket!="-"&&$jenis=="ALL"){
        $list=$list->where('vw_rekap_transaksi.loket_code',$loket);
        $list=$list->leftJoin('lokets','lokets.loket_code','=','vw_rekap_transaksi.loket_code');
      } 
      //jenis All, loket isi, jenis trans isi          
      else if  ($id_loket!="-"&&$jenis<>"ALL"){
        $list=$list->where('vw_rekap_transaksi.loket_code',$loket)
                   ->where('vw_rekap_transaksi.jenis_transaksi',$jenis);  
        $list=$list->leftJoin('lokets','lokets.loket_code','=','vw_rekap_transaksi.loket_code');           
      }
      //jenis All, loket All, jenis trans isi          
      else if  ($id_loket=="-"&&$tipe=="ALL"&&$jenis<>"ALL"){
        $list=$list->where('vw_rekap_transaksi.jenis_transaksi',$jenis);
        $list=$list->leftJoin('lokets','lokets.loket_code','=','vw_rekap_transaksi.loket_code'); 
      }
      //jenis isi, loket ALL, jenis trans ALL
      else if ($id_loket=="-"&&$tipe<>"ALL"&&$jenis=="ALL"){
        $list=$list->Join('lokets',function($join) use ($tipe)
                  {
                    $join->on('lokets.loket_code','=','vw_rekap_transaksi.loket_code');
                    $join->where('lokets.tipe','=',$tipe);
                  });
      }
      //jenis isi, loket ALL, jenis trans isi
      else if ($id_loket=="-"&&$tipe<>"ALL"&&$jenis<>"ALL"){
        $list=$list->where('vw_rekap_transaksi.jenis_transaksi',$jenis) 
                ->Join('lokets',function($join) use ($tipe)
                  {
                    $join->on('lokets.loket_code','=','vw_rekap_transaksi.loket_code');
                    $join->where('lokets.tipe','=',$tipe);
                  });
      }else{
        $list=$list->leftJoin('lokets','lokets.loket_code','=','vw_rekap_transaksi.loket_code');
      }
      //jenis ALL, loket ALL, jenis trans ALL
      //status
      $list=$list->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets');        
      $list=$list->leftJoin('web_status_rekon',function($join) 
                  {
                    $join->on('web_status_rekon.tanggal','=','vw_rekap_transaksi.tanggal');
                    $join->on('web_status_rekon.jenis_loket','=','vw_rekap_transaksi.jenis_loket');
                  });  
      $list=$list->get();
      }else

      //user
      {
        $list = transaksi::select('vw_rekap_transaksi.*','web_status_rekon.status','web_mntr_shareLoket.pdam',
                DB::raw(" (@nom := @nom+1) nomor,'' as aksi"))
                ->whereBetween('vw_rekap_transaksi.tanggal', [$dari,$sampai]);
      //jenis All, loket isi, jenis trans ALL 
        $list=$list->whereIn('vw_rekap_transaksi.loket_code',$array_loket);
        $list=$list->leftJoin('lokets','lokets.loket_code','=','vw_rekap_transaksi.loket_code'); 
        $list=$list->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets');  
      //status        
        $list=$list->leftJoin('web_status_rekon',function($join) 
                  {
                    $join->on('web_status_rekon.tanggal','=','vw_rekap_transaksi.tanggal');
                    $join->on('web_status_rekon.jenis_loket','=','vw_rekap_transaksi.jenis_loket');
                  });  
        $list=$list->get();
      }
      
      return Response::json(array(
        'status' => 'Success',
        'message' => '-',
        'data' => $list
      ),200);
    }

    public function ajaxDetailTransaksi($loket,$tanggal,$user)
    {
      set_time_limit(0);
      $list =DB::select('SET @nom=0');
      $list = detailTransaksi::select('vw_detail_transaksi.*', DB::raw(" (@nom := @nom+1) nomor"))->
                               where('loket_code',$loket)->
                               where('tanggal',$tanggal)->
                               where('user_',$user)->
                               get();
      return Response::json(array(
        'status' => 'Success',
        'message' => '-',
        'data' => $list
      ),200);
    } 

    public function ajaxDetailTransaksiNew($loket,$tanggal,$user)
    {
      set_time_limit(0);

      $pdam=DB::table('vw_pdambjm_trans')
              ->select('vw_pdambjm_trans.*')
              ->where('loket_code',$loket)
              ->where('tanggal',$tanggal)
              ->where('user_',$user); 

      $pln=DB::table('vw_transaksi_pln')
              ->select('vw_transaksi_pln.*')
              ->where('loket_code',$loket)
              ->where('tanggal',$tanggal)
              ->where('user_',$user);

      $pln_nontaglis=DB::table('vw_transaksi_pln_nontaglis')
              ->select('vw_transaksi_pln_nontaglis.*')
              ->where('loket_code',$loket)
              ->where('tanggal',$tanggal)
              ->where('user_',$user);

      $list=DB::table('vw_transaksi_pln_prepaid')
              ->select('vw_transaksi_pln_prepaid.*')
              ->where('loket_code',$loket)
              ->where('tanggal',$tanggal)
              ->where('user_',$user)
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

    public function cetakDetailPdam($loket,$tanggal,$user){
      set_time_limit(0);
      $list = detailTransaksi::select('idpel', 'nama','periode','tanggal','tagihan','admin','total','user_',
                                      'loket_name','loket_code','jenis_loket','jenis_transaksi')
                                ->where('loket_code',$loket)->
                                 where('tanggal',$tanggal)->
                                 where('user_',$user)->
                                 get();

      $data = json_decode(json_encode($list), True);
          
      Excel::create('Data Detail Transaksi '.$tanggal, function($excel) use($data,$tanggal) {
      //sheet1
      $excel->sheet('Detail', function($sheet)  use($data,$tanggal){
          $sheet->setStyle(array(
              'font' => array('name' =>  'Times New Roman')
          )); 
          $sheet->setAutoSize(true);
          $sheet->mergeCells('A1:L1');
          $sheet->cell('A1', function($cells) use($data,$tanggal){
              $cells->setAlignment('center');
              $cells->setValignment('center');
              $cells->setFontWeight('bold');
              $cells->setValue('Detail Transaksi Tanggal : '.$tanggal);
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

          //footer
          $y++;
          $jumlah_tagihan=array_sum(array_column($data, 'tagihan'));
          $lembar=count($data);

          $sheet->cell('A'.$y, function($cells) {
            $cells->setValue('Jumlah Lembar');
          });
          $sheet->cell('B'.$y, function($cells) use($lembar){
            $cells->setValue($lembar);
          });
          $sheet->cell('E'.$y, function($cells) use ($jumlah_tagihan){
            $cells->setValue($jumlah_tagihan);
          });

          $sheet->setBorder('A3:'.$kolom.''.$y, 'thin');
                  
          });
      })->export('xls');
      return back();
    } 

    public function cetakDetailPdamNew($loket,$tanggal,$user){
      set_time_limit(0);
      $pdam=DB::table('vw_pdambjm_trans_baru')
                ->select('idpel', 'nama','periode','tanggal','tagihan','admin','total','user_',
                          'loket_name','loket_code','jenis_loket','jenis_transaksi','transaction_code')
                ->where('loket_code',$loket)
                ->where('tanggal',$tanggal)
                ->where('user_',$user); 

      $pln=DB::table('vw_transaksi_pln')
              ->select('idpel', 'nama','periode','tanggal','tagihan','admin','total','user_',
                          'loket_name','loket_code','jenis_loket','jenis_transaksi',DB::raw("'' as transaction_code"))
              ->where('loket_code',$loket)
              ->where('tanggal',$tanggal)
              ->where('user_',$user);

      $pln_nontaglis=DB::table('vw_transaksi_pln_nontaglis')
              ->select('idpel', 'nama','periode','tanggal','tagihan','admin','total','user_',
                          'loket_name','loket_code','jenis_loket','jenis_transaksi',DB::raw("'' as transaction_code"))
              ->where('loket_code',$loket)
              ->where('tanggal',$tanggal)
              ->where('user_',$user);

      $list=DB::table('vw_transaksi_pln_prepaid')
              ->select('idpel', 'nama','periode','tanggal','tagihan','admin','total','user_',
                          'loket_name','loket_code','jenis_loket','jenis_transaksi',DB::raw("'' as transaction_code"))
              ->where('loket_code',$loket)
              ->where('tanggal',$tanggal)
              ->where('user_',$user)
              ->union($pdam)
              ->union($pln)
              ->union($pln_nontaglis)
              ->get();                        

      $data = json_decode(json_encode($list), True);
          
      Excel::create('Data Detail Transaksi '.$tanggal, function($excel) use($data,$tanggal) {
      //sheet1
      $excel->sheet('Detail', function($sheet)  use($data,$tanggal){
          $sheet->setStyle(array(
              'font' => array('name' =>  'Times New Roman')
          )); 
          $sheet->setAutoSize(true);
          $sheet->mergeCells('A1:L1');
          $sheet->cell('A1', function($cells) use($data,$tanggal){
              $cells->setAlignment('center');
              $cells->setValignment('center');
              $cells->setFontWeight('bold');
              $cells->setValue('Detail Transaksi Tanggal : '.$tanggal);
          });
          //header tabel
          $sheet->row(3, ['ID Pelanggan','Nama','Bulan Tahun','Tanggal','Tagihan','Admin','Total','User','Loket','Kode Loket','Jenis Loket','Jenis Transaksi','Transaction Code']);

          //isi
          $y=4;   
          for ($x = 0; $x < count($data); $x++){
              $sheet->row($y, $data[$x]);
              $y+=1;
          }        
          //border
          $kolom="A";
          for ($x = 0; $x < 12; $x++){
              $kolom++;
          }

          //footer
          $y++;
          $jumlah_tagihan=array_sum(array_column($data, 'tagihan'));
          $lembar=count($data);

          $sheet->cell('A'.$y, function($cells) {
            $cells->setValue('Jumlah Lembar');
          });
          $sheet->cell('B'.$y, function($cells) use($lembar){
            $cells->setValue($lembar);
          });
          $sheet->cell('E'.$y, function($cells) use ($jumlah_tagihan){
            $cells->setValue($jumlah_tagihan);
          });

          $sheet->setBorder('A3:'.$kolom.''.$y, 'thin');
                  
          });
      })->export('xls');
      return back();
    }

    public function cetakRekapExcellDetail($tipe,$jenis,$id_loket,$dari,$sampai){
      set_time_limit(0);
      $loket='';
      $array_loket=array();

      if  ($id_loket<>"-"){
        $loket =explode(",", $id_loket); 
        $lokets=lokets::whereIn('id',$loket)->get()->toArray(); 

        for ($a=0;$a<count($lokets);$a++){
         array_push($array_loket,$lokets[$a]['loket_code']);
        }
      }
      //tipe  
      $array_tipe=array();
      if ($tipe<>"-"){
         $atipe =explode(",", $tipe); 
         for ($a=0;$a<count($atipe);$a++){
          array_push($array_tipe,$atipe[$a]);
         }
      }
      //jenis
      $array_jenis=array();
      if ($jenis<>"-"){
         $ajenis =explode(",", $jenis); 
         for ($a=0;$a<count($ajenis);$a++){
          array_push($array_jenis,$ajenis[$a]);
         }
      }  

      $tanggal=$dari." sampai ".$sampai;

      $list = detailTransaksi::select('vw_detail_transaksi.idpel', 'vw_detail_transaksi.nama','vw_detail_transaksi.periode',
                                      'vw_detail_transaksi.tanggal','vw_detail_transaksi.tagihan','vw_detail_transaksi.admin',
                                      'vw_detail_transaksi.total','vw_detail_transaksi.user_',
                                      'vw_detail_transaksi.loket_name','vw_detail_transaksi.loket_code',
                                      'vw_detail_transaksi.jenis_loket','vw_detail_transaksi.jenis_transaksi')
                                ->whereBetween('vw_detail_transaksi.tanggal', [$dari,$sampai]);
      if  ($id_loket<>"-"||$tipe=="-"){
        $list=$list->when(count($array_loket)>0, function ($query) use ($array_loket) {
                      return $query->whereIn('vw_detail_transaksi.loket_code',$array_loket);
                  })
                  ->when(count($array_jenis)>0, function ($query) use ($array_jenis) {
                      return $query->whereIn('vw_detail_transaksi.jenis_transaksi',$array_jenis);
                  }) 
                  ->leftJoin('lokets','lokets.loket_code','=','vw_detail_transaksi.loket_code')
                  ->orderBy('vw_detail_transaksi.loket_name','vw_detail_transaksi.tanggal')
                  ->get();         
      }
      //tipe/jenis isi
      else if ($tipe<>"-"){
        
        $list=$list->when(count($array_jenis)>0, function ($query) use ($array_jenis) {
                      return $query->whereIn('vw_detail_transaksi.jenis_transaksi',$array_jenis);
                  }) 
                ->Join('lokets',function($join) use ($array_tipe)
                  {
                    $join->on('lokets.loket_code','=','vw_detail_transaksi.loket_code');
                    $join->whereIn('lokets.tipe',$array_tipe);
                  })
                ->orderBy('vw_detail_transaksi.loket_name','vw_detail_transaksi.tanggal')
                ->get();         
      }                          

      $data = json_decode(json_encode($list), True);
          
      Excel::create('Data Rekap Detail Transaksi '.$tanggal, function($excel) use($data,$tanggal) {
      //sheet1
      $excel->sheet('Detail', function($sheet)  use($data,$tanggal){
          $sheet->setStyle(array(
              'font' => array('name' =>  'Times New Roman')
          )); 
          $sheet->setAutoSize(true);
          $sheet->mergeCells('A1:L1');
          $sheet->cell('A1', function($cells) use($data,$tanggal){
              $cells->setAlignment('center');
              $cells->setValignment('center');
              $cells->setFontWeight('bold');
              $cells->setValue('Detail Transaksi Tanggal : '.$tanggal);
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

    public function cetakRekapExcellDetailNew($tipe,$jenis,$id_loket,$dari,$sampai){
      set_time_limit(0);
      $loket='';
      $array_loket=array();

      if  ($id_loket<>"-"){
        $loket =explode(",", $id_loket); 
        $lokets=lokets::whereIn('id',$loket)->get()->toArray(); 

        for ($a=0;$a<count($lokets);$a++){
         array_push($array_loket,$lokets[$a]['loket_code']);
        }
      }
      //tipe  
      $array_tipe=array();
      if ($tipe<>"-"){
         $atipe =explode(",", $tipe); 
         for ($a=0;$a<count($atipe);$a++){
          array_push($array_tipe,$atipe[$a]);
         }
      }
      //jenis
      $array_jenis=array();
      if ($jenis<>"-"){
         $ajenis =explode(",", $jenis); 
         for ($a=0;$a<count($ajenis);$a++){
          array_push($array_jenis,$ajenis[$a]);
         }
      }  

      $tanggal=$dari." sampai ".$sampai;

      $pdam=DB::table('vw_pdambjm_trans')
                ->select('vw_pdambjm_trans.idpel', 'vw_pdambjm_trans.nama','vw_pdambjm_trans.periode','vw_pdambjm_trans.tanggal','vw_pdambjm_trans.tagihan','vw_pdambjm_trans.admin',
                          'vw_pdambjm_trans.total','user_','vw_pdambjm_trans.loket_name','vw_pdambjm_trans.loket_code','vw_pdambjm_trans.jenis_loket','vw_pdambjm_trans.jenis_transaksi')
                ->whereBetween('vw_pdambjm_trans.tanggal', [$dari,$sampai]); 

      $pln=DB::table('vw_transaksi_pln')
              ->select('vw_transaksi_pln.idpel', 'vw_transaksi_pln.nama','vw_transaksi_pln.periode','vw_transaksi_pln.tanggal','vw_transaksi_pln.tagihan','vw_transaksi_pln.admin',
                          'vw_transaksi_pln.total','vw_transaksi_pln.user_','vw_transaksi_pln.loket_name','vw_transaksi_pln.loket_code','vw_transaksi_pln.jenis_loket','vw_transaksi_pln.jenis_transaksi')
              ->whereBetween('vw_transaksi_pln.tanggal', [$dari,$sampai]);

      $pln_nontaglis=DB::table('vw_transaksi_pln_nontaglis')
              ->select('vw_transaksi_pln_nontaglis.idpel', 'vw_transaksi_pln_nontaglis.nama','vw_transaksi_pln_nontaglis.periode','vw_transaksi_pln_nontaglis.tanggal','vw_transaksi_pln_nontaglis.tagihan','vw_transaksi_pln_nontaglis.admin',
                          'vw_transaksi_pln_nontaglis.total','vw_transaksi_pln_nontaglis.user_','vw_transaksi_pln_nontaglis.loket_name','vw_transaksi_pln_nontaglis.loket_code','vw_transaksi_pln_nontaglis.jenis_loket','vw_transaksi_pln_nontaglis.jenis_transaksi')
              ->whereBetween('vw_transaksi_pln_nontaglis.tanggal', [$dari,$sampai]);

      $list=DB::table('vw_transaksi_pln_prepaid')
              ->select('vw_transaksi_pln_prepaid.idpel', 'vw_transaksi_pln_prepaid.nama','vw_transaksi_pln_prepaid.periode','vw_transaksi_pln_prepaid.tanggal','vw_transaksi_pln_prepaid.tagihan','vw_transaksi_pln_prepaid.admin',
                          'vw_transaksi_pln_prepaid.total','vw_transaksi_pln_prepaid.user_','vw_transaksi_pln_prepaid.loket_name','vw_transaksi_pln_prepaid.loket_code','vw_transaksi_pln_prepaid.jenis_loket','vw_transaksi_pln_prepaid.jenis_transaksi')
              ->whereBetween('vw_transaksi_pln_prepaid.tanggal', [$dari,$sampai]);        


      if  ($id_loket<>"-"||$tipe=="-"){
        $pdam=$pdam->when(count($array_loket)>0, function ($query) use ($array_loket) {
                      return $query->whereIn('vw_pdambjm_trans.loket_code',$array_loket);
                  })
                  ->when(count($array_jenis)>0, function ($query) use ($array_jenis) {
                      return $query->whereIn('jenis_transaksi',$array_jenis);
                  }) 
                  ->leftJoin('lokets','lokets.loket_code','=','vw_pdambjm_trans.loket_code')
                  ->orderBy('vw_pdambjm_trans.loket_name','vw_pdambjm_trans.tanggal');

        $pln=$pln->when(count($array_loket)>0, function ($query) use ($array_loket) {
                      return $query->whereIn('vw_transaksi_pln.loket_code',$array_loket);
                  })
                  ->when(count($array_jenis)>0, function ($query) use ($array_jenis) {
                      return $query->whereIn('jenis_transaksi',$array_jenis);
                  }) 
                  ->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln.loket_code')
                  ->orderBy('vw_transaksi_pln.loket_name','vw_transaksi_pln.tanggal');

        $pln_nontaglis=$pln_nontaglis->when(count($array_loket)>0, function ($query) use ($array_loket) {
                      return $query->whereIn('vw_transaksi_pln_nontaglis.loket_code',$array_loket);
                  })
                  ->when(count($array_jenis)>0, function ($query) use ($array_jenis) {
                      return $query->whereIn('jenis_transaksi',$array_jenis);
                  }) 
                  ->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_nontaglis.loket_code')
                  ->orderBy('vw_transaksi_pln_nontaglis.loket_name','vw_transaksi_pln_nontaglis.tanggal');                    

        $list=$list->when(count($array_loket)>0, function ($query) use ($array_loket) {
                      return $query->whereIn('vw_transaksi_pln_prepaid.loket_code',$array_loket);
                  })
                  ->when(count($array_jenis)>0, function ($query) use ($array_jenis) {
                      return $query->whereIn('jenis_transaksi',$array_jenis);
                  }) 
                  ->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_prepaid.loket_code')
                  ->orderBy('vw_transaksi_pln_prepaid.loket_name','vw_transaksi_pln_prepaid.tanggal')
                  ->union($pdam)
                  ->union($pln)
                  ->union($pln_nontaglis)
                  ->get();         
      }
      //tipe/jenis isi
      else if ($tipe<>"-"){
        $pdam=$pdam->when(count($array_jenis)>0, function ($query) use ($array_jenis) {
                      return $query->whereIn('jenis_transaksi',$array_jenis);
                  }) 
                ->Join('lokets',function($join) use ($array_tipe)
                  {
                    $join->on('lokets.loket_code','=','vw_pdambjm_trans.loket_code');
                    $join->whereIn('lokets.tipe',$array_tipe);
                  })
                ->orderBy('vw_pdambjm_trans.loket_name','vw_pdambjm_trans.tanggal');

        $pln=$pln->when(count($array_jenis)>0, function ($query) use ($array_jenis) {
                      return $query->whereIn('jenis_transaksi',$array_jenis);
                  }) 
                ->Join('lokets',function($join) use ($array_tipe)
                  {
                    $join->on('lokets.loket_code','=','vw_transaksi_pln.loket_code');
                    $join->whereIn('lokets.tipe',$array_tipe);
                  })
                ->orderBy('vw_transaksi_pln.loket_name','vw_transaksi_pln.tanggal');

        $pln_nontaglis=$pln_nontaglis->when(count($array_jenis)>0, function ($query) use ($array_jenis) {
                      return $query->whereIn('jenis_transaksi',$array_jenis);
                  }) 
                ->Join('lokets',function($join) use ($array_tipe)
                  {
                    $join->on('lokets.loket_code','=','vw_transaksi_pln_nontaglis.loket_code');
                    $join->whereIn('lokets.tipe',$array_tipe);
                  })
                ->orderBy('vw_transaksi_pln_nontaglis.loket_name','vw_transaksi_pln_nontaglis.tanggal');                
        
        $list=$list->when(count($array_jenis)>0, function ($query) use ($array_jenis) {
                      return $query->whereIn('jenis_transaksi',$array_jenis);
                  }) 
                ->Join('lokets',function($join) use ($array_tipe)
                  {
                    $join->on('lokets.loket_code','=','vw_transaksi_pln_prepaid.loket_code');
                    $join->whereIn('lokets.tipe',$array_tipe);
                  })
                ->orderBy('vw_transaksi_pln_prepaid.loket_name','vw_transaksi_pln_prepaid.tanggal')
                ->union($pdam)
                ->union($pln)
                ->union($pln_nontaglis)
                ->get();         
      }                          

      $data = json_decode(json_encode($list), True);
          
      Excel::create('Data Rekap Detail Transaksi '.$tanggal, function($excel) use($data,$tanggal) {
      //sheet1
      $excel->sheet('Detail', function($sheet)  use($data,$tanggal){
          $sheet->setStyle(array(
              'font' => array('name' =>  'Times New Roman')
          )); 
          $sheet->setAutoSize(true);
          $sheet->mergeCells('A1:L1');
          $sheet->cell('A1', function($cells) use($data,$tanggal){
              $cells->setAlignment('center');
              $cells->setValignment('center');
              $cells->setFontWeight('bold');
              $cells->setValue('Detail Transaksi Tanggal : '.$tanggal);
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

    public function cetakRekapExcell($tipe,$jenis,$id_loket,$dari,$sampai)
    {
      set_time_limit(0);
      $loket='';
      $array_loket=array();

      if  ($id_loket<>"-"){
        $loket =explode(",", $id_loket); 
        $lokets=lokets::whereIn('id',$loket)->get()->toArray(); 

        for ($a=0;$a<count($lokets);$a++){
         array_push($array_loket,$lokets[$a]['loket_code']);
        }
      }
        //tipe  
        $array_tipe=array();
        if ($tipe<>"-"){
           $atipe =explode(",", $tipe); 
           for ($a=0;$a<count($atipe);$a++){
            array_push($array_tipe,$atipe[$a]);
           }
        }
        //jenis
        $array_jenis=array();
        if ($jenis<>"-"){
           $ajenis =explode(",", $jenis); 
           for ($a=0;$a<count($ajenis);$a++){
            array_push($array_jenis,$ajenis[$a]);
           }
        }  

      $pdam=DB::table('vw_pdambjm_trans')
                ->select('vw_pdambjm_trans.tanggal AS tanggal','vw_pdambjm_trans.user_ AS user_',
                  'vw_pdambjm_trans.loket_code AS loket_code','vw_pdambjm_trans.loket_name AS loket_name',
                  'vw_pdambjm_trans.jenis_loket AS jenis_loket','vw_pdambjm_trans.jenis_transaksi AS jenis_transaksi',
                DB::raw("sum(vw_pdambjm_trans.tagihan) AS tagihan,sum(vw_pdambjm_trans.admin) AS admin,
                      sum(vw_pdambjm_trans.total) AS total,count(0) AS jumlah"))
                ->whereBetween('vw_pdambjm_trans.tanggal', [$dari,$sampai]); 

      $pln=DB::table('vw_transaksi_pln')
              ->select('vw_transaksi_pln.tanggal AS tanggal','vw_transaksi_pln.user_ AS user_',
                'vw_transaksi_pln.loket_code AS loket_code','vw_transaksi_pln.loket_name AS loket_name',
                'vw_transaksi_pln.jenis_loket AS jenis_loket','vw_transaksi_pln.jenis_transaksi AS jenis_transaksi',
              DB::raw("sum(vw_transaksi_pln.tagihan) AS tagihan,sum(vw_transaksi_pln.admin) AS admin,
                    sum(vw_transaksi_pln.total) AS total,count(0) AS jumlah"))
              ->whereBetween('vw_transaksi_pln.tanggal', [$dari,$sampai]);

      $pln_nontaglis=DB::table('vw_transaksi_pln_nontaglis')
              ->select('vw_transaksi_pln_nontaglis.tanggal AS tanggal','vw_transaksi_pln_nontaglis.user_ AS user_',
                'vw_transaksi_pln_nontaglis.loket_code AS loket_code','vw_transaksi_pln_nontaglis.loket_name AS loket_name',
                'vw_transaksi_pln_nontaglis.jenis_loket AS jenis_loket','vw_transaksi_pln_nontaglis.jenis_transaksi AS jenis_transaksi',
              DB::raw("sum(vw_transaksi_pln_nontaglis.tagihan) AS tagihan,sum(vw_transaksi_pln_nontaglis.admin) AS admin,
                    sum(vw_transaksi_pln_nontaglis.total) AS total,count(0) AS jumlah"))
              ->whereBetween('vw_transaksi_pln_nontaglis.tanggal', [$dari,$sampai]);

      $list=DB::table('vw_transaksi_pln_prepaid')
              ->select('vw_transaksi_pln_prepaid.tanggal AS tanggal','vw_transaksi_pln_prepaid.user_ AS user_',
                'vw_transaksi_pln_prepaid.loket_code AS loket_code','vw_transaksi_pln_prepaid.loket_name AS loket_name',
                'vw_transaksi_pln_prepaid.jenis_loket AS jenis_loket','vw_transaksi_pln_prepaid.jenis_transaksi AS jenis_transaksi',
              DB::raw("sum(vw_transaksi_pln_prepaid.tagihan) AS tagihan,sum(vw_transaksi_pln_prepaid.admin) AS admin,
                    sum(vw_transaksi_pln_prepaid.total) AS total,count(0) AS jumlah"))
              ->whereBetween('vw_transaksi_pln_prepaid.tanggal', [$dari,$sampai]); 
      
      //loket isi or semua          
      if  ($id_loket<>"-"||$tipe=="-"){

        $pdam=$pdam->when(count($array_loket)>0, function ($query) use ($array_loket) {
                      return $query->whereIn('vw_pdambjm_trans.loket_code',$array_loket);
                  })
                  ->when(count($array_jenis)>0, function ($query) use ($array_jenis) {
                      return $query->whereIn('vw_pdambjm_trans.jenis_transaksi',$array_jenis);
                  }) 
                  ->leftJoin('lokets','lokets.loket_code','=','vw_pdambjm_trans.loket_code')
                  ->groupBy('vw_pdambjm_trans.tanggal','vw_pdambjm_trans.user_',
                        'vw_pdambjm_trans.loket_code','vw_pdambjm_trans.loket_name',
                        'vw_pdambjm_trans.jenis_loket','vw_pdambjm_trans.jenis_transaksi');

        $pln=$pln->when(count($array_loket)>0, function ($query) use ($array_loket) {
                      return $query->whereIn('vw_transaksi_pln.loket_code',$array_loket);
                  })
                  ->when(count($array_jenis)>0, function ($query) use ($array_jenis) {
                      return $query->whereIn('vw_transaksi_pln.jenis_transaksi',$array_jenis);
                  }) 
                  ->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln.loket_code')
                  ->groupBy('vw_transaksi_pln.tanggal','vw_transaksi_pln.user_',
                        'vw_transaksi_pln.loket_code','vw_transaksi_pln.loket_name',
                        'vw_transaksi_pln.jenis_loket','vw_transaksi_pln.jenis_transaksi');

        $pln_nontaglis=$pln_nontaglis->when(count($array_loket)>0, function ($query) use ($array_loket) {
                      return $query->whereIn('vw_transaksi_pln_nontaglis.loket_code',$array_loket);
                  })
                  ->when(count($array_jenis)>0, function ($query) use ($array_jenis) {
                      return $query->whereIn('vw_transaksi_pln_nontaglis.jenis_transaksi',$array_jenis);
                  }) 
                  ->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_nontaglis.loket_code')
                  ->groupBy('vw_transaksi_pln_nontaglis.tanggal','vw_transaksi_pln_nontaglis.user_',
                        'vw_transaksi_pln_nontaglis.loket_code','vw_transaksi_pln_nontaglis.loket_name',
                        'vw_transaksi_pln_nontaglis.jenis_loket','vw_transaksi_pln_nontaglis.jenis_transaksi');

        $list=$list->when(count($array_loket)>0, function ($query) use ($array_loket) {
                      return $query->whereIn('vw_transaksi_pln_prepaid.loket_code',$array_loket);
                  })
                  ->when(count($array_jenis)>0, function ($query) use ($array_jenis) {
                      return $query->whereIn('vw_transaksi_pln_prepaid.jenis_transaksi',$array_jenis);
                  }) 
                  ->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_prepaid.loket_code')
                  ->groupBy('vw_transaksi_pln_prepaid.tanggal','vw_transaksi_pln_prepaid.user_',
                        'vw_transaksi_pln_prepaid.loket_code','vw_transaksi_pln_prepaid.loket_name',
                        'vw_transaksi_pln_prepaid.jenis_loket','vw_transaksi_pln_prepaid.jenis_transaksi')
                  ->union($pdam)
                  ->union($pln)
                  ->union($pln_nontaglis)
                  ->get();         
      }
      //tipe/jenis isi
      else if ($tipe<>"-"){
        $pdam=$pdam->when(count($array_jenis)>0, function ($query) use ($array_jenis) {
                      return $query->whereIn('vw_pdambjm_trans.jenis_transaksi',$array_jenis);
                  }) 
                ->Join('lokets',function($join) use ($array_tipe)
                  {
                    $join->on('lokets.loket_code','=','vw_pdambjm_trans.loket_code');
                    $join->whereIn('lokets.tipe',$array_tipe);
                  })
                ->groupBy('vw_pdambjm_trans.tanggal','vw_pdambjm_trans.user_',
                        'vw_pdambjm_trans.loket_code','vw_pdambjm_trans.loket_name',
                        'vw_pdambjm_trans.jenis_loket','vw_pdambjm_trans.jenis_transaksi');
        $pln=$pln->when(count($array_jenis)>0, function ($query) use ($array_jenis) {
                      return $query->whereIn('vw_transaksi_pln.jenis_transaksi',$array_jenis);
                  })
                ->Join('lokets',function($join) use ($array_tipe)
                  {
                    $join->on('lokets.loket_code','=','vw_transaksi_pln.loket_code');
                    $join->whereIn('lokets.tipe',$array_tipe);
                  })
                ->groupBy('vw_transaksi_pln.tanggal','vw_transaksi_pln.user_',
                        'vw_transaksi_pln.loket_code','vw_transaksi_pln.loket_name',
                        'vw_transaksi_pln.jenis_loket','vw_transaksi_pln.jenis_transaksi');
        $pln_nontaglis=$pln_nontaglis->when(count($array_jenis)>0, function ($query) use ($array_jenis) {
                      return $query->whereIn('vw_transaksi_pln_nontaglis.jenis_transaksi',$array_jenis);
                  }) 
                ->Join('lokets',function($join) use ($array_tipe)
                  {
                    $join->on('lokets.loket_code','=','vw_transaksi_pln_nontaglis.loket_code');
                    $join->whereIn('lokets.tipe',$array_tipe);
                  })
                ->groupBy('vw_transaksi_pln_nontaglis.tanggal','vw_transaksi_pln_nontaglis.user_',
                        'vw_transaksi_pln_nontaglis.loket_code','vw_transaksi_pln_nontaglis.loket_name',
                        'vw_transaksi_pln_nontaglis.jenis_loket','vw_transaksi_pln_nontaglis.jenis_transaksi');
        $list=$list->when(count($array_jenis)>0, function ($query) use ($array_jenis) {
                      return $query->whereIn('vw_transaksi_pln_prepaid.jenis_transaksi',$array_jenis);
                  }) 
                ->Join('lokets',function($join) use ($array_tipe)
                  {
                    $join->on('lokets.loket_code','=','vw_transaksi_pln_prepaid.loket_code');
                    $join->whereIn('lokets.tipe',$array_tipe);
                  })
                ->groupBy('vw_transaksi_pln_prepaid.tanggal','vw_transaksi_pln_prepaid.user_',
                        'vw_transaksi_pln_prepaid.loket_code','vw_transaksi_pln_prepaid.loket_name',
                        'vw_transaksi_pln_prepaid.jenis_loket','vw_transaksi_pln_prepaid.jenis_transaksi')
                ->union($pdam)
                ->union($pln)
                ->union($pln_nontaglis)
                ->get();         
      }
        
        // $list=$list->toArray();                       

      $data = json_decode(json_encode($list), True);

      $tanggal=$dari." sampai ".$sampai;
          
      Excel::create('Data Rekap Transaksi '.$tanggal, function($excel) use($data,$tanggal) {
      //sheet1
      $excel->sheet('Rekap', function($sheet)  use($data,$tanggal){
          $sheet->setStyle(array(
              'font' => array('name' =>  'Times New Roman')
          )); 
          $sheet->setAutoSize(true);
          $sheet->mergeCells('A1:L1');
          $sheet->cell('A1', function($cells) use($data,$tanggal){
              $cells->setAlignment('center');
              $cells->setValignment('center');
              $cells->setFontWeight('bold');
              $cells->setValue('Rekap Transaksi Tanggal : '.$tanggal);
          });
          //header tabel
          $sheet->row(3, ['Tanggal','User','Kode Loket','Nama Loket','Jenis Loket','Jenis Transaksi','Tagihan','Admin','Total','Jumlah']);

          //isi
          $y=4;   
          for ($x = 0; $x < count($data); $x++){
              $sheet->row($y, $data[$x]);
              $y+=1;
          }      

          $sheet->getStyle('G4:J'.$y)->getNumberFormat()->setFormatCode('#,##0');   
          //border
          $kolom="A";
          for ($x = 0; $x < 9; $x++){
              $kolom++;
          }
          $sheet->setBorder('A3:'.$kolom.''.$y, 'thin');
                  
          });
      })->export('xls');
      return back();
    }

    public function Rekonsiliasi()
    {
      return view('rekonsiliasi.rekonsiliasi');
    }

    public function RekonsiliasiPost($tanggal)
    {
      set_time_limit(0);
      $nonadmin = transaksi::select('vw_rekap_transaksi.tanggal as tanggal', 'vw_rekap_transaksi.jenis_loket as jenis_loket',
                              DB::raw('SUM(vw_rekap_transaksi.tagihan) as rupiah,SUM(vw_rekap_transaksi.jumlah) as jumlah'),
                              DB::raw("'' as aksi"),
                              'web_status_rekon.status as status')
                              ->leftJoin('web_status_rekon',function($join)
                                {
                                  $join->on('web_status_rekon.tanggal', '=', 'vw_rekap_transaksi.tanggal')
                                        ->on('web_status_rekon.jenis_loket', '=', 'vw_rekap_transaksi.jenis_loket');
                                })
                              ->where('vw_rekap_transaksi.tanggal',$tanggal)
                              ->where('vw_rekap_transaksi.jenis_loket','NON_ADMIN')
                              ->where('vw_rekap_transaksi.jenis_transaksi','PDAM_BANDARMASIH')
                              ->groupBy('tanggal','jenis_loket','status');
                       
      $list = transaksi::select('vw_rekap_transaksi.tanggal as tanggal', 'vw_rekap_transaksi.jenis_loket as jenis_loket',
                              DB::raw('SUM(vw_rekap_transaksi.tagihan) as rupiah,SUM(vw_rekap_transaksi.jumlah) as jumlah'),
                              DB::raw("'' as aksi"),
                              'web_status_rekon.status as status')
                              ->leftJoin('web_status_rekon',function($join)
                                {
                                  $join->on('web_status_rekon.tanggal', '=', 'vw_rekap_transaksi.tanggal')
                                        ->on('web_status_rekon.jenis_loket', '=', 'vw_rekap_transaksi.jenis_loket');
                                })
                              ->where('vw_rekap_transaksi.tanggal',$tanggal)
                              ->where('vw_rekap_transaksi.jenis_loket','<>','NON_ADMIN')
                              ->where('vw_rekap_transaksi.jenis_transaksi','PDAM_BANDARMASIH')
                              ->groupBy('tanggal','jenis_loket','status')
                              ->union($nonadmin)
                              ->get(); 


      return Response::json(array(
        'status' => 'Success',
        'message' => '-',
        'data' => $list
      ),200);
    }

    public function RekonsiliasiPostNew($tanggal)
    {
      set_time_limit(0);
      $nonadmin = DB::table('vw_pdambjm_trans')
                ->select('vw_pdambjm_trans.tanggal as tanggal', 'vw_pdambjm_trans.jenis_loket as jenis_loket',
                              DB::raw('SUM(vw_pdambjm_trans.tagihan) as rupiah,count(0) AS jumlah'),
                              DB::raw("'' as aksi"),
                              'web_status_rekon.status as status')
                              ->leftJoin('web_status_rekon',function($join)
                                {
                                  $join->on('web_status_rekon.tanggal', '=', 'vw_pdambjm_trans.tanggal')
                                        ->on('web_status_rekon.jenis_loket', '=', 'vw_pdambjm_trans.jenis_loket');
                                })
                              ->where('vw_pdambjm_trans.tanggal',$tanggal)
                              ->where('vw_pdambjm_trans.jenis_loket','NON_ADMIN')
                              ->groupBy('tanggal','jenis_loket','status');
                       
      $list = DB::table('vw_pdambjm_trans')
                ->select('vw_pdambjm_trans.tanggal as tanggal', 'vw_pdambjm_trans.jenis_loket as jenis_loket',
                              DB::raw('SUM(vw_pdambjm_trans.tagihan) as rupiah,count(0) AS jumlah'),
                              DB::raw("'' as aksi"),
                              'web_status_rekon.status as status')
                              ->leftJoin('web_status_rekon',function($join)
                                {
                                  $join->on('web_status_rekon.tanggal', '=', 'vw_pdambjm_trans.tanggal')
                                        ->on('web_status_rekon.jenis_loket', '=', 'vw_pdambjm_trans.jenis_loket');
                                })
                              ->where('vw_pdambjm_trans.tanggal',$tanggal)
                              ->where('vw_pdambjm_trans.jenis_loket','<>','NON_ADMIN')
                              ->groupBy('tanggal','jenis_loket','status')
                              ->union($nonadmin)
                              ->get(); 


      return Response::json(array(
        'status' => 'Success',
        'message' => '-',
        'data' => $list
      ),200);
    }

    public function detailRekon($jenis_loket,$tanggal)
    {
      set_time_limit(0);
      //$list = detailTransaksi::where('tanggal',$tanggal);
      //if ($jenis_loket=='ADMIN'){
       // $list = $list->where('jenis_loket','<>','NON_ADMIN');
      //}
      //else {
      //  $list = $list->where('jenis_loket','=','NON_ADMIN');
      //}
      //$list=$list->where('jenis_transaksi','PDAM_BANDARMASIH');
      //$list = $list->get();

      $list = detailTransaksi::where('tanggal',$tanggal)
                            ->where('jenis_loket',$jenis_loket)
                             ->where('jenis_transaksi','PDAM_BANDARMASIH')
                            ->get();

      $numRekap = $list->count();
        if($numRekap > 0){
            return Response::json(array(
                'status' => 'Success',
                'message' => '-',
                'data' => $list->toArray(),
            ),200);

        }else{
            return Response::json(array(
                'status' => 'Error',
                'message' => 'Tidak ada transaksi di tanggal : '.$tanggal,
                'data' => ''
            ),200);
        }                        

    } 

    public function cetakDetail($tanggal,$jenis_loket){
      set_time_limit(0);
      $list = detailTransaksi::select('idpel', 'nama','periode','tanggal','tagihan','admin','total','user_',
                              'loket_name','loket_code','jenis_loket','jenis_transaksi')
                              ->where('tanggal',$tanggal)
                              ->where('jenis_loket',$jenis_loket)
                               ->where('jenis_transaksi','PDAM_BANDARMASIH')
                              ->get();

      $data = json_decode(json_encode($list), True);
          
      Excel::create('Data Detail Transaksi '.$jenis_loket.' '.$tanggal, function($excel) use($data,$jenis_loket,$tanggal) {
      //sheet1
      $excel->sheet('Detail', function($sheet)  use($data,$jenis_loket,$tanggal){
          $sheet->setStyle(array(
              'font' => array('name' =>  'Times New Roman')
          )); 
          $sheet->setAutoSize(true);
          $sheet->mergeCells('A1:L1');
          $sheet->cell('A1', function($cells) use($data,$jenis_loket,$tanggal){
              $cells->setAlignment('center');
              $cells->setValignment('center');
              $cells->setFontWeight('bold');
              $cells->setValue('DATA Transaksi '.$jenis_loket.' Tanggal : '.$tanggal);
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

    public static function ajaxSimpanStatus(Request $req)
    {   
       set_time_limit(0); 
       $result = DB::select('select * from web_status_rekon 
        WHERE tanggal = ? AND jenis_loket = ?', [$req->tanggal, $req->jenis]);

        if (count($result) == 1) {
          status_rekon::where('tanggal', $req->tanggal)
                  ->where('jenis_loket', $req->jenis)
                  ->update(["status" => $req->status]);  
        }                      
        else{
          status_rekon::insert([
                  "tanggal" => $req->tanggal,
                  "jenis_loket" => $req->jenis,
                  "status" => $req->status
                  ]);     
        }  
    }


    public function cetakPdf($tipe,$jenis,$loket,$dari,$sampai) {
        set_time_limit(0);
        $loket2='';
        $kode_loket='';

        if ($loket<>"-"){
          $lokets=lokets::where('id',$loket)->first(); 
          $loket2=$lokets->loket_code;
          $kode_loket=$loket;
        }
       

        $list = transaksi::select('vw_rekap_transaksi.*')
                  ->whereBetween('vw_rekap_transaksi.tanggal', [$dari,$sampai]);
        //jenis All, loket isi, jenis trans ALL          
        if  ($loket<>"-"&&$jenis=="ALL"){
          $list=$list->where('vw_rekap_transaksi.loket_code',$loket2);
          $list=$list->leftJoin('lokets','lokets.loket_code','=','vw_rekap_transaksi.loket_code');
        } 
        //jenis All, loket isi, jenis trans isi          
        else if  ($loket<>"-"&&$jenis<>"ALL"){
          $list=$list->where('vw_rekap_transaksi.loket_code',$loket2)
                     ->where('vw_rekap_transaksi.jenis_transaksi',$jenis);  
          $list=$list->leftJoin('lokets','lokets.loket_code','=','vw_rekap_transaksi.loket_code');           
        }
        //jenis All, loket All, jenis trans isi          
        else if  ($loket=="-"&&$tipe=="ALL"&&$jenis<>"ALL"){
          $list=$list->where('vw_rekap_transaksi.jenis_transaksi',$jenis);
          $list=$list->leftJoin('lokets','lokets.loket_code','=','vw_rekap_transaksi.loket_code'); 
        }
        //jenis isi, loket ALL, jenis trans ALL
        else if ($loket=="-"&&$tipe<>"ALL"&&$jenis=="ALL"){
          $list=$list->Join('lokets',function($join) use ($tipe)
                    {
                      $join->on('lokets.loket_code','=','vw_rekap_transaksi.loket_code');
                      $join->where('lokets.tipe','=',$tipe);
                    });
        }
        //jenis isi, loket ALL, jenis trans isi
        else if ($loket=="-"&&$tipe<>"ALL"&&$jenis<>"ALL"){
          $list=$list->where('vw_rekap_transaksi.jenis_transaksi',$jenis) 
                  ->Join('lokets',function($join) use ($tipe)
                    {
                      $join->on('lokets.loket_code','=','vw_rekap_transaksi.loket_code');
                      $join->where('lokets.tipe','=',$tipe);
                    });
        }else{
          $list=$list->leftJoin('lokets','lokets.loket_code','=','vw_rekap_transaksi.loket_code');
        }
        //jenis ALL, loket ALL, jenis trans ALL
        $list=$list->get();
        
        $list=$list->toArray();

        function BulanIndo($bulan){
              $BulanIndo = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");            
              $result = $BulanIndo[(int)$bulan-1];;     
              return($result);
            } 
        //$bulan=BulanIndo($bulan);
        $dari1=explode('-', $dari);  
        $sampai1=explode('-', $sampai);  

        $tanggal=$dari1[2]." ".BulanIndo($dari1[1])." ".$dari1[0].
                  " s/d ".$sampai1[2]." ".BulanIndo($sampai1[1])." ".$sampai1[0];
          
        $content = view('transaksi.transaksipdf')
                ->with('list',$list)
                ->with('tanggal',$tanggal)
                ->render();     

        $pdf = new PDF();
        $pdf->loadHTML($content);
        return $pdf->Stream('document.pdf');
    }

    public function cetakPdfNew($tipe,$jenis,$id_loket,$dari,$sampai) 
    {
      set_time_limit(0);
      $loket='';
      $array_loket=array();

      if  ($id_loket<>"-"){
        $loket =explode(",", $id_loket); 
        $lokets=lokets::whereIn('id',$loket)->get()->toArray(); 

        for ($a=0;$a<count($lokets);$a++){
         array_push($array_loket,$lokets[$a]['loket_code']);
        }
      }
        //tipe  
        $array_tipe=array();
        if ($tipe<>"-"){
           $atipe =explode(",", $tipe); 
           for ($a=0;$a<count($atipe);$a++){
            array_push($array_tipe,$atipe[$a]);
           }
        }
        //jenis
        $array_jenis=array();
        if ($jenis<>"-"){
           $ajenis =explode(",", $jenis); 
           for ($a=0;$a<count($ajenis);$a++){
            array_push($array_jenis,$ajenis[$a]);
           }
        }  

      $pdam=DB::table('vw_pdambjm_trans')
                ->select('vw_pdambjm_trans.tanggal AS tanggal','vw_pdambjm_trans.user_ AS user_',
                  'vw_pdambjm_trans.loket_code AS loket_code','vw_pdambjm_trans.loket_name AS loket_name',
                  'vw_pdambjm_trans.jenis_loket AS jenis_loket','vw_pdambjm_trans.jenis_transaksi AS jenis_transaksi',
                DB::raw("sum(vw_pdambjm_trans.tagihan) AS tagihan,sum(vw_pdambjm_trans.admin) AS admin,
                      sum(vw_pdambjm_trans.total) AS total,count(0) AS jumlah"))
                ->whereBetween('vw_pdambjm_trans.tanggal', [$dari,$sampai]); 

      $pln=DB::table('vw_transaksi_pln')
              ->select('vw_transaksi_pln.tanggal AS tanggal','vw_transaksi_pln.user_ AS user_',
                'vw_transaksi_pln.loket_code AS loket_code','vw_transaksi_pln.loket_name AS loket_name',
                'vw_transaksi_pln.jenis_loket AS jenis_loket','vw_transaksi_pln.jenis_transaksi AS jenis_transaksi',
              DB::raw("sum(vw_transaksi_pln.tagihan) AS tagihan,sum(vw_transaksi_pln.admin) AS admin,
                    sum(vw_transaksi_pln.total) AS total,count(0) AS jumlah"))
              ->whereBetween('vw_transaksi_pln.tanggal', [$dari,$sampai]);

      $pln_nontaglis=DB::table('vw_transaksi_pln_nontaglis')
              ->select('vw_transaksi_pln_nontaglis.tanggal AS tanggal','vw_transaksi_pln_nontaglis.user_ AS user_',
                'vw_transaksi_pln_nontaglis.loket_code AS loket_code','vw_transaksi_pln_nontaglis.loket_name AS loket_name',
                'vw_transaksi_pln_nontaglis.jenis_loket AS jenis_loket','vw_transaksi_pln_nontaglis.jenis_transaksi AS jenis_transaksi',
              DB::raw("sum(vw_transaksi_pln_nontaglis.tagihan) AS tagihan,sum(vw_transaksi_pln_nontaglis.admin) AS admin,
                    sum(vw_transaksi_pln_nontaglis.total) AS total,count(0) AS jumlah"))
              ->whereBetween('vw_transaksi_pln_nontaglis.tanggal', [$dari,$sampai]);

      $list=DB::table('vw_transaksi_pln_prepaid')
              ->select('vw_transaksi_pln_prepaid.tanggal AS tanggal','vw_transaksi_pln_prepaid.user_ AS user_',
                'vw_transaksi_pln_prepaid.loket_code AS loket_code','vw_transaksi_pln_prepaid.loket_name AS loket_name',
                'vw_transaksi_pln_prepaid.jenis_loket AS jenis_loket','vw_transaksi_pln_prepaid.jenis_transaksi AS jenis_transaksi',
              DB::raw("sum(vw_transaksi_pln_prepaid.tagihan) AS tagihan,sum(vw_transaksi_pln_prepaid.admin) AS admin,
                    sum(vw_transaksi_pln_prepaid.total) AS total,count(0) AS jumlah"))
              ->whereBetween('vw_transaksi_pln_prepaid.tanggal', [$dari,$sampai]); 
      
      //loket isi or semua          
      if  ($id_loket<>"-"||$tipe=="-"){

        $pdam=$pdam->when(count($array_loket)>0, function ($query) use ($array_loket) {
                      return $query->whereIn('vw_pdambjm_trans.loket_code',$array_loket);
                  })
                  ->when(count($array_jenis)>0, function ($query) use ($array_jenis) {
                      return $query->whereIn('vw_pdambjm_trans.jenis_transaksi',$array_jenis);
                  }) 
                  ->leftJoin('lokets','lokets.loket_code','=','vw_pdambjm_trans.loket_code')
                  ->groupBy('vw_pdambjm_trans.tanggal','vw_pdambjm_trans.user_',
                        'vw_pdambjm_trans.loket_code','vw_pdambjm_trans.loket_name',
                        'vw_pdambjm_trans.jenis_loket','vw_pdambjm_trans.jenis_transaksi');

        $pln=$pln->when(count($array_loket)>0, function ($query) use ($array_loket) {
                      return $query->whereIn('vw_transaksi_pln.loket_code',$array_loket);
                  })
                  ->when(count($array_jenis)>0, function ($query) use ($array_jenis) {
                      return $query->whereIn('vw_transaksi_pln.jenis_transaksi',$array_jenis);
                  }) 
                  ->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln.loket_code')
                  ->groupBy('vw_transaksi_pln.tanggal','vw_transaksi_pln.user_',
                        'vw_transaksi_pln.loket_code','vw_transaksi_pln.loket_name',
                        'vw_transaksi_pln.jenis_loket','vw_transaksi_pln.jenis_transaksi');

        $pln_nontaglis=$pln_nontaglis->when(count($array_loket)>0, function ($query) use ($array_loket) {
                      return $query->whereIn('vw_transaksi_pln_nontaglis.loket_code',$array_loket);
                  })
                  ->when(count($array_jenis)>0, function ($query) use ($array_jenis) {
                      return $query->whereIn('vw_transaksi_pln_nontaglis.jenis_transaksi',$array_jenis);
                  }) 
                  ->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_nontaglis.loket_code')
                  ->groupBy('vw_transaksi_pln_nontaglis.tanggal','vw_transaksi_pln_nontaglis.user_',
                        'vw_transaksi_pln_nontaglis.loket_code','vw_transaksi_pln_nontaglis.loket_name',
                        'vw_transaksi_pln_nontaglis.jenis_loket','vw_transaksi_pln_nontaglis.jenis_transaksi');

        $list=$list->when(count($array_loket)>0, function ($query) use ($array_loket) {
                      return $query->whereIn('vw_transaksi_pln_prepaid.loket_code',$array_loket);
                  })
                  ->when(count($array_jenis)>0, function ($query) use ($array_jenis) {
                      return $query->whereIn('vw_transaksi_pln_prepaid.jenis_transaksi',$array_jenis);
                  }) 
                  ->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_prepaid.loket_code')
                  ->groupBy('vw_transaksi_pln_prepaid.tanggal','vw_transaksi_pln_prepaid.user_',
                        'vw_transaksi_pln_prepaid.loket_code','vw_transaksi_pln_prepaid.loket_name',
                        'vw_transaksi_pln_prepaid.jenis_loket','vw_transaksi_pln_prepaid.jenis_transaksi')
                  ->union($pdam)
                  ->union($pln)
                  ->union($pln_nontaglis)
                  ->get();         
      }
      //tipe/jenis isi
      else if ($tipe<>"-"){
        $pdam=$pdam->when(count($array_jenis)>0, function ($query) use ($array_jenis) {
                      return $query->whereIn('vw_pdambjm_trans.jenis_transaksi',$array_jenis);
                  }) 
                ->Join('lokets',function($join) use ($array_tipe)
                  {
                    $join->on('lokets.loket_code','=','vw_pdambjm_trans.loket_code');
                    $join->whereIn('lokets.tipe',$array_tipe);
                  })
                ->groupBy('vw_pdambjm_trans.tanggal','vw_pdambjm_trans.user_',
                        'vw_pdambjm_trans.loket_code','vw_pdambjm_trans.loket_name',
                        'vw_pdambjm_trans.jenis_loket','vw_pdambjm_trans.jenis_transaksi');
        $pln=$pln->when(count($array_jenis)>0, function ($query) use ($array_jenis) {
                      return $query->whereIn('vw_transaksi_pln.jenis_transaksi',$array_jenis);
                  })
                ->Join('lokets',function($join) use ($array_tipe)
                  {
                    $join->on('lokets.loket_code','=','vw_transaksi_pln.loket_code');
                    $join->whereIn('lokets.tipe',$array_tipe);
                  })
                ->groupBy('vw_transaksi_pln.tanggal','vw_transaksi_pln.user_',
                        'vw_transaksi_pln.loket_code','vw_transaksi_pln.loket_name',
                        'vw_transaksi_pln.jenis_loket','vw_transaksi_pln.jenis_transaksi');
        $pln_nontaglis=$pln_nontaglis->when(count($array_jenis)>0, function ($query) use ($array_jenis) {
                      return $query->whereIn('vw_transaksi_pln_nontaglis.jenis_transaksi',$array_jenis);
                  }) 
                ->Join('lokets',function($join) use ($array_tipe)
                  {
                    $join->on('lokets.loket_code','=','vw_transaksi_pln_nontaglis.loket_code');
                    $join->whereIn('lokets.tipe',$array_tipe);
                  })
                ->groupBy('vw_transaksi_pln_nontaglis.tanggal','vw_transaksi_pln_nontaglis.user_',
                        'vw_transaksi_pln_nontaglis.loket_code','vw_transaksi_pln_nontaglis.loket_name',
                        'vw_transaksi_pln_nontaglis.jenis_loket','vw_transaksi_pln_nontaglis.jenis_transaksi');
        $list=$list->when(count($array_jenis)>0, function ($query) use ($array_jenis) {
                      return $query->whereIn('vw_transaksi_pln_prepaid.jenis_transaksi',$array_jenis);
                  }) 
                ->Join('lokets',function($join) use ($array_tipe)
                  {
                    $join->on('lokets.loket_code','=','vw_transaksi_pln_prepaid.loket_code');
                    $join->whereIn('lokets.tipe',$array_tipe);
                  })
                ->groupBy('vw_transaksi_pln_prepaid.tanggal','vw_transaksi_pln_prepaid.user_',
                        'vw_transaksi_pln_prepaid.loket_code','vw_transaksi_pln_prepaid.loket_name',
                        'vw_transaksi_pln_prepaid.jenis_loket','vw_transaksi_pln_prepaid.jenis_transaksi')
                ->union($pdam)
                ->union($pln)
                ->union($pln_nontaglis)
                ->get();         
      }
        
        $list=$list->toArray();

        function BulanIndo($bulan){
              $BulanIndo = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");            
              $result = $BulanIndo[(int)$bulan-1];;     
              return($result);
            } 
        //$bulan=BulanIndo($bulan);
        $dari1=explode('-', $dari);  
        $sampai1=explode('-', $sampai);  

        $tanggal=$dari1[2]." ".BulanIndo($dari1[1])." ".$dari1[0].
                  " s/d ".$sampai1[2]." ".BulanIndo($sampai1[1])." ".$sampai1[0];
          
        $content = view('transaksi.transaksipdf')
                ->with('list',$list)
                ->with('tanggal',$tanggal)
                ->render();     

        $pdf = new PDF();
        $pdf->loadHTML($content);
        return $pdf->Stream('document.pdf');
    }

    //H+1
    public function transaksiH1()
    {
      return view('transaksi.TransaksiH1');
    } 
    //H+1
    public function ajaxTransaksiH1($tipe)
    {
      set_time_limit(0);
      $tanggal = date('Y-m-d', strtotime('+8 hours'));
      //$tanggal='2017-05-26';

      $list = lokets::select(DB::raw('sum(vw_rekap_transaksi.total) AS total,"" as aksi'),
                'topups.topup_money','lokets.nama as loket_name','lokets.loket_code','lokets.pulsa',
                'web_mntr_shareLoket.limit')
                ->where('lokets.tipe',$tipe)
                ->leftJoin('vw_rekap_transaksi',function($join) use ($tanggal)
                  {
                    $join->on('lokets.loket_code','=','vw_rekap_transaksi.loket_code');
                    $join->whereDate('vw_rekap_transaksi.tanggal',$tanggal);
                  })
                ->leftJoin(DB::raw('(SELECT loket_id,topup_date,sum(topup_money) AS topup_money FROM topups GROUP BY loket_id,topup_date) topups'), function($join) use($tanggal)
                  {
                    $join->on('lokets.id','=','topups.loket_id');
                    $join->whereDate('topups.topup_date',$tanggal);
                  })
                ->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets') 
                ->groupBy('lokets.nama','lokets.loket_code','topups.topup_money',
                           'lokets.pulsa','web_mntr_shareLoket.limit')
                ->get();

      return Response::json(array(
          'status' => 'Success',
          'message' => '-',
          'data' => $list
        ),200);
    }

    public function ajaxTransaksiH1new($tipe)
    {
      set_time_limit(0);
      $tanggal = date('Y-m-d', strtotime('+8 hours'));
      //$tanggal='2017-05-26';

      $list = lokets::select(DB::raw('COALESCE(sum(vw_pdambjm_trans.total),0)
                                      +COALESCE(sum(vw_transaksi_pln.total),0)
                                      +COALESCE(sum(vw_transaksi_pln_nontaglis.total),0)
                                      +COALESCE(sum(vw_transaksi_pln_prepaid.total),0) AS total,
                "" as aksi'),
                'topups.topup_money','lokets.nama as loket_name','lokets.loket_code','lokets.pulsa',
                'web_mntr_shareLoket.limit')
                ->where('lokets.tipe',$tipe)
                ->leftJoin('vw_pdambjm_trans',function($join) use ($tanggal)
                  {
                    $join->on('lokets.loket_code','=','vw_pdambjm_trans.loket_code');
                    $join->whereDate('vw_pdambjm_trans.tanggal',$tanggal);
                  })
                ->leftJoin('vw_transaksi_pln',function($join) use ($tanggal)
                  {
                    $join->on('lokets.loket_code','=','vw_transaksi_pln.loket_code');
                    $join->whereDate('vw_transaksi_pln.tanggal',$tanggal);
                  })
                ->leftJoin('vw_transaksi_pln_nontaglis',function($join) use ($tanggal)
                  {
                    $join->on('lokets.loket_code','=','vw_transaksi_pln_nontaglis.loket_code');
                    $join->whereDate('vw_transaksi_pln_nontaglis.tanggal',$tanggal);
                  })
                ->leftJoin('vw_transaksi_pln_prepaid',function($join) use ($tanggal)
                  {
                    $join->on('lokets.loket_code','=','vw_transaksi_pln_prepaid.loket_code');
                    $join->whereDate('vw_transaksi_pln_prepaid.tanggal',$tanggal);
                  })

                ->leftJoin(DB::raw('(SELECT loket_id,topup_date,sum(topup_money) AS topup_money FROM topups GROUP BY loket_id,topup_date) topups'), function($join) use($tanggal)
                  {
                    $join->on('lokets.id','=','topups.loket_id');
                    $join->whereDate('topups.topup_date',$tanggal);
                  })
                ->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets') 
                ->groupBy('lokets.nama','lokets.loket_code','topups.topup_money',
                           'lokets.pulsa','web_mntr_shareLoket.limit')
                ->get();
        
      return Response::json(array(
        'status' => 'Success',
        'message' => '-',
        'data' => $list
      ),200);
    }

    public function ajaxTransaksiNew($tipe,$jenis,$id_loket,$dari,$sampai)
    {
      set_time_limit(0);
      $loket='';
      $array_loket=array();
      $kode_loket='';

      if (session('auth')->level!=2&&session('auth')->level!=6){
        if  ($id_loket!="-"){
          $lokets=lokets::where('id',$id_loket)->first(); 
          $loket=$lokets->loket_code;
        }
      }else {
         $loket =explode(",", session('auth')->id_loket); 
         $lokets=lokets::whereIn('id',$loket)->get()->toArray(); 

         for ($a=0;$a<count($lokets);$a++){
          array_push($array_loket,$lokets[$a]['loket_code']);
         }
      } 
      $list =DB::select('SET @nom=0');

      if (session('auth')->level!=2&&session('auth')->level!=6){

      $pdam=DB::table('vw_pdambjm_trans')
                ->select('vw_pdambjm_trans.tanggal AS tanggal','vw_pdambjm_trans.user_ AS user_',
                  'vw_pdambjm_trans.loket_code AS loket_code','vw_pdambjm_trans.loket_name AS loket_name',
                  'vw_pdambjm_trans.jenis_loket AS jenis_loket','vw_pdambjm_trans.jenis_transaksi AS jenis_transaksi',
                  'web_status_rekon.status','web_mntr_shareLoket.pdam',
                DB::raw("sum(vw_pdambjm_trans.tagihan) AS tagihan,sum(vw_pdambjm_trans.admin) AS admin,
                      sum(vw_pdambjm_trans.total) AS total,count(0) AS jumlah, '' as nomor,'' as aksi"))
                ->whereBetween('vw_pdambjm_trans.tanggal', [$dari,$sampai]); 

      $pln=DB::table('vw_transaksi_pln')
              ->select('vw_transaksi_pln.tanggal AS tanggal','vw_transaksi_pln.user_ AS user_',
                'vw_transaksi_pln.loket_code AS loket_code','vw_transaksi_pln.loket_name AS loket_name',
                'vw_transaksi_pln.jenis_loket AS jenis_loket','vw_transaksi_pln.jenis_transaksi AS jenis_transaksi',
                'web_status_rekon.status','web_mntr_shareLoket.pdam',
              DB::raw("sum(vw_transaksi_pln.tagihan) AS tagihan,sum(vw_transaksi_pln.admin) AS admin,
                    sum(vw_transaksi_pln.total) AS total,count(0) AS jumlah, '' as nomor,'' as aksi"))
              ->whereBetween('vw_transaksi_pln.tanggal', [$dari,$sampai]);

      $pln_nontaglis=DB::table('vw_transaksi_pln_nontaglis')
              ->select('vw_transaksi_pln_nontaglis.tanggal AS tanggal','vw_transaksi_pln_nontaglis.user_ AS user_',
                'vw_transaksi_pln_nontaglis.loket_code AS loket_code','vw_transaksi_pln_nontaglis.loket_name AS loket_name',
                'vw_transaksi_pln_nontaglis.jenis_loket AS jenis_loket','vw_transaksi_pln_nontaglis.jenis_transaksi AS jenis_transaksi',
                'web_status_rekon.status','web_mntr_shareLoket.pdam',
              DB::raw("sum(vw_transaksi_pln_nontaglis.tagihan) AS tagihan,sum(vw_transaksi_pln_nontaglis.admin) AS admin,
                    sum(vw_transaksi_pln_nontaglis.total) AS total,count(0) AS jumlah, '' as nomor,'' as aksi"))
              ->whereBetween('vw_transaksi_pln_nontaglis.tanggal', [$dari,$sampai]);

      $list=DB::table('vw_transaksi_pln_prepaid')
              ->select('vw_transaksi_pln_prepaid.tanggal AS tanggal','vw_transaksi_pln_prepaid.user_ AS user_',
                'vw_transaksi_pln_prepaid.loket_code AS loket_code','vw_transaksi_pln_prepaid.loket_name AS loket_name',
                'vw_transaksi_pln_prepaid.jenis_loket AS jenis_loket','vw_transaksi_pln_prepaid.jenis_transaksi AS jenis_transaksi',
                'web_status_rekon.status','web_mntr_shareLoket.pdam',
              DB::raw("sum(vw_transaksi_pln_prepaid.tagihan) AS tagihan,sum(vw_transaksi_pln_prepaid.admin) AS admin,
                    sum(vw_transaksi_pln_prepaid.total) AS total,count(0) AS jumlah, '' as nomor,'' as aksi"))
              ->whereBetween('vw_transaksi_pln_prepaid.tanggal', [$dari,$sampai]);
      //jenis All, loket isi, jenis trans ALL          
      if  ($id_loket!="-"&&$jenis=="ALL"){
        $pdam=$pdam->where('vw_pdambjm_trans.loket_code',$loket);
        $pdam=$pdam->leftJoin('lokets','lokets.loket_code','=','vw_pdambjm_trans.loket_code');

        $pln=$pln->where('vw_transaksi_pln.loket_code',$loket);
        $pln=$pln->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln.loket_code');

        $pln_nontaglis=$pln_nontaglis->where('vw_transaksi_pln_nontaglis.loket_code',$loket);
        $pln_nontaglis=$pln_nontaglis->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_nontaglis.loket_code');

        $list=$list->where('vw_transaksi_pln_prepaid.loket_code',$loket);
        $list=$list->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_prepaid.loket_code');
      } 
      //jenis All, loket isi, jenis trans isi          
      else if  ($id_loket!="-"&&$jenis<>"ALL"){
        $pdam=$pdam->where('vw_pdambjm_trans.loket_code',$loket)
                   ->where('vw_pdambjm_trans.jenis_transaksi',$jenis); 
        $pdam=$pdam->leftJoin('lokets','lokets.loket_code','=','vw_pdambjm_trans.loket_code');

        $pln=$pln->where('vw_transaksi_pln.loket_code',$loket)
                   ->where('vw_transaksi_pln.jenis_transaksi',$jenis); 
        $pln=$pln->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln.loket_code');

        $pln_nontaglis=$pln_nontaglis->where('vw_transaksi_pln_nontaglis.loket_code',$loket)
                   ->where('vw_transaksi_pln_nontaglis.jenis_transaksi',$jenis); 
        $pln_nontaglis=$pln_nontaglis->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_nontaglis.loket_code');

        $list=$list->where('vw_transaksi_pln_prepaid.loket_code',$loket)
                   ->where('vw_transaksi_pln_prepaid.jenis_transaksi',$jenis); 
        $list=$list->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_prepaid.loket_code');           
      }
      //jenis All, loket All, jenis trans isi          
      else if  ($id_loket=="-"&&$tipe=="ALL"&&$jenis<>"ALL"){
        $pdam=$pdam->where('vw_pdambjm_trans.jenis_transaksi',$jenis); 
        $pdam=$pdam->leftJoin('lokets','lokets.loket_code','=','vw_pdambjm_trans.loket_code');

        $pln=$pln->where('vw_transaksi_pln.jenis_transaksi',$jenis); 
        $pln=$pln->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln.loket_code');

        $pln_nontaglis=$pln_nontaglis->where('vw_transaksi_pln_nontaglis.jenis_transaksi',$jenis); 
        $pln_nontaglis=$pln_nontaglis->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_nontaglis.loket_code');

        $list=$list->where('vw_transaksi_pln_prepaid.jenis_transaksi',$jenis); 
        $list=$list->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_prepaid.loket_code');
      }
      //jenis isi, loket ALL, jenis trans ALL
      else if ($id_loket=="-"&&$tipe<>"ALL"&&$jenis=="ALL"){
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
      else if ($id_loket=="-"&&$tipe<>"ALL"&&$jenis<>"ALL"){
        $pdam=$pdam->where('vw_pdambjm_trans.jenis_transaksi',$jenis) 
                ->Join('lokets',function($join) use ($tipe)
                  {
                    $join->on('lokets.loket_code','=','vw_pdambjm_trans.loket_code');
                    $join->where('lokets.tipe','=',$tipe);
                  });
        $pln=$pln->where('vw_transaksi_pln.jenis_transaksi',$jenis) 
                ->Join('lokets',function($join) use ($tipe)
                  {
                    $join->on('lokets.loket_code','=','vw_transaksi_pln.loket_code');
                    $join->where('lokets.tipe','=',$tipe);
                  });
        $pln_nontaglis=$pln_nontaglis->where('vw_transaksi_pln_nontaglis.jenis_transaksi',$jenis) 
                ->Join('lokets',function($join) use ($tipe)
                  {
                    $join->on('lokets.loket_code','=','vw_transaksi_pln_nontaglis.loket_code');
                    $join->where('lokets.tipe','=',$tipe);
                  });
        $list=$list->where('vw_transaksi_pln_prepaid.jenis_transaksi',$jenis) 
                ->Join('lokets',function($join) use ($tipe)
                  {
                    $join->on('lokets.loket_code','=','vw_transaksi_pln_prepaid.loket_code');
                    $join->where('lokets.tipe','=',$tipe);
                  });        
      }else{
        $pdam=$pdam->leftJoin('lokets','lokets.loket_code','=','vw_pdambjm_trans.loket_code');
        $pln=$pln->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln.loket_code');
        $pln_nontaglis=$pln_nontaglis->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_nontaglis.loket_code');
        $list=$list->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_prepaid.loket_code');
      }
      //jenis ALL, loket ALL, jenis trans ALL
      //status
      $pdam=$pdam->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')       
                ->leftJoin('web_status_rekon',function($join) 
                      {
                        $join->on('web_status_rekon.tanggal','=','vw_pdambjm_trans.tanggal');
                        $join->on('web_status_rekon.jenis_loket','=','vw_pdambjm_trans.jenis_loket');
                      })
                ->groupBy('vw_pdambjm_trans.tanggal','vw_pdambjm_trans.user_',
                        'vw_pdambjm_trans.loket_code','vw_pdambjm_trans.loket_name',
                        'vw_pdambjm_trans.jenis_loket','vw_pdambjm_trans.jenis_transaksi',
                        'web_status_rekon.status','web_mntr_shareLoket.pdam'); 

        $pln=$pln->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')       
                ->leftJoin('web_status_rekon',function($join) 
                      {
                        $join->on('web_status_rekon.tanggal','=','vw_transaksi_pln.tanggal');
                        $join->on('web_status_rekon.jenis_loket','=','vw_transaksi_pln.jenis_loket');
                      })
                ->groupBy('vw_transaksi_pln.tanggal','vw_transaksi_pln.user_',
                        'vw_transaksi_pln.loket_code','vw_transaksi_pln.loket_name',
                        'vw_transaksi_pln.jenis_loket','vw_transaksi_pln.jenis_transaksi',
                        'web_status_rekon.status','web_mntr_shareLoket.pdam');

        $pln_nontaglis=$pln_nontaglis->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')       
                ->leftJoin('web_status_rekon',function($join) 
                      {
                        $join->on('web_status_rekon.tanggal','=','vw_transaksi_pln_nontaglis.tanggal');
                        $join->on('web_status_rekon.jenis_loket','=','vw_transaksi_pln_nontaglis.jenis_loket');
                      })
                ->groupBy('vw_transaksi_pln_nontaglis.tanggal','vw_transaksi_pln_nontaglis.user_',
                        'vw_transaksi_pln_nontaglis.loket_code','vw_transaksi_pln_nontaglis.loket_name',
                        'vw_transaksi_pln_nontaglis.jenis_loket','vw_transaksi_pln_nontaglis.jenis_transaksi',
                        'web_status_rekon.status','web_mntr_shareLoket.pdam');

        $list=$list->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')       
                ->leftJoin('web_status_rekon',function($join) 
                      {
                        $join->on('web_status_rekon.tanggal','=','vw_transaksi_pln_prepaid.tanggal');
                        $join->on('web_status_rekon.jenis_loket','=','vw_transaksi_pln_prepaid.jenis_loket');
                      })
                ->groupBy('vw_transaksi_pln_prepaid.tanggal','vw_transaksi_pln_prepaid.user_',
                        'vw_transaksi_pln_prepaid.loket_code','vw_transaksi_pln_prepaid.loket_name',
                        'vw_transaksi_pln_prepaid.jenis_loket','vw_transaksi_pln_prepaid.jenis_transaksi',
                        'web_status_rekon.status','web_mntr_shareLoket.pdam')
                ->union($pdam)
                ->union($pln)
                ->union($pln_nontaglis)
                ->get();
      }else

      //USER
      {
        $pdam=DB::table('vw_pdambjm_trans')
                ->select('vw_pdambjm_trans.tanggal AS tanggal','vw_pdambjm_trans.user_ AS user_',
                  'vw_pdambjm_trans.loket_code AS loket_code','vw_pdambjm_trans.loket_name AS loket_name',
                  'vw_pdambjm_trans.jenis_loket AS jenis_loket','vw_pdambjm_trans.jenis_transaksi AS jenis_transaksi',
                  'web_status_rekon.status','web_mntr_shareLoket.pdam',
                DB::raw("sum(vw_pdambjm_trans.tagihan) AS tagihan,sum(vw_pdambjm_trans.admin) AS admin,
                      sum(vw_pdambjm_trans.total) AS total,count(0) AS jumlah, '' as nomor,'' as aksi"))
                ->whereBetween('vw_pdambjm_trans.tanggal', [$dari,$sampai])
                ->whereIn('vw_pdambjm_trans.loket_code',$array_loket)
                ->leftJoin('lokets','lokets.loket_code','=','vw_pdambjm_trans.loket_code')
                ->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')       
                ->leftJoin('web_status_rekon',function($join) 
                      {
                        $join->on('web_status_rekon.tanggal','=','vw_pdambjm_trans.tanggal');
                        $join->on('web_status_rekon.jenis_loket','=','vw_pdambjm_trans.jenis_loket');
                      })
                ->groupBy('vw_pdambjm_trans.tanggal','vw_pdambjm_trans.user_',
                        'vw_pdambjm_trans.loket_code','vw_pdambjm_trans.loket_name',
                        'vw_pdambjm_trans.jenis_loket','vw_pdambjm_trans.jenis_transaksi',
                        'web_status_rekon.status','web_mntr_shareLoket.pdam'); 

        $pln=DB::table('vw_transaksi_pln')
                ->select('vw_transaksi_pln.tanggal AS tanggal','vw_transaksi_pln.user_ AS user_',
                  'vw_transaksi_pln.loket_code AS loket_code','vw_transaksi_pln.loket_name AS loket_name',
                  'vw_transaksi_pln.jenis_loket AS jenis_loket','vw_transaksi_pln.jenis_transaksi AS jenis_transaksi',
                  'web_status_rekon.status','web_mntr_shareLoket.pdam',
                DB::raw("sum(vw_transaksi_pln.tagihan) AS tagihan,sum(vw_transaksi_pln.admin) AS admin,
                      sum(vw_transaksi_pln.total) AS total,count(0) AS jumlah, '' as nomor,'' as aksi"))
                ->whereBetween('vw_transaksi_pln.tanggal', [$dari,$sampai])
                ->whereIn('vw_transaksi_pln.loket_code',$array_loket)
                ->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln.loket_code')
                ->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')       
                ->leftJoin('web_status_rekon',function($join) 
                      {
                        $join->on('web_status_rekon.tanggal','=','vw_transaksi_pln.tanggal');
                        $join->on('web_status_rekon.jenis_loket','=','vw_transaksi_pln.jenis_loket');
                      })
                ->groupBy('vw_transaksi_pln.tanggal','vw_transaksi_pln.user_',
                        'vw_transaksi_pln.loket_code','vw_transaksi_pln.loket_name',
                        'vw_transaksi_pln.jenis_loket','vw_transaksi_pln.jenis_transaksi',
                        'web_status_rekon.status','web_mntr_shareLoket.pdam');

        $pln_nontaglis=DB::table('vw_transaksi_pln_nontaglis')
                ->select('vw_transaksi_pln_nontaglis.tanggal AS tanggal','vw_transaksi_pln_nontaglis.user_ AS user_',
                  'vw_transaksi_pln_nontaglis.loket_code AS loket_code','vw_transaksi_pln_nontaglis.loket_name AS loket_name',
                  'vw_transaksi_pln_nontaglis.jenis_loket AS jenis_loket','vw_transaksi_pln_nontaglis.jenis_transaksi AS jenis_transaksi',
                  'web_status_rekon.status','web_mntr_shareLoket.pdam',
                DB::raw("sum(vw_transaksi_pln_nontaglis.tagihan) AS tagihan,sum(vw_transaksi_pln_nontaglis.admin) AS admin,
                      sum(vw_transaksi_pln_nontaglis.total) AS total,count(0) AS jumlah, '' as nomor,'' as aksi"))
                ->whereBetween('vw_transaksi_pln_nontaglis.tanggal', [$dari,$sampai])
                ->whereIn('vw_transaksi_pln_nontaglis.loket_code',$array_loket)
                ->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_nontaglis.loket_code')
                ->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')       
                ->leftJoin('web_status_rekon',function($join) 
                      {
                        $join->on('web_status_rekon.tanggal','=','vw_transaksi_pln_nontaglis.tanggal');
                        $join->on('web_status_rekon.jenis_loket','=','vw_transaksi_pln_nontaglis.jenis_loket');
                      })
                ->groupBy('vw_transaksi_pln_nontaglis.tanggal','vw_transaksi_pln_nontaglis.user_',
                        'vw_transaksi_pln_nontaglis.loket_code','vw_transaksi_pln_nontaglis.loket_name',
                        'vw_transaksi_pln_nontaglis.jenis_loket','vw_transaksi_pln_nontaglis.jenis_transaksi',
                        'web_status_rekon.status','web_mntr_shareLoket.pdam');

        $list=DB::table('vw_transaksi_pln_prepaid')
                ->select('vw_transaksi_pln_prepaid.tanggal AS tanggal','vw_transaksi_pln_prepaid.user_ AS user_',
                  'vw_transaksi_pln_prepaid.loket_code AS loket_code','vw_transaksi_pln_prepaid.loket_name AS loket_name',
                  'vw_transaksi_pln_prepaid.jenis_loket AS jenis_loket','vw_transaksi_pln_prepaid.jenis_transaksi AS jenis_transaksi',
                  'web_status_rekon.status','web_mntr_shareLoket.pdam',
                DB::raw("sum(vw_transaksi_pln_prepaid.tagihan) AS tagihan,sum(vw_transaksi_pln_prepaid.admin) AS admin,
                      sum(vw_transaksi_pln_prepaid.total) AS total,count(0) AS jumlah, '' as nomor,'' as aksi"))
                ->whereBetween('vw_transaksi_pln_prepaid.tanggal', [$dari,$sampai])
                ->whereIn('vw_transaksi_pln_prepaid.loket_code',$array_loket)
                ->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_prepaid.loket_code')
                ->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')       
                ->leftJoin('web_status_rekon',function($join) 
                      {
                        $join->on('web_status_rekon.tanggal','=','vw_transaksi_pln_prepaid.tanggal');
                        $join->on('web_status_rekon.jenis_loket','=','vw_transaksi_pln_prepaid.jenis_loket');
                      })
                ->groupBy('vw_transaksi_pln_prepaid.tanggal','vw_transaksi_pln_prepaid.user_',
                        'vw_transaksi_pln_prepaid.loket_code','vw_transaksi_pln_prepaid.loket_name',
                        'vw_transaksi_pln_prepaid.jenis_loket','vw_transaksi_pln_prepaid.jenis_transaksi',
                        'web_status_rekon.status','web_mntr_shareLoket.pdam')
                ->union($pdam)
                ->union($pln)
                ->union($pln_nontaglis)
                ->get();                                                      

      }      
      return Response::json(array(
        'status' => 'Success',
        'message' => '-',
        'data' => $list
      ),200);
    }

    public function ajaxTransaksiNewMultiple($tipe,$jenis,$id_loket,$dari,$sampai)
    {  
      set_time_limit(0);
      $loket='';
      $array_loket=array();

      if (session('auth')->level!=2&&session('auth')->level!=6){
        if  ($id_loket<>"-"){
          $loket =explode(",", $id_loket); 
          $lokets=lokets::whereIn('id',$loket)->get()->toArray(); 

          for ($a=0;$a<count($lokets);$a++){
           array_push($array_loket,$lokets[$a]['loket_code']);
          }
        }
      }else {
         $loket =explode(",", session('auth')->id_loket); 
         $lokets=lokets::whereIn('id',$loket)->get()->toArray(); 

         for ($a=0;$a<count($lokets);$a++){
          array_push($array_loket,$lokets[$a]['loket_code']);
         }
      } 

      if (session('auth')->level!=2&&session('auth')->level!=6){
        //tipe  
        $array_tipe=array();
        if ($tipe<>"-"){
           $atipe =explode(",", $tipe); 
           for ($a=0;$a<count($atipe);$a++){
            array_push($array_tipe,$atipe[$a]);
           }
        }
        //jenis
        $array_jenis=array();
        if ($jenis<>"-"){
           $ajenis =explode(",", $jenis); 
           for ($a=0;$a<count($ajenis);$a++){
            array_push($array_jenis,$ajenis[$a]);
           }
        }  

      $pdam=DB::table('vw_pdambjm_trans')
                ->select('vw_pdambjm_trans.tanggal AS tanggal','vw_pdambjm_trans.user_ AS user_',
                  'vw_pdambjm_trans.loket_code AS loket_code','vw_pdambjm_trans.loket_name AS loket_name',
                  'vw_pdambjm_trans.jenis_loket AS jenis_loket','vw_pdambjm_trans.jenis_transaksi AS jenis_transaksi',
                  'web_status_rekon.status','web_mntr_shareLoket.pdam',
                DB::raw("sum(vw_pdambjm_trans.tagihan) AS tagihan,sum(vw_pdambjm_trans.admin) AS admin,
                      sum(vw_pdambjm_trans.total) AS total,count(0) AS jumlah, '' as nomor,'' as aksi"))
                ->whereBetween('vw_pdambjm_trans.tanggal', [$dari,$sampai]); 

      $pln=DB::table('vw_transaksi_pln')
              ->select('vw_transaksi_pln.tanggal AS tanggal','vw_transaksi_pln.user_ AS user_',
                'vw_transaksi_pln.loket_code AS loket_code','vw_transaksi_pln.loket_name AS loket_name',
                'vw_transaksi_pln.jenis_loket AS jenis_loket','vw_transaksi_pln.jenis_transaksi AS jenis_transaksi',
                'web_status_rekon.status','web_mntr_shareLoket.pdam',
              DB::raw("sum(vw_transaksi_pln.tagihan) AS tagihan,sum(vw_transaksi_pln.admin) AS admin,
                    sum(vw_transaksi_pln.total) AS total,count(0) AS jumlah, '' as nomor,'' as aksi"))
              ->whereBetween('vw_transaksi_pln.tanggal', [$dari,$sampai]);

      $pln_nontaglis=DB::table('vw_transaksi_pln_nontaglis')
              ->select('vw_transaksi_pln_nontaglis.tanggal AS tanggal','vw_transaksi_pln_nontaglis.user_ AS user_',
                'vw_transaksi_pln_nontaglis.loket_code AS loket_code','vw_transaksi_pln_nontaglis.loket_name AS loket_name',
                'vw_transaksi_pln_nontaglis.jenis_loket AS jenis_loket','vw_transaksi_pln_nontaglis.jenis_transaksi AS jenis_transaksi',
                'web_status_rekon.status','web_mntr_shareLoket.pdam',
              DB::raw("sum(vw_transaksi_pln_nontaglis.tagihan) AS tagihan,sum(vw_transaksi_pln_nontaglis.admin) AS admin,
                    sum(vw_transaksi_pln_nontaglis.total) AS total,count(0) AS jumlah, '' as nomor,'' as aksi"))
              ->whereBetween('vw_transaksi_pln_nontaglis.tanggal', [$dari,$sampai]);

      $list=DB::table('vw_transaksi_pln_prepaid')
              ->select('vw_transaksi_pln_prepaid.tanggal AS tanggal','vw_transaksi_pln_prepaid.user_ AS user_',
                'vw_transaksi_pln_prepaid.loket_code AS loket_code','vw_transaksi_pln_prepaid.loket_name AS loket_name',
                'vw_transaksi_pln_prepaid.jenis_loket AS jenis_loket','vw_transaksi_pln_prepaid.jenis_transaksi AS jenis_transaksi',
                'web_status_rekon.status','web_mntr_shareLoket.pdam',
              DB::raw("sum(vw_transaksi_pln_prepaid.tagihan) AS tagihan,sum(vw_transaksi_pln_prepaid.admin) AS admin,
                    sum(vw_transaksi_pln_prepaid.total) AS total,count(0) AS jumlah, '' as nomor,'' as aksi"))
              ->whereBetween('vw_transaksi_pln_prepaid.tanggal', [$dari,$sampai]); 
      
      //loket isi or semua          
      if  ($id_loket<>"-"||$tipe=="-"){

        $pdam=$pdam->when(count($array_loket)>0, function ($query) use ($array_loket) {
                      return $query->whereIn('vw_pdambjm_trans.loket_code',$array_loket);
                  })
                  ->when(count($array_jenis)>0, function ($query) use ($array_jenis) {
                      return $query->whereIn('vw_pdambjm_trans.jenis_transaksi',$array_jenis);
                  }) 
                  ->leftJoin('lokets','lokets.loket_code','=','vw_pdambjm_trans.loket_code');

        $pln=$pln->when(count($array_loket)>0, function ($query) use ($array_loket) {
                      return $query->whereIn('vw_transaksi_pln.loket_code',$array_loket);
                  })
                  ->when(count($array_jenis)>0, function ($query) use ($array_jenis) {
                      return $query->whereIn('vw_transaksi_pln.jenis_transaksi',$array_jenis);
                  }) 
                  ->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln.loket_code');

        $pln_nontaglis=$pln_nontaglis->when(count($array_loket)>0, function ($query) use ($array_loket) {
                      return $query->whereIn('vw_transaksi_pln_nontaglis.loket_code',$array_loket);
                  })
                  ->when(count($array_jenis)>0, function ($query) use ($array_jenis) {
                      return $query->whereIn('vw_transaksi_pln_nontaglis.jenis_transaksi',$array_jenis);
                  }) 
                  ->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_nontaglis.loket_code');

        $list=$list->when(count($array_loket)>0, function ($query) use ($array_loket) {
                      return $query->whereIn('vw_transaksi_pln_prepaid.loket_code',$array_loket);
                  })
                  ->when(count($array_jenis)>0, function ($query) use ($array_jenis) {
                      return $query->whereIn('vw_transaksi_pln_prepaid.jenis_transaksi',$array_jenis);
                  }) 
                  ->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_prepaid.loket_code');           
      }
      //tipe/jenis isi
      else if ($tipe<>"-"){
        $pdam=$pdam->when(count($array_jenis)>0, function ($query) use ($array_jenis) {
                      return $query->whereIn('vw_pdambjm_trans.jenis_transaksi',$array_jenis);
                  }) 
                ->Join('lokets',function($join) use ($array_tipe)
                  {
                    $join->on('lokets.loket_code','=','vw_pdambjm_trans.loket_code');
                    $join->whereIn('lokets.tipe',$array_tipe);
                  });
        $pln=$pln->when(count($array_jenis)>0, function ($query) use ($array_jenis) {
                      return $query->whereIn('vw_transaksi_pln.jenis_transaksi',$array_jenis);
                  })
                ->Join('lokets',function($join) use ($array_tipe)
                  {
                    $join->on('lokets.loket_code','=','vw_transaksi_pln.loket_code');
                    $join->whereIn('lokets.tipe',$array_tipe);
                  });
        $pln_nontaglis=$pln_nontaglis->when(count($array_jenis)>0, function ($query) use ($array_jenis) {
                      return $query->whereIn('vw_transaksi_pln_nontaglis.jenis_transaksi',$array_jenis);
                  }) 
                ->Join('lokets',function($join) use ($array_tipe)
                  {
                    $join->on('lokets.loket_code','=','vw_transaksi_pln_nontaglis.loket_code');
                    $join->whereIn('lokets.tipe',$array_tipe);
                  });
        $list=$list->when(count($array_jenis)>0, function ($query) use ($array_jenis) {
                      return $query->whereIn('vw_transaksi_pln_prepaid.jenis_transaksi',$array_jenis);
                  }) 
                ->Join('lokets',function($join) use ($array_tipe)
                  {
                    $join->on('lokets.loket_code','=','vw_transaksi_pln_prepaid.loket_code');
                    $join->whereIn('lokets.tipe',$array_tipe);
                  });        
      }
      //status
      $pdam=$pdam->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')       
                ->leftJoin('web_status_rekon',function($join) 
                      {
                        $join->on('web_status_rekon.tanggal','=','vw_pdambjm_trans.tanggal');
                        $join->on('web_status_rekon.jenis_loket','=','vw_pdambjm_trans.jenis_loket');
                      })
                ->groupBy('vw_pdambjm_trans.tanggal','vw_pdambjm_trans.user_',
                        'vw_pdambjm_trans.loket_code','vw_pdambjm_trans.loket_name',
                        'vw_pdambjm_trans.jenis_loket','vw_pdambjm_trans.jenis_transaksi',
                        'web_status_rekon.status','web_mntr_shareLoket.pdam'); 

        $pln=$pln->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')       
                ->leftJoin('web_status_rekon',function($join) 
                      {
                        $join->on('web_status_rekon.tanggal','=','vw_transaksi_pln.tanggal');
                        $join->on('web_status_rekon.jenis_loket','=','vw_transaksi_pln.jenis_loket');
                      })
                ->groupBy('vw_transaksi_pln.tanggal','vw_transaksi_pln.user_',
                        'vw_transaksi_pln.loket_code','vw_transaksi_pln.loket_name',
                        'vw_transaksi_pln.jenis_loket','vw_transaksi_pln.jenis_transaksi',
                        'web_status_rekon.status','web_mntr_shareLoket.pdam');

        $pln_nontaglis=$pln_nontaglis->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')       
                ->leftJoin('web_status_rekon',function($join) 
                      {
                        $join->on('web_status_rekon.tanggal','=','vw_transaksi_pln_nontaglis.tanggal');
                        $join->on('web_status_rekon.jenis_loket','=','vw_transaksi_pln_nontaglis.jenis_loket');
                      })
                ->groupBy('vw_transaksi_pln_nontaglis.tanggal','vw_transaksi_pln_nontaglis.user_',
                        'vw_transaksi_pln_nontaglis.loket_code','vw_transaksi_pln_nontaglis.loket_name',
                        'vw_transaksi_pln_nontaglis.jenis_loket','vw_transaksi_pln_nontaglis.jenis_transaksi',
                        'web_status_rekon.status','web_mntr_shareLoket.pdam');

        $list=$list->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')       
                ->leftJoin('web_status_rekon',function($join) 
                      {
                        $join->on('web_status_rekon.tanggal','=','vw_transaksi_pln_prepaid.tanggal');
                        $join->on('web_status_rekon.jenis_loket','=','vw_transaksi_pln_prepaid.jenis_loket');
                      })
                ->groupBy('vw_transaksi_pln_prepaid.tanggal','vw_transaksi_pln_prepaid.user_',
                        'vw_transaksi_pln_prepaid.loket_code','vw_transaksi_pln_prepaid.loket_name',
                        'vw_transaksi_pln_prepaid.jenis_loket','vw_transaksi_pln_prepaid.jenis_transaksi',
                        'web_status_rekon.status','web_mntr_shareLoket.pdam')
                ->union($pdam)
                ->union($pln)
                ->union($pln_nontaglis)
                ->get();
      }else

      //USER
      {
        $pdam=DB::table('vw_pdambjm_trans')
                ->select('vw_pdambjm_trans.tanggal AS tanggal','vw_pdambjm_trans.user_ AS user_',
                  'vw_pdambjm_trans.loket_code AS loket_code','vw_pdambjm_trans.loket_name AS loket_name',
                  'vw_pdambjm_trans.jenis_loket AS jenis_loket','vw_pdambjm_trans.jenis_transaksi AS jenis_transaksi',
                  'web_status_rekon.status','web_mntr_shareLoket.pdam',
                DB::raw("sum(vw_pdambjm_trans.tagihan) AS tagihan,sum(vw_pdambjm_trans.admin) AS admin,
                      sum(vw_pdambjm_trans.total) AS total,count(0) AS jumlah, '' as nomor,'' as aksi"))
                ->whereBetween('vw_pdambjm_trans.tanggal', [$dari,$sampai])
                ->whereIn('vw_pdambjm_trans.loket_code',$array_loket)
                ->leftJoin('lokets','lokets.loket_code','=','vw_pdambjm_trans.loket_code')
                ->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')       
                ->leftJoin('web_status_rekon',function($join) 
                      {
                        $join->on('web_status_rekon.tanggal','=','vw_pdambjm_trans.tanggal');
                        $join->on('web_status_rekon.jenis_loket','=','vw_pdambjm_trans.jenis_loket');
                      })
                ->groupBy('vw_pdambjm_trans.tanggal','vw_pdambjm_trans.user_',
                        'vw_pdambjm_trans.loket_code','vw_pdambjm_trans.loket_name',
                        'vw_pdambjm_trans.jenis_loket','vw_pdambjm_trans.jenis_transaksi',
                        'web_status_rekon.status','web_mntr_shareLoket.pdam'); 

        $pln=DB::table('vw_transaksi_pln')
                ->select('vw_transaksi_pln.tanggal AS tanggal','vw_transaksi_pln.user_ AS user_',
                  'vw_transaksi_pln.loket_code AS loket_code','vw_transaksi_pln.loket_name AS loket_name',
                  'vw_transaksi_pln.jenis_loket AS jenis_loket','vw_transaksi_pln.jenis_transaksi AS jenis_transaksi',
                  'web_status_rekon.status','web_mntr_shareLoket.pdam',
                DB::raw("sum(vw_transaksi_pln.tagihan) AS tagihan,sum(vw_transaksi_pln.admin) AS admin,
                      sum(vw_transaksi_pln.total) AS total,count(0) AS jumlah, '' as nomor,'' as aksi"))
                ->whereBetween('vw_transaksi_pln.tanggal', [$dari,$sampai])
                ->whereIn('vw_transaksi_pln.loket_code',$array_loket)
                ->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln.loket_code')
                ->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')       
                ->leftJoin('web_status_rekon',function($join) 
                      {
                        $join->on('web_status_rekon.tanggal','=','vw_transaksi_pln.tanggal');
                        $join->on('web_status_rekon.jenis_loket','=','vw_transaksi_pln.jenis_loket');
                      })
                ->groupBy('vw_transaksi_pln.tanggal','vw_transaksi_pln.user_',
                        'vw_transaksi_pln.loket_code','vw_transaksi_pln.loket_name',
                        'vw_transaksi_pln.jenis_loket','vw_transaksi_pln.jenis_transaksi',
                        'web_status_rekon.status','web_mntr_shareLoket.pdam');

        $pln_nontaglis=DB::table('vw_transaksi_pln_nontaglis')
                ->select('vw_transaksi_pln_nontaglis.tanggal AS tanggal','vw_transaksi_pln_nontaglis.user_ AS user_',
                  'vw_transaksi_pln_nontaglis.loket_code AS loket_code','vw_transaksi_pln_nontaglis.loket_name AS loket_name',
                  'vw_transaksi_pln_nontaglis.jenis_loket AS jenis_loket','vw_transaksi_pln_nontaglis.jenis_transaksi AS jenis_transaksi',
                  'web_status_rekon.status','web_mntr_shareLoket.pdam',
                DB::raw("sum(vw_transaksi_pln_nontaglis.tagihan) AS tagihan,sum(vw_transaksi_pln_nontaglis.admin) AS admin,
                      sum(vw_transaksi_pln_nontaglis.total) AS total,count(0) AS jumlah, '' as nomor,'' as aksi"))
                ->whereBetween('vw_transaksi_pln_nontaglis.tanggal', [$dari,$sampai])
                ->whereIn('vw_transaksi_pln_nontaglis.loket_code',$array_loket)
                ->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_nontaglis.loket_code')
                ->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')       
                ->leftJoin('web_status_rekon',function($join) 
                      {
                        $join->on('web_status_rekon.tanggal','=','vw_transaksi_pln_nontaglis.tanggal');
                        $join->on('web_status_rekon.jenis_loket','=','vw_transaksi_pln_nontaglis.jenis_loket');
                      })
                ->groupBy('vw_transaksi_pln_nontaglis.tanggal','vw_transaksi_pln_nontaglis.user_',
                        'vw_transaksi_pln_nontaglis.loket_code','vw_transaksi_pln_nontaglis.loket_name',
                        'vw_transaksi_pln_nontaglis.jenis_loket','vw_transaksi_pln_nontaglis.jenis_transaksi',
                        'web_status_rekon.status','web_mntr_shareLoket.pdam');

        $list=DB::table('vw_transaksi_pln_prepaid')
                ->select('vw_transaksi_pln_prepaid.tanggal AS tanggal','vw_transaksi_pln_prepaid.user_ AS user_',
                  'vw_transaksi_pln_prepaid.loket_code AS loket_code','vw_transaksi_pln_prepaid.loket_name AS loket_name',
                  'vw_transaksi_pln_prepaid.jenis_loket AS jenis_loket','vw_transaksi_pln_prepaid.jenis_transaksi AS jenis_transaksi',
                  'web_status_rekon.status','web_mntr_shareLoket.pdam',
                DB::raw("sum(vw_transaksi_pln_prepaid.tagihan) AS tagihan,sum(vw_transaksi_pln_prepaid.admin) AS admin,
                      sum(vw_transaksi_pln_prepaid.total) AS total,count(0) AS jumlah, '' as nomor,'' as aksi"))
                ->whereBetween('vw_transaksi_pln_prepaid.tanggal', [$dari,$sampai])
                ->whereIn('vw_transaksi_pln_prepaid.loket_code',$array_loket)
                ->leftJoin('lokets','lokets.loket_code','=','vw_transaksi_pln_prepaid.loket_code')
                ->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')       
                ->leftJoin('web_status_rekon',function($join) 
                      {
                        $join->on('web_status_rekon.tanggal','=','vw_transaksi_pln_prepaid.tanggal');
                        $join->on('web_status_rekon.jenis_loket','=','vw_transaksi_pln_prepaid.jenis_loket');
                      })
                ->groupBy('vw_transaksi_pln_prepaid.tanggal','vw_transaksi_pln_prepaid.user_',
                        'vw_transaksi_pln_prepaid.loket_code','vw_transaksi_pln_prepaid.loket_name',
                        'vw_transaksi_pln_prepaid.jenis_loket','vw_transaksi_pln_prepaid.jenis_transaksi',
                        'web_status_rekon.status','web_mntr_shareLoket.pdam')
                ->union($pdam)
                ->union($pln)
                ->union($pln_nontaglis)
                ->get();                                                      

      }      
      return Response::json(array(
        'status' => 'Success',
        'message' => '-',
        'data' => $list
      ),200);
    }


}
