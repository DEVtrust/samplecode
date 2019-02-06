<?php
     /* Model: Modifier 
     * purpose: Manage all the database realated query.
     * created by: deepak
     * last updated: 19-jan-2019
     * updated by: deepak
     */
namespace App\Models;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Modifier extends Model
{
    protected $table = 'modifier';
    protected $guarded = ["id"];
    const TABLE = 'modifier';
    
    public static function masterModifierById($modifierId){
       
         return DB::table(self::TABLE )
                ->select(DB::raw('CONCAT( name, " [",price,"]") as tags,created_at, created_by, group_name, id,modifier_limit, optional, resort_id, type, updated_at, updated_by'))
                ->where('id',$modifierId)
                ->get(); 
       
    }
    public static function getModifier($productid,$subcat){
         
         return DB::table(self::TABLE )
                ->select(DB::raw('CONCAT( name, " [",price,"]") as tags,created_at, created_by, group_name, id,modifier_limit, optional, resort_id, type, updated_at, updated_by'))
                ->where('id',$modifierId)
                ->get(); 
        
    }
}
