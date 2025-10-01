<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use DB;
use Response; 
use Validator;
use App\Model\lokets;
use App\Model\user;

class UserController extends Controller
{
    public function setupUser()
    {   
        set_time_limit(0);
        $lokets= lokets::select('id as id', 'nama as text')->get();
        $list= user::select('web_mntr_users.id as id','web_mntr_users.name as name','web_mntr_users.email as email',
                    'web_mntr_users.user as username','web_mntr_users.id_loket','lokets.nama as nama','web_mntr_users.level as level')
                    ->leftJoin('lokets','web_mntr_users.id_loket','=','lokets.id')->get();
        return view('user.setupUser',compact('list','lokets'));
    }
    public function ajaxListUser()
    {
        set_time_limit(0);
        $list =DB::select('SET @nom=0');
        $list= user::select('web_mntr_users.id as id','web_mntr_users.name as name','web_mntr_users.email as email',
                    'web_mntr_users.user as username','web_mntr_users.id_loket','lokets.nama as nama','web_mntr_users.level as level',
                    DB::raw(" (@nom := @nom+1) nomor,'' as aksi"))
                    ->leftJoin('lokets','web_mntr_users.id_loket','=','lokets.id')->get();
        return Response::json(array(
            'status' => 'Success',
            'message' => '-',
            'data' => $list
          ),200);
    }

    public static function ajaxSimpanUser(Request $req)
    {   
     set_time_limit(0);   
     if($req->level=="2"||$req->level=="6"){$loket=$req->loket;}
        else {$loket=0;}      
       if(!empty($req->id)){
    //update
        if(!empty($req->password)){
            user::where('id', $req->id)->update([
                "name" => $req->nama,
                "email" => $req->email,
                "user" => $req->user,
                "password" => md5($req->password),
                "level" => $req->level,
                "id_loket" => $loket
                ]); 
        }else{
            user::where('id', $req->id)->update([
                "name" => $req->nama,
                "email" => $req->email,
                "user" => $req->user,
                "level" => $req->level,
                "id_loket" => $loket
                ]); 
        }                  
        }else {
            $rules = array(
              'user'=> 'required|unique:web_mntr_users,user' 
            );
            $validator = Validator::make($req->toArray(), $rules);

              if ($validator->fails()) {
                  return Response::json(array(
                    'status' => 'Failed',
                    'message' => '-'
                  ),200);

              } else {
                user::insert([
                    "name" => $req->nama,
                    "email" => $req->email,
                    "user" => $req->user,
                    "password" => md5($req->password),
                    "level" => $req->level,
                    "id_loket" => $loket
                    ]); 
              }          
        }
        return Response::json(array(
            'status' => 'Success',
            'message' => '-'
          ),200); 
    }

    public function ajaxUser($id)
    {
        $data=user::where('id', $id)
                       ->first();
         return json_encode($data);
    }
    public function ajaxHapusUser($id)
    {
    user::where('id', $id)
                 ->delete();
    } 
}
