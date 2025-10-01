<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use DB;
use Response;
use App\Model\lokets;
use App\Model\shareLoket;

class loketController extends Controller
{

public function listLoket()
{
	$lokets= lokets::select('id as id', 'nama as text')->get();	
	return view('loket.jenisLoket',compact('lokets'));
}

public function ajaxListLoket()
{
	set_time_limit(0);
	$list =DB::select('SET @nom=0');
  	$list= lokets::select('lokets.id','lokets.nama','lokets.loket_code','lokets.tipe',
  					'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid',
  					'web_mntr_shareLoket.pln_postpaid',
  					'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
  					'web_mntr_shareLoket.pln_postpaid_n',
  					'web_mntr_shareLoket.pln_prepaid_n','web_mntr_shareLoket.pln_nontaglis_n',
  					'web_mntr_shareLoket.limit',
  					DB::raw(" (@nom := @nom+1) nomor"))
  					->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')
  					->get();

	return Response::json(array(
            'status' => 'Success',
            'message' => '-',
            'data' => $list
          ),200);
}

public static function ajaxSimpanLoket(Request $req)
{  
 set_time_limit(0);	
 DB::transaction(function() use($req) {
	
	Lokets::where('id', $req->loket)->update([
        "tipe" => $req->tipe
        ]);
	
	$result = shareLoket::where('id_lokets',$req->loket)->get();

	$data=array("limit" => $req->limit,
	        "pdam" => $req->pdam,
	        "pln_postpaid" => $req->pln_postpaid,
	        "pln_prepaid" => $req->pln_prepaid,
	        "pln_nontaglis" => $req->pln_nontaglis,
	        "pln_postpaid_n" => $req->pln_postpaid_n,
	        "pln_prepaid_n" => $req->pln_prepaid_n,
	        "pln_nontaglis_n" => $req->pln_nontaglis_n);

    if (count($result)>0)
    {
    	shareLoket::where('id_lokets', $req->loket)->update($data);
    }else
    {
    	$data["id_lokets"]=$req->loket;
    	shareLoket::insert($data);
    }  
 });                         
}

public function ajaxLoketsTipe($tipe)
{
	$data=lokets::select('id as id', 'nama as text');
	if  ($tipe<>'ALL'){
	    $data=$data->where('tipe','=',$tipe);
	  }
	$data=$data->get();
	return json_encode($data); 
}  

public function ajaxLokets($id)
{
	$data=lokets::select('lokets.id','lokets.nama','lokets.loket_code','lokets.tipe',
				'web_mntr_shareLoket.pdam','web_mntr_shareLoket.pln_prepaid',
				'web_mntr_shareLoket.pln_postpaid',
				'web_mntr_shareLoket.pln_prepaid','web_mntr_shareLoket.pln_nontaglis',
				'web_mntr_shareLoket.pln_postpaid_n',
  				'web_mntr_shareLoket.pln_prepaid_n','web_mntr_shareLoket.pln_nontaglis_n',
				'web_mntr_shareLoket.limit')
				->where('lokets.id',$id)
				->leftJoin('web_mntr_shareLoket','lokets.id','=','web_mntr_shareLoket.id_lokets')
				->first();
	return json_encode($data); 
}



public function tes()
{
    $tanggal = date('Y-m-d', strtotime('+8 hours')); 
	var_dump($tanggal);
}



}
