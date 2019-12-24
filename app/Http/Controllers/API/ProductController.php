<?php
namespace App\Http\Controllers\API;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\DB;
use File;
class ProductController extends Controller
{
public $successStatus = 200;
/**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function categoryInsert(Request $request)
    {
        $users = Auth::user();
        if(Auth::user()){
            $user_id=Auth::user();
            $validator = Validator::make($request->all(), [
                'name' => 'required|unique:category,name',
                'category_image' => 'required',
                'meta_title' => 'required',
                'meta_keyword' => 'required',
                'meta_description' => 'required',
                'image_folder_name' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['error'=>$validator->errors()], 401);
            }
            $input['name'] = $request->name;
            $input['category_image'] = $request->name.".jpg";
            $input['meta_title'] = $request->meta_title;
            $input['meta_keyword'] = $request->meta_keyword;
            $input['meta_description'] = $request->meta_description;
            $input['image_folder_name'] = $request->image_folder_name;
            $input['created_by'] = $user_id->id;
            $input['created_at'] = date('Y-m-d H:i:s');
            $input['updated_at'] = date('Y-m-d H:i:s');
            $path = public_path('images/products/'.$request->image_folder_name);
            $paths = public_path('images/category')."/" . $request->name . ".jpg";
            if(!File::isDirectory($path)){
                File::makeDirectory($path, 0777, true, true);
                $upload_image = file_put_contents($paths, base64_decode($request->category_image));
                $category = DB::table('category')->insert($input);
                return response()->json(['success'=>$category], $this-> successStatus);
            }else{
                return response()->json(['error'=>'Folder name alredy exists'], 401);
            }
        }else{
            return response()->json(['error'=>'Unauthorised'], 401);
        }
    }
    public function getImageDetails(){
        //$paths = public_path('images/products')."/" . $request->name . ".jpg";
        $result = [];
        $dirs = File::directories(public_path('images/products'));
        foreach($dirs as $dir){
            var_dump($dir); //actually string: /home/mylinuxiser/myproject/public"
            $files = File::files($dir);
            foreach($files as $f){
                //var_dump($f); //actually object SplFileInfo
                if(ends_with($f, ['.png', '.jpg', '.jpeg', '.gif'])){
                    $result[] = $f->getPathname(); //prefix your public folder here if you want
                    //echo $res= file_get_contents($f->getPathname());
                   // echo  $SummaryText = File::get($f->getPathname());
                    $imgs = glob($f->getPathname());
                    $exif_data = exif_read_data($imgs[0],0,true);
                    print_r($exif_data);
                    $exif_description =array();
                    if (!empty($exif_data['ImageDescription']))
                        $exif_description = $exif_data['ImageDescription'];
                        print_r($exif_description);
                    list($width, $height, $type, $attr) = getimageproperty($f->getPathname());
                    echo "Image width " .$width;
                    echo "<BR>";
                    echo "Image height " .$height;
                    echo "<BR>";
                    echo "Image type " .$type;
                    echo "<BR>";
                    echo "Attribute " .$attr;
                }
            }
        }
        return $result; //will be in this case ['img/text1_logo.png']
    }
    public function check_device($security){
        $status1 = DB::table('oauth_access_tokens')->where('id', '=', $security)->first();
        if(isset($status1) && $status1->user_id!=''){
            $user = DB::table('oauth_access_tokens')->where('user_id', '=', $status1->user_id)->orderBy('id','desc')->first();
            if($security==$user->id){
                return $user->user_id;
            }else{
                return false;
            }
        }else{
            return false;
        }
	}
}
