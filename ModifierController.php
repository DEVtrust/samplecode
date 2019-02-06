<?php
    /* Class: ModifierController 
     * purpose: Manage all the infromation related to all modifire.
     * created by: deepak
     * last updated: 19-jan-2019
     * updated by: deepak
     */
namespace App\Http\Controllers\Modifier;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Modifier;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\ModifierProductRel;

class ModifierController extends Controller
{
    public $successStatus = 200;
  public function __construct() {
        
    }
     /* function: returnErrorMsg 
     * purpose: Manage all the error messages.
     * created by: deepak
     * last updated: 19-jan-2019
     * updated by: deepak
     */
    
   public static function returnErrorMsg($error){
       if(gettype($error)=="string"){
            $result = [
                'success' => FALSE,
                'message' => $error
            ]; 
        }
        
        else if(gettype($error)=="object"){
        $messageArr = json_decode($error, true);
        $i = 1;
        if(count($messageArr)>1){
            foreach ($messageArr as $key => $value) {
            foreach ($value as $key => $value) {
                $arr[] = $i++ . ') ' . $value;
            }
            }
            $errorMsg = implode(' ', $arr);
        }
        else{
            foreach ($messageArr as $key => $value) {
            foreach ($value as $key => $value) {
                $arr[] = $value;
            }
            }
            $errorMsg = $arr;
        }
        
        $result = [
            'success' => FALSE,
            'message' => $errorMsg
        ];
    }
    return $result;
        
    } 
    /* function: masterModifierCreate 
     * purpose: Fetch the list of modifire
     * created by: deepak
     * last updated: 25-jan-2019
     * updated by: deepak
     */
   public function masterModifierCreate(Request $request){
    DB::beginTransaction(); 
        try {
            $base_url =  url('/');
            
            
            
            if (!empty($request->id)) {
                $rules = [
                'name' => 'required|max:100',
                'id' => 'required',
                ];
            }else{
                $rules = [
                'resort_id' => 'required',
                'name' => 'required|max:100',
               ];
            }
                       
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                //throw new Exception($validator->errors());
                $result = self::returnErrorMsg($validator->errors());
                return response()->json($result);
            }
            
            
                           
            if (!empty($request->id)) {
                
                if(isset($request->tags)){
                        $tagData = $request->tags;
                        if (strpos($tagData, ']') !== false) {
                               $data = substr($tagData, 0, strpos($tagData, "]"));
                               $tagArr = explode("[", $data,2);
                               $modifierName = $tagArr[0];
                               $modifierPrice = $tagArr[1];
                            }
                            else
                            {
                                $modifierName = $tagData;
                                $modifierPrice = 0.00;
                            }
                        
                            $modifierCheck = Modifier::where('resort_id', '=', $request->resort_id)
                                    //->where("group_name", "=", $request->name)
                                    ->where("name", "=", $modifierName)
                                    ->first();
                            
                            if($modifierCheck){
                                if($request->original_modifier!=$modifierName){       //same name modifier exist within same resort
                                    //update information except name and price  
                                    $inputData['group_name'] = isset($request->name) ? trim($request->name) : '';
                                    $inputData['modifier_limit'] = isset($request->limitModifier) ? trim($request->limitModifier) : 0;
                                    $inputData['optional'] = isset($request->optional) ? trim($request->optional) : 0;
                                    $inputData['type'] = isset($request->type) ? trim($request->type) : "limited";
                                    $inputData['updated_by'] = isset($request->updatedBy) ? trim($request->updatedBy) : null;
                                    $resUpdate = Modifier::where('id', $request->id)->update($inputData);
                                    
                                }
                                else{
                                    $inputData['group_name'] = isset($request->name) ? trim($request->name) : '';
                                    $inputData['modifier_limit'] = isset($request->limitModifier) ? trim($request->limitModifier) : 0;
                                    $inputData['optional'] = isset($request->optional) ? trim($request->optional) : 0;
                                    $inputData['type'] = isset($request->type) ? trim($request->type) : "limited";
                                    //$inputData['price'] = isset($request->price) ? trim($request->price) : 0.00;
                                    $inputData['updated_by'] = isset($request->updatedBy) ? trim($request->updatedBy) : null;
                                    $resUpdate = Modifier::where('id', $request->id)->update($inputData);
                                }
                            }
                            else
                            {
                                $inputData['group_name'] = isset($request->name) ? trim($request->name) : '';
                                $inputData['name'] = $modifierName;
                                $inputData['modifier_limit'] = isset($request->limitModifier) ? trim($request->limitModifier) : 0;
                                $inputData['optional'] = isset($request->optional) ? trim($request->optional) : 0;
                                $inputData['type'] = isset($request->type) ? trim($request->type) : "limited";
                                $inputData['price'] = isset($modifierPrice) ? trim($modifierPrice) : 0.00;
                                $inputData['updated_by'] = isset($request->updatedBy) ? trim($request->updatedBy) : null;
                                
                                $resUpdate = Modifier::create($inputData);
                                
                            }
                            
                        
                        
                    }
                
                if($resUpdate){
                    if(!$request->isMaster){
                        //Create or update modifier_product_rel table
                    }
                }
                    $result = [
                   'success' => TRUE,
                   'message' => 'Modifier Updated successfully.'
                   ];
            } 
            else {
               
                $inputData = [
                    'resort_id' => $request->resort_id,
                    'group_name' => isset($request->name) ? trim($request->name) : '',
                    'modifier_limit' => isset($request->limitModifier) ? trim($request->limitModifier) : 0,
                    'optional' => isset($request->optional) ? trim($request->optional) : 0,
                    'type' => isset($request->type) ? trim($request->type) : "limited",
//                    'price' => isset($request->price) ? trim($request->price) : 0.00,
                    'updated_by' => isset($request->updatedBy) ? trim($request->updatedBy) : null,
                    'created_by' => isset($request->createdBy) ? trim($request->createdBy) : null,
                ];
                
                if (isset($request->tags)) {
                    $tagData = $request->tags;
                    $tagDataArr = explode(",", $tagData);
                    $lenTag = count($tagDataArr);
                    if ($lenTag > 1) {
                        for ($i = 0; $i < $lenTag; $i++) {
                            if (strpos($tagDataArr[$i], ']') !== false) {
                                $data = substr($tagDataArr[$i], 0, strpos($tagDataArr[$i], "]"));
                                $tagArr = explode("[", $data, 2);
                                $inputData['name'] = $tagArr[0];
                                $inputData['price'] = $tagArr[1];
                            } else {
                                $inputData['name'] = $tagDataArr[$i];
                                $inputData['price'] = 0.00;
                            }

                            $modifierCheck = Modifier::where('resort_id', '=', $request->resort_id)
                                    ->where("name", "=", $inputData['name'])
                                    ->first();
                            if (!isset($modifierCheck->id) || empty($modifierCheck->id)) {
                                $resCreate = Modifier::create($inputData);
                            }
                        }
                    } else {
                        if (strpos($tagDataArr[0], ']') !== false) {
                            $data = substr($tagDataArr[0], 0, strpos($tagDataArr[0], "]"));
                            $tagArr = explode("[", $data, 2);
                            $inputData['name'] = $tagArr[0];
                            $inputData['price'] = $tagArr[1];
                        } else {
                            $inputData['name'] = $tagDataArr[0];
                            $inputData['price'] = 0.00;
                        }

                        $modifierCheck = Modifier::where('resort_id', '=', $request->resort_id)
                                ->where("name", "=", $inputData['name'])
                                ->first();
                        if (!isset($modifierCheck->id) || empty($modifierCheck->id)) {
                            $resCreate = Modifier::create($inputData);
                        }
                    }
                }


                if($resCreate){
                        if(!$request->isMaster){
                            //Create or update modifier_product_rel table
                        }
                    }
                        $result = [ 'success' => TRUE,
                                    'message' => 'Modifier saved successfully.'
                                  ];
                
                
            }
         DB::commit();   
        } catch (Exception $ex) {
             DB::rollback();
            $result = self::returnErrorMsg($ex->getMessage());
        }
        return response()->json($result);
    }
           
    /* function: masterModifierById 
     * purpose: Modified the master table value.
     * created by: deepak
     * last updated: 29-jan-2019
     * updated by: deepak
     */
    public function masterModifierById(Request $request) {

        try {
            $matchThese = [];

            $filter = $request->header('filter');
            $dataArr = json_decode($filter, TRUE);
            if ($filter) {
                $id = (isset($dataArr['modifier_id'])) ? $dataArr['modifier_id'] : '';
                $data = Modifier::masterModifierById($id);
                return response()->json(['success' => true, 'modifierListData' => $data], $this->successStatus);
            }
        } catch (Exception $ex) {
            $result = [
                'success' => FALSE,
                'message' => $ex->getMessage()
            ];
        }
        return response()->json($result);
    }
    /* function: listForProduct 
     * purpose: Get the list of the poducts
     * created by: deepak
     * last updated: 29-jan-2019
     * updated by: deepak
     */
    public function listForProduct(Request $request){
        try {
            $matchThese = [];

            $filter = $request->header('filter');
            $dataArr = json_decode($filter, TRUE);
            if ($filter) {
                $id = $dataArr['resort_id'];
                $data = Modifier::select(DB::raw('CONCAT( name, " [",price,"]") as label'),'id as value')
                        ->where("resort_id", $id)->get();
                return response()->json(['success' => true, 'modifierListData' => $data], $this->successStatus);
            }
        } catch (Exception $ex) {
            $result = [
                'success' => FALSE,
                'message' => $ex->getMessage()
            ];
        }
        return response()->json($result);
    }
    
     /* function: fetchForExistingProduct 
     * purpose: Fetch for Existing Products 
     * created by: deepak
     * last updated: 29-jan-2019
     * updated by: deepak
     */
    public function fetchForExistingProduct(Request $request){
        try {
            $matchThese = [];

            $filter = $request->header('filter');
            $dataArr = json_decode($filter, TRUE);
            if ($filter) {
                $productId = $dataArr['product_id'];
                $subcategoryId = $dataArr['subcategory_id'];
                $resortId = $dataArr['resort_id'];
                
                $data = ModifierProductRel::fetchForExistingProduct($resortId, $productId, $subcategoryId);
                return response()->json(['success' => true, 'modifierListData' => $data], $this->successStatus);
            }
        } catch (Exception $ex) {
            $result = [
                'success' => FALSE,
                'message' => $ex->getMessage()
            ];
        }
        return response()->json($result);
    }
     /* function: fetchForSameSubCategory 
     * purpose: Fetch for the same subcategory 
     * created by: deepak
     * last updated: 29-jan-2019
     * updated by: deepak
     */
    
    public function fetchForSameSubCategory(Request $request){
        try {
            $matchThese = [];

            $filter = $request->header('filter');
            $dataArr = json_decode($filter, TRUE);
            if ($filter) {
                $productId = $dataArr['product_id'];
                $subcategoryId = $dataArr['subcategory_id'];
                $resortId = $dataArr['resort_id'];
                
                $data = ModifierProductRel::fetchForSameSubCategory($resortId, $productId, $subcategoryId);
                return response()->json(['success' => true, 'modifierListData' => $data], $this->successStatus);
            }
        } catch (Exception $ex) {
            $result = [
                'success' => FALSE,
                'message' => $ex->getMessage()
            ];
        }
        return response()->json($result);
    }
    
 /* function: isExistingModifier 
     * purpose: Check for exisiting modifier
     * created by: deepak
     * last updated: 29-jan-2019
     * updated by: deepak
     */
    public function isExistingModifier(Request $request){
        try {
            $matchThese = [];

            $filter = $request->header('filter');
            $dataArr = json_decode($filter, TRUE);
            if ($filter) {
                
                $tagData = $dataArr['name'];
                        if (strpos($tagData, ']') !== false) {
                               $data = substr($tagData, 0, strpos($tagData, "]"));
                               $tagArr = explode("[", $data,2);
                               $modifierName = $tagArr[0];
                               
                            }
                            else
                            {
                                $modifierName = $tagData;
                                
                            }
                
                $resortId = $dataArr['resort_id'];
                
                $modifierCheck = Modifier::where('resort_id', '=', $resortId)
                                        ->where("name", "=", $modifierName)
                                        ->first();
                if(!isset($modifierCheck->id) || empty($modifierCheck->id)){
                    
                    return response()->json(['success' => true,'isExisting' => false], $this->successStatus);
                }
                else{
                    return response()->json(['success' => true, 'isExisting' => true, 'name'=> $modifierName, 'groupName'=> $modifierCheck->group_name], $this->successStatus);
                }
                
            }
        } catch (Exception $ex) {
            $result = [
                'success' => FALSE,
                'message' => $ex->getMessage()
            ];
        }
        return response()->json($result);
    }
    
    /* function: deleteModifier 
     * purpose: delete Modifier and relation as per modifierId
     * created by: deepak
     * last updated: 29-jan-2019
     * updated by: deepak
     */

     public function deleteModifier(Request $request) {
        DB::beginTransaction();
        try {

            if (!empty($request->modifierId)) {

                
                if (ModifierProductRel::where('modifier_id', '=', $request->modifierId)->exists()){
                        $prodModifier = ModifierProductRel::where('modifier_id', '=', $request->modifierId)->delete();
                        $modifier = Modifier::where('id', "=", $request->modifierId)->delete();
                    }
                
                else{
                    $modifier = Modifier::where('id', "=", $request->modifierId)->delete();
                    
                }
                

                $result = [
                    'success' => TRUE,
                    'message' => "Modifier deleted successfully."
                ];
            } else {
                $result = [
                    'success' => FALSE,
                    'message' => "Modifier not found."
                ];
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            $result = [
                'success' => FALSE,
                'message' => $ex->getMessage()
            ];
        }
        return response()->json($result);
    } 
}

