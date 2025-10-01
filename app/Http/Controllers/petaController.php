<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use DB;
use Response;
use App\Model\loketPetaModel; 

class petaController extends Controller
{
    public function peta()
    {                 
      return view('peta.peta');
    }
    public function ajaxPeta()
    { 
      $list=loketPetaModel::get(); 
                          
      if (empty($list)){$list='';}
      return $data = $list->toArray();
    }
    public static function simpanPeta(Request $req)
    {  
      if(!empty($req->id)){
        loketPetaModel::where('id', $req->id)->update([
          "nama" => $req->nama,
          "alamat" => $req->alamat,
          "latitude" => $req->latitude,
          "longitude" => $req->longitude
        ]);              
      }else {
        loketPetaModel::insert([
          "nama" => $req->nama,
          "alamat" => $req->alamat,
          "latitude" => $req->latitude,
          "longitude" => $req->longitude
        ]);       
      }  
    }

    public function listLoket()
    {                
      $list= loketPetaModel::get();     
      return view('peta.listLoketPeta',compact('list'));
    }
    public function ajaxListLoket()
    {
        $list =DB::select('SET @nom=0');
        $list= loketPetaModel::select('web_mntr_loketPeta.*',
              DB::raw(" (@nom := @nom+1) nomor,'' as aksi"))->get();
      
      return Response::json(array(
            'status' => 'Success',
            'message' => '-',
            'data' => $list
          ),200);
    }

    public function getLoket($id)
    {
        $data=loketPetaModel::where('id', $id)
                       ->first();
         return json_encode($data);
    }

    public function hapusLoket($id)
    {
      loketPetaModel::where('id', $id)
                 ->delete();
    }
}
