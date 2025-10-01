<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use DB;
use Response;
use App\Model\menu;
use App\Model\lokets;
use App\Model\role;

class menuController extends Controller
{
    public function setupMenu()
    {   
        set_time_limit(0);
        $paren= menu::select('id as id', 'name as text')
                ->where('parent_id','0')
                ->get();
                
        $list= menu::select('web_mntr_menu.id as id','web_mntr_menu.name as name','web_mntr_menu.url as url',
              'web_mntr_menu.class as class','web_mntr_menu.parent_id','menu_parent.name as parent')
              ->leftJoin('web_mntr_menu AS menu_parent','menu_parent.id','=','web_mntr_menu.parent_id')->get();

                
      return view('menu.setupMenu',compact('list','paren'));
    }
    public function ajaxListMenu()
    {
        set_time_limit(0);
        $list =DB::select('SET @nom=0');
        $list= menu::select('web_mntr_menu.id as id','web_mntr_menu.name as name','web_mntr_menu.url as url',
              'web_mntr_menu.class as class','web_mntr_menu.parent_id','menu_parent.name as parent',
              DB::raw(" (@nom := @nom+1) nomor,'' as aksi"))
              ->leftJoin('web_mntr_menu AS menu_parent','menu_parent.id','=','web_mntr_menu.parent_id')->get();
      
      return Response::json(array(
            'status' => 'Success',
            'message' => '-',
            'data' => $list
          ),200);
    }
    public function ajaxMenu($id)
    {
        $data=menu::where('id', $id)
                       ->first();
         return json_encode($data);
    }

    public static function ajaxSimpanMenu(Request $req)
    {  
      set_time_limit(0);
      if (!empty($req->parent)){$idparent=$req->parent;} else {$idparent=0;}     
       if(!empty($req->id)){
    //update
            menu::where('id', $req->id)->update([
                    "name" => $req->nama,
                    "url" => $req->url,
                    "class" => $req->class,
                    "parent_id" => $idparent
                    ]);            
        }else {
            menu::insert([
                    "name" => $req->nama,
                    "url" => $req->url,
                    "class" => $req->class,
                    "parent_id" => $idparent
                    ]);     
        }
    }
    public function ajaxHapusMenu($id)
    {
      menu::where('id', $id)
                 ->delete();
    }


    public function setupPermission()
    {
      return view('menu.setupPermission');
    }
    public function ajaxPermission($id)
	 {
      set_time_limit(0);
      if ($id==0){
        $list = new \StdClass;
      }else{
        $list =DB::select('SET @nom=0');
        $list= menu::select('web_mntr_role.level','web_mntr_menu.id as id_menu','web_mntr_menu.name',
                            //'web_mntr_menu.parent_id','menu_parent.name as parent',
                            DB::raw(" (@nom := @nom+1) nomor,'' as cek,case when web_mntr_menu.parent_id=0 then 'Parent' 
                                              else  menu_parent.name end as parent"))
            ->leftJoin('web_mntr_menu AS menu_parent','menu_parent.id','=','web_mntr_menu.parent_id')
            ->leftJoin('web_mntr_role',function($join) use ($id)
                {
                  $join->on('web_mntr_role.menu_id','=','web_mntr_menu.id');
                  $join->where('web_mntr_role.level',$id);
                })
            ->orderBy('web_mntr_menu.id')
            ->get();
      }      
      return Response::json(array(
        'status' => 'Success',
        'message' => '-',
        'data' => $list
      ),200);
    }
    
    public function ajaxSimpanPermission($lvl,Request $req)
    {
       set_time_limit(0); 
       DB::transaction(function() use($lvl,$req) {
            role::where('level', $lvl)->delete();
            $level = array();

            foreach($req->cek as $c){
                $level[] = ["level"=>$lvl,"menu_id" => $c];
            }
            role::insert($level);
        });
    }
}
