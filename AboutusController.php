<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AdminController;
use Yajra\Datatables\Datatables;
use App\Aboutus;
use App\Department;
use App\User;
use App\Helpers\LogActivity;
use Validator;
use Hash;
use Auth;
use App\ImageUpload;
use Session;

class AboutusController extends AdminController
{
    public function __construct()
    {

        parent::__construct();
        $this->aboutus = new Aboutus;
        $this->department = new Department;
        $this->user = new User;
    }

    public function index(Request $request)
    {
     //echo "sdf";exit;
	   
        if ($request->ajax()) {
            
            $data = Aboutus::latest()->get();
               //print_r($data);
               //exit;
            return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('department', function($row){
                           $department = explode(',', $row->department_id);
                           $depArray = [];
                           foreach ($department as $key => $value) {
                                $depData = Department::where('id',$value)->first();
                                $depArray[] = $depData->name;
                           }
                           return implode(', ', $depArray);
                    })
                    ->addColumn('status', function($row){
						 
                        $status = '';
                        if ($row->is_disable == 0) {
                            $status = '<label class="label label-success">Enable</label>';
                        }elseif ($row->is_disable== 1) {
                            $status = '<label class="label label-danger">Disable</label>';
                        }
                        return $status;
                    })
                    ->addColumn('action', function($row){
                           $btn = '<a href="'.route('admin.aboutus.show',[$row->id]).'" class="btn btn-info btn-xs btn-flat" data-toggle="tooltip" data-placement="top" data-original-title="Show"><i class="fa fa-eye"></i></a>';

                           $btn = $btn.' <a href="'. route('admin.aboutus.edit',[$row->id]) .'" class="btn btn-primary btn-xs btn-flat" data-toggle="tooltip" data-placement="top" data-original-title="Edit"><i class="fa fa-pencil"></i></a>';

                           $btn = $btn.' <button class="btn btn-danger btn-flat btn-xs remove-crud" data-id="'. $row->id .'" data-action="'. route('admin.aboutus.destroy',$row->id) .'"  data-toggle="tooltip" data-placement="top" data-original-title="Delete"> <i class="fa fa-trash"></i></button>';

                           $id = Auth::user()->id;
                           if ($row->type != 1 && $id == 1) {
                                $btn = $btn.' <a style="margin-top: 5px;" class="btn btn-success btn-flat btn-xs waves-light waves-effect user-login-as" href="'. route('admin.aboutus.department.loginAs',$row->id) .'"  data-toggle="tooltip" data-placement="top" data-original-title="Login With This User"> <i class="fa fa-sign-in"></i> Login As</a>';

                                if ($row->is_disable == 0) {
                                    $btn = $btn.' <a style="margin-top: 5px;" class="btn btn-danger btn-flat btn-xs " data-id="'.$row->id.'" id="user-disible" href="#"  data-toggle="tooltip" data-placement="top" data-original-title="Disable This User"> <i class="fa fa-ban "></i> Disable</a>';
                                }else{
                                    $btn = $btn.' <a style="margin-top: 5px;" class="btn btn-success btn-flat btn-xs waves-light waves-effect user-login-as" href="'. route('admin.aboutus.user.enable',$row->id) .'"  data-toggle="tooltip" data-placement="top" data-original-title="Enable This User"> <i class="fa fa-repeat "></i> Enable</a>';
                                }
                            }

                           return $btn;
                    })
                    ->rawColumns(['action','department','status'])
                    ->make(true);

        }

        return view('admin.aboutus.index' );

    }

    public function create()
    {
        $departmentList = $this->department->getDepartmentList();

        return view('admin.aboutus.create',compact('departmentList'));
    }

    public function store(Request $request)
    {
        $input = $request->all();
        //print_r($input);
        //exit;
        $validator = Validator::make($input, [
            'library_name'=>'required',
            'department_id'=>'required',
            'sort_order'=>'required',
             'file_name'=>'mimes:jpeg,jpg,png,svg|required|max:10000',
            // 'city_village'=>'required',
            // 'taluka'=>'required',
            // 'district'=>'required',
            'state'=>'required',
            'country'=>'required',
            // 'phone_number'=>'required',
            // 'mobile_number'=>'required',
            'email'=>'required|email|unique:users,email',
            'password'=>'required|same:confirm_password|max:15|min:10|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/',
            'confirm_password'=>'required|max:15|min:10|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/',
            // 'website_url'=>'required',   
            // 'principal_name'=>'required',
            // 'about_library'=>'required',
            // 'address'=>'required',
        ],
        [
            'department_id.required' => 'The department field is required. '
        ]
        ); 

        if ($validator->passes()) {

            $input = array_except($input,array('confirm_password'));

            $input['department_id'] = implode(',', $input['department_id']);
            $input['slug'] = str_slug($input['library_name']);

            if(isset($input['file_name']) && $input['file_name']!=null)
            {
            $image = $request->file('file_name');
			
             $input['document_files'] = $image;

            $input['file_name'] = time().'.'.$image->getClientOriginalName();
            $destinationPath = public_path('/aboutus');
            $image->move($destinationPath, $input['file_name']);
            $input['logo'] = $input['file_name'];
            
            }

             if($request->hasFile('image')){
				 
				 request()->validate([

            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',

        ]);
				 
                $image = ImageUpload::upload('/public/aboutus',$request->file('image'));
                $fileExist = Aboutus::where('image',$image)->first();
                if (!is_null($fileExist)) {
                    return redirect()->back()->withErrors("Profile Image Allready Exist");
                }else{
                    $input['image'] = $image;
                
            }
        }


            



            $aboutus = $this->aboutus->addAboutus($input);
            
            $uinput = [];
            $uinput['aboutus_id'] = $aboutus->id;
            $uinput['name'] = $input['library_name'];
            $uinput['email'] = $input['email'];
            $uinput['password'] = Hash::make($input['password']);
            $uinput['type'] = 2;
            $this->user->addUser($uinput);

            LogActivity::addToLog('About Library created successfully.');

            ////notificationMsg('success',$this->crudMessage('add','Aboutus'));
            
            return redirect(route('admin.aboutus.index'));
        }
        
        return redirect()->back()->withErrors($validator)->withInput();
    }

    public function show($id)
    {
        $aboutus = $this->aboutus->findAboutus($id);

        $departmentList = $this->department->getDepartmentList();

        return view('admin.aboutus.show',compact('aboutus','departmentList'));
    }

    public function edit($id)
    {
        $aboutus = $this->aboutus->findAboutus($id);

        $department = explode(',', $aboutus->department_id);

        $departmentList = $this->department->getDepartmentList();

        return view('admin.aboutus.edit',compact('aboutus','departmentList','department'));
    }

    public function update($id, Request $request)
    {
		 	
        $input = $request->all();
        $aboutus = $this->aboutus->findAboutus($id);
        $user = User::where('library_id',$id)->first();
       

        $validator = Validator::make($input, [
           'library_name'=>'required',
            'department_id'=>'required',
            'sort_order'=>'required',
            'file_name'=>'mimes:jpeg,jpg,png,svg|max:10000',
            // 'city_village'=>'required',
            // 'taluka'=>'required',
            // 'district'=>'required',
            'state'=>'required',
            'country'=>'required',
            // 'phone_number'=>'required',
            // 'mobile_number'=>'required',
            'email'=>'required|email|unique:users,email,'.$aboutus->id,
            'password'=>'same:confirm_password|max:15|min:10|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/',
            // 'website_url'=>'required',
            // 'principal_name'=>'required',
            // 'about_library'=>'required',
            // 'address'=>'required',
        ],
        [
            'department_id.required' => 'The department field is required. '
        ]
        ); 


        if ($validator->passes()) {


            $input['department_id'] = implode(',', $input['department_id']);
            $input['slug'] = str_slug($input['library_name']);

            if(isset($input['file_name']) && $input['file_name']!=null)
            {
            $image = $request->file('file_name');
             $input['document_files'] = $image;

            $input['file_name'] = time().'.'.$image->getClientOriginalName();
            $destinationPath = public_path('/aboutus');
            $image->move($destinationPath, $input['file_name']);
            $input['logo'] = $input['file_name'];
            
            }
            else
            {
                 $input['logo'] = $aboutus->logo;
            }

                if($request->hasFile('image')){
                $image = ImageUpload::upload('/public/aboutus',$request->file('image'));
                $fileExist = Aboutus::where('image',$image)->first();
                if (!is_null($fileExist)) {
                    return redirect()->back()->withErrors("Profile Image Allready Exist");
                }else{
                    $input['image'] = $image;
                }
                
            }
			
			
            $this->aboutus->updateAboutus($id, $input);
			
            $uinput = [];
            $uinput['aboutus_id'] = $id;
            $uinput['name'] = $input['library_name'];
            $uinput['email'] = $input['email'];
            $uinput['type'] = 2;
 
			
            $uinput = array_except($input,array('confirm_password'));
            if(!empty($input['password'])){
                $uinput['password'] = Hash::make($input['password']);
            }else{
                $uinput = array_except($input,array('password'));    
            }

			
          

            LogActivity::addToLog('Aboutus Library updated successfully.');

            ////notificationMsg('success',$this->crudMessage('update','Aboutus'));
            
            return redirect(route('admin.aboutus.index'));
        }

        return redirect()->back()->withErrors($validator)->withInput();
    }

    public function destroy(Request $request)
    {
        $aboutus = $this->aboutus->deleteAboutus($request->id);

        LogActivity::addToLog('About Library deleted successfully.');

        ////notificationMsg('success',$this->crudMessage('delete','Aboutus'));
        
        return redirect()->back();
    }

    public function aboutAboutusEdit()
    {
        $aboutus = $this->aboutus->findAboutus(auth()->user()->aboutus_id);
        
        return view('admin.aboutus.about', compact('aboutus'));        
    }


     public function deanMessageEdit()
    {
        $aboutus = $this->aboutus->findAboutus(auth()->user()->aboutus_id);
        
        return view('admin.deanmessage.about', compact('aboutus'));        
    }

    public function aboutAboutusUpdate($id, Request $request)
    {
        $input = $request->all();
        $aboutusLibrary=[];
        $aboutusLibrary['about_library']= $input['about_library']; 
        $aboutusLibrary['sort_description']= $input['sort_description'];
        $aboutusLibrary['page_link'] = $input['page_link'];

        $this->aboutus->updateAboutus($id, $aboutusLibrary);

        if($request->hasFile('image')){
            $cinput = [];
            $cinput['image'] = ImageUpload::upload('/upload/user',$request->file('image'));

            $this->user->updateAboutus($id, $cinput);
        }

        LogActivity::addToLog('Aboutus Library updated successfully.');

        ////notificationMsg('success',$this->crudMessage('update','Aboutus'));
        
        return redirect(route('admin.about.aboutus'));

    }

     public function deanMessageUpdate($id, Request $request)
    {
        $input = $request->all();
        $aboutusLibrary=[];
        $aboutusLibrary['about_dean']= $input['about_dean']; 
          if($request->hasFile('dean_image')){
           
            $aboutusLibrary['dean_image'] = ImageUpload::upload('/upload/user',$request->file('dean_image'));
        }

        $this->aboutus->updateAboutus($id, $aboutusLibrary);

        

      

        LogActivity::addToLog('Dean Message updated successfully.');

        ////notificationMsg('success',$this->crudMessage('update','Dean Message'));
        
        return redirect(route('admin.about.dean'));

    }

    public function loginAs($id)
    {   
        $aboutusId = User::where('aboutus_id',$id)->first();
        auth()->logout();
        Session::flush();
        Auth::loginUsingId($id);
        return redirect()->route('admin.dashboard');
    }

     public function userDisible(Request $request)
    {
        $input= $request->all();
        $data = Aboutus::where('id',$input['id'])->first();
        $data->update(['is_disable' => 1]);
        LogActivity::addToLog('Aboutus Library Disable successfully.');

        User::where('library_id',$data->id)->update(['is_disable' => 1]);
        ////notificationMsg('success',$this->crudMessage('delete','Aboutus Library User Disable'));
        return response('success');
    }

    public function userEnable($id)
    {

        $data = Aboutus::where('id',$id)->first();
        $data->update(['is_disable' => 0]);
        LogActivity::addToLog('Aboutus Library Enable successfully.');

        User::where('library_id',$data->id)->update(['is_disable' => 0]);
        ////notificationMsg('success','Aboutus Library User Enable');
        return redirect()->back();
    }
}
