<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AdminController;
use Yajra\Datatables\Datatables;
use App\Aboutdep;
use App\User;
use App\CollegeMasterMenu;
use App\MenuTypeMaster;
use Validator;
use App\DocumentFile;
use App\Helpers\LogActivity;
use App\ImageUpload;
use DB;

class AboutdepController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->aboutdep = new Aboutdep;
		$this->user = new User;
        $this->collegeMasterMenu = new CollegeMasterMenu;
        $this->documentFile = new DocumentFile;
    }

    public function index(Request $request,$id)
    {
		$dpuser = User::latest()->where('type',5)->where('parent_id',auth()->user()->id)->get();
		$faculty_id=auth()->user()->id;
		$id2 = user::latest()->where('type',5)->where('parent_id',auth()->user()->id)->pluck('id');
		$data2 = Aboutdep::latest()->where('faculty_id', '=', $id2)->get();
    	if ($request->ajax()) {
			
		//$data2 = user::latest()->where('type',5)->where('parent_id',auth()->user()->id)->pluck('id');
		$data = Aboutdep::latest()->where('faculty_id', '=', $id)->get();
		$data2=array();	
        //$data = Aboutdep::latest()->where('faculty_id', '=', $faculty_id)->get();

            return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('image', function($row){
                        if(file_exists(public_path('../upload/testimonial').'/'.$row->image) && $row->image!=null){
                           $image =  '<img src="'.asset('upload/testimonial/').'/'.$row->image.'" height="50" width="50">';
                        }
                        else{
                            $image = '<img src="'.asset('upload/avatar/user.png').'" height="50" width="50">';
                        }
                        return $image;         
                    })
                    ->addColumn('action', function($row){
                           $btn = '<a href="'.route('admin.aboutdep.cms.show',[$row->id]).'" class="btn btn-info btn-xs btn-flat" data-toggle="tooltip" data-placement="top" data-original-title="Show"><i class="fa fa-eye"></i></a>';

                           $btn = $btn.' <a href="'. route('admin.aboutdep.cms.edit',[$row->id]) .'" class="btn btn-primary btn-xs btn-flat" data-toggle="tooltip" data-placement="top" data-original-title="Edit"><i class="fa fa-pencil"></i></a>';

                           $btn = $btn.' <button class="btn btn-danger btn-flat btn-xs remove-crud" data-id="'. $row->id .'" data-action="'. route('admin.aboutdep.cms.destroy',$row->id) .'"  data-toggle="tooltip" data-placement="top" data-original-title="Delete"> <i class="fa fa-trash"></i></button>';

                           return $btn;
                    })
                    ->rawColumns(['action','image'])
                    ->make(true);
        }
        return view('admin.aboutdep.index',compact('data2','dpuser','id'));
    }

    public function create($id)
    {
		$dpuser = User::latest()->where('type',5)->where('parent_id',auth()->user()->id)->get();
    	return view('admin.aboutdep.create',compact('id','dpuser'));
    }

    public function store(Request $request)
    {
        $input = $request->all();
		  
	   
        $validator = Validator::make($input, [
            //'name'=>'required',
            //'department_id'=>'required',
        ],
        [
           // 'department_id.required' => 'The department field is required. '
        ]
        ); 

        if ($validator->passes()) {
            //$fobc = $this->fobc->addFobc($input);
			 $name = $request->input('name');
			 $description = $request->input('description');
		    DB::insert('insert into aboutdep (name, description,faculty_id) values (?, ?,?)', [$input['name'],$input['description'],$input['faculty_id']]);
            LogActivity::addToLog('Fobc created successfully.');
            //notificationMsg('success',$this->crudMessage('add','Library'));
			return back()->with('success','Record has been created successfully.');
            return redirect(route('admin.fobc.index'));
        }
        
        return redirect()->back()->withErrors($validator)->withInput();
    }

    public function show($id)
    {
    	$cms = $this->aboutdep->findaboutdepCms($id);
		$dpuser = User::latest()->where('type',5)->where('parent_id',auth()->user()->id)->get();
    	return view('admin.aboutdep.show',compact('cms','id','dpuser'));
    }
	
    public function edit($id)
    {
        $cms = $this->aboutdep->findaboutdepCms($id);
		$dpuser = User::latest()->where('type',5)->where('parent_id',auth()->user()->id)->get();

        return view('admin.aboutdep.edit',compact('cms','id','dpuser'));
    }

    public function update($id, Request $request)
    {
       

        $input = $request->all();
        

        
        $validator = Validator::make($input, [
           // 'image' => 'image|mimes:jpeg,png,jpg|max:5000',
            'name'=>'required',
           // 'branch'=>'required',
            // 'sort_description'=>'required',
            // 'sort_order'=>'required',
            'description'=>'required',
        ],
        [
            'name.required' => 'The Name Field Is Required.',
            //'branch.required' => 'The Branch Field Is Required.',
            // 'sort_description.required' => 'The Sort Description field is required.',
            'description.required' => 'The description field is required.',
        ]); 

        if ($validator->passes()) {
           

          
            if($request->hasFile('image')){
                $file = ImageUpload::upload('/upload/testimonial',$request->file('image'));
                $fileExist = Testimonials::where('image',$file)->first();
                if (!is_null($fileExist)) {
                    return redirect()->back()->withErrors("Image Allready Exist");
                }else{

                     
                    $unlink = Testimonials::find($id);
                    $file_path = base_path().'/upload/testimonial/'.$unlink->image;


                        if(!is_null($unlink->image))
                        {
                             if(file_exists($file_path))
                            {

                            $image_path =("upload/testimonial/").'/'.$unlink->image;
                          

                            unlink($image_path);
                            }

                        }
                    $input['image'] = $file;
                }
            }


            // if($request->hasFile('campus_img')){
            //     $file = ImageUpload::upload('/upload/collegecampus',$request->file('campus_img'));
            
            //     $fileExist = CampusFacility::where('campus_img',$file)->first();
            //     if (!is_null($fileExist)) {
            //         return redirect()->back()->withErrors("Campus Facility  Image Allready Exist");
            //     }else{
            //         $unlink = CampusFacility::find($id);
            //         $file_path = base_path().'/upload/collegecampus/'.$unlink->campus_img;
                    
            //             if(!is_null($unlink->campus_img))
            //             {
            //                 if(file_exists($file_path))
            //                 {
            //                     $image_path =("upload/collegecampus/").'/'.$unlink->campus_img;

            //                      unlink($image_path);
            //                 }
                            

            //             }
            //         $input['campus_img'] = $file;
            //     }
            // }

            $this->aboutdep->updateaboutdepCms($id, $input);

            LogActivity::addToLog('Testimonial updated successfully.');

            //notificationMsg('success',$this->crudMessage('update','Testimonial'));
            
			return back()->with('success','Record has been updated successfully.');
			
			
            return redirect(route('admin.aboutdep.cms.home'));
        }

        return redirect()->back()->withErrors($validator)->withInput();
    }

    public function destroy(Request $request)
    {

     
        //$cms = $this->testimonials->deletetestimonial($request->id);
       DB::delete('delete from aboutdep where id = ?',[$request->id]);
     
	  return back()->with('success','Record has been deleted successfully.');

        LogActivity::addToLog('Testimonial deleted successfully.');

        //notificationMsg('success',$this->crudMessage('delete','Testimonial'));
        
        return redirect()->back();
    }


}
