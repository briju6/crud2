<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AdminController;
use Yajra\Datatables\Datatables;
use App\ImageUpload;
use App\Awards;
use Validator;
use App\Helpers\LogActivity;
use App\TypeMaster;
use App\MenuTypeMaster;
use App\DocumentFile;
use DB;

class AwardsController extends AdminController
{
    public function __construct()
     {
         
        parent::__construct();
        $this->awards = new Awards;	
        $this->typeMaster = new TypeMaster;
        $this->menuTypeMaster = new menuTypeMaster;
        $this->documentFile = new DocumentFile;
    }

    public function index(Request $request)
    {
    	
        if ($request->ajax()) {


           
            $data = Awards::latest()->get();
            
            return Datatables::of($data)
                    ->addIndexColumn()
                    
                     
                     ->addColumn('start_date_latest_news', function($row){
                        return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $row->start_date_latest_news)->format('d/m/Y H:i:s');
                    })
                    ->addColumn('end_date_latest_news', function($row){
                        return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $row->end_date_latest_news)->format('d/m/Y H:i:s');
                    })
                    /*->addColumn('type_master', function($row){
                        $typeMasterArray = [];
                        foreach ($row->type_masters as $key => $value) {
                            $typeMasterArray[] = '<span class="label label-warning">'.$value->name.'</span>';
                        }
                        return implode(' ', $typeMasterArray);
                    })*/
                    

                    ->addColumn('page_type', function($row){
                        if ($row->page_type == 0) {
                            $page_type = '<span class="label label-warning">CMS</span>';
                        }elseif ($row->page_type == 1) {
                            $page_type = '<span class="label label-info">Link</span>';
                        }elseif ($row->page_type == 2) {
                            $page_type = '<span class="label label-primary">Dynamic List</span>';
                        }
                        return $page_type;
                    })
                    
                    ->addColumn('status', function($row){
                        if ($row->status == 0) {
                            $status = '<label class="label label-success">Enable</label>';
                        }elseif ($row->status == 1) {
                            $status = '<label class="label label-danger">Disable</label>';
							
                        }
                        return $status;
						
                    })
                    
                    ->addColumn('action', function($row){
                           $btn = '<a href="'.route('admin.awards.show',[$row->id]).'" class="btn btn-info btn-xs btn-flat" data-toggle="tooltip" data-placement="top" data-original-title="Show"><i class="fa fa-eye"></i></a>';

                           $btn = $btn.' <a href="'. route('admin.awards.edit',[$row->id]) .'" class="btn btn-primary btn-xs btn-flat" data-toggle="tooltip" data-placement="top" data-original-title="Edit"><i class="fa fa-pencil"></i></a>';

                           $btn = $btn.' <button class="btn btn-danger btn-flat btn-xs remove-crud" data-id="'. $row->id .'" data-action="'. route('admin.awards.destroy',$row->id) .'"  data-toggle="tooltip" data-placement="top" data-original-title="Delete"> <i class="fa fa-trash"></i></button>';

                           return $btn;
                    })
                    ->rawColumns(['action','status','page_type','start_date_latest_news','end_date_latest_news'])
                    ->make(true);
        }

        return view('admin.awards.index');
    }

    public function create()
    {
        
       
        return view('admin.awards.create');
    }

    public function store(Request $request)
   { 
        $input = $request->all();
       
       
        $validator = Validator::make($input, [
            'type_master'=>'required',
            'name'=>'required',
            'start_date_latest_news'=>'required',
            'end_date_latest_news'=>'required|date_format:d/m/Y H:i:s|after:start_date_latest_news',
        ],
        [
            'start_date_latest_news.required' => 'The start date field is required.',
            'end_date_latest_news.required' => 'The end date field is required.',
            'end_date_latest_news.after' => 'The end date must be a date after start date.',
            
        ]); 
        if ($validator->passes()) {
            $input['slug'] = str_slug($input['name']);
			
            $input['status'] = isset($input['status']) ? 0 : 1 ;
			
    
            if (isset($input['start_date_latest_news'])) {
                $input['start_date_latest_news'] = \Carbon\Carbon::createFromFormat('d/m/Y H:i:s', $input['start_date_latest_news'])->format('Y-m-d H:i:s');
            }

            if (isset($input['end_date_latest_news'])) {
                $input['end_date_latest_news'] = \Carbon\Carbon::createFromFormat('d/m/Y H:i:s', $input['end_date_latest_news'])->format('Y-m-d H:i:s');
				
				DB::delete('delete from awards where id = ?',[$request->id]);
            }



            if(isset($input['file_name']) && $input['file_name']!=null)
            {
            $image = $request->file('file_name');
             $input['document_files'] = $image;
			 
			

            $input['file_name'] = time().'.'.$image->getClientOriginalName();
            $destinationPath = public_path('/awards');
            $image->move($destinationPath, $input['file_name']);
            $input['document_files'] = $input['file_name'];
            
            }
			





            //$awards = $this->awards->getNewsWithSlug('Latest-News');
           

            //$input['parent_id'] = $news->id;
           

            $input['page_type'] = 1;

            if (isset($input['fileArray'])) {
                $input['document_files'] = implode(',', $input['fileArray']);
            }
            
            $name = $request->input('name');
			$slug = str_slug($input['slug']);
			  $input['status'] = isset($input['status']) ? 0 : 1 ;
		    $status=$input['status'];
			$start_date_latest_news = $input['start_date_latest_news'];
			 $end_date_latest_news = $input['end_date_latest_news'];
             $file_name = $input['file_name'];
		     $description = $input['description'];
		   
		   
			DB::insert('insert into awards (name, slug, status, start_date_latest_news, end_date_latest_news, file_name, description) values ( ?, ?, ?, ?, ?, ?, ?)', [$input['name'],$input['slug'],$input['status'],$input['start_date_latest_news'],$input['end_date_latest_news'],$input['file_name'],$input['description']]);
			
			
			
            
            LogActivity::addToLog('Latest News Update created successfully.');

            /*foreach ($input['type_master'] as $key => $value) {
                
                $mtminput = [];   
                //$mtminput['menu_id'] = $newsData->id;   
                $mtminput['type_master_id'] = $value;
          
                $this->menuTypeMaster->addMenuTypeMaster($mtminput);
            }*/

            ////notificationMsg('success',$this->crudMessage('add','Latest News'));
            
			
			return back()->with('success','Awards created successfully.');	
			
			
            return redirect(route('admin.awards.home'));
        }
        
        return redirect()->back()->withErrors($validator)->withInput();
    }

    public function show($id)
    {
       
        $awards = $this->awards->findAwards($id);
		  
        return view('admin.awards.show',compact('awards'));
    }

    public function edit($id)
    {
        $awards = $this->awards->findAwards($id);   
    
        $documentFileIds = explode(',', $awards->document_files);
             
        $documentFileArray = [];
		
        if(!empty($documentFileIds)){
			
            foreach ($documentFileIds as $key => $value) {
				
                $documentFileData = $this->documentFile->findFile($value);
                

                if (!empty($documentFileData)) {
					
                    $documentFileArray[] = ['name'=>asset('/upload/file/'.$documentFileData->files_name), 'size'=>'10'];
                   
                }
            }
            // $documentFileArray = json_encode($documentFileArray);
        }

        $typeMasterList = $this->typeMaster->getTypeMasterList();
           
        $menuTypeMaster = $this->menuTypeMaster->getMenuTypeMasterUsingMenuId($id);
      

        $startDate = '';
        if (!is_null($awards->start_date_latest_news)) {
            $startDate = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $awards->start_date_latest_news)->format('d/m/Y H:i:s');
                       
        }

        $endDate = '';
        if (!is_null($awards->end_date_latest_news)) {
            
            $endDate = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $awards->end_date_latest_news)->format('d/m/Y H:i:s');

        }        
        
        return view('admin.awards.edit',compact('awards','typeMasterList','menuTypeMaster','startDate','endDate','documentFileArray'));
    }

    public function update($id, Request $request)
  {
        $input = $request->all();
		
		//print_r($input);exit;
		
		
		
		
        $validator = Validator::make($input, [
            'type_master'=>'required',
            'name'=>'required',
            'start_date_latest_news'=>'required',
            'end_date_latest_news'=>'required|date_format:d/m/Y H:i:s|after:start_date_latest_news',
        ],
        [
            'start_date_latest_news.required' => 'The start date field is required.',
            'end_date_latest_news.required' => 'The end date field is required.',
            'end_date_latest_news.after' => 'The end date must be a date after start date.',

        ]);

        if ($validator->passes()) {
            $input['slug'] = str_slug($input['name']);
			$input['status'] = isset($input['status']) ? 0 : 1 ;
			

            if (isset($input['start_date_latest_news'])) {
                $input['start_date_latest_news'] = \Carbon\Carbon::createFromFormat('d/m/Y H:i:s', $input['start_date_latest_news'])->format('Y-m-d H:i:s');
            }

            if (isset($input['end_date_latest_news'])) {
                $input['end_date_latest_news'] = \Carbon\Carbon::createFromFormat('d/m/Y H:i:s', $input['end_date_latest_news'])->format('Y-m-d H:i:s');
				
				DB::delete('delete from awards where id = ?',[$request->id]);
            }
            
			//print_r($input['end_date_latest_news']);exit;
			
			
			
			 if(isset($input['file_name']) && $input['file_name']!=null)
            {
            $image = $request->file('file_name');
             $input['document_files'] = $image;
			 
			

            $input['file_name'] = time().'.'.$image->getClientOriginalName();
            $destinationPath = public_path('/awards');
            $image->move($destinationPath, $input['file_name']);
            $input['document_files'] = $input['file_name'];
            
            }
			
			
			
			
			
			
            //$news = $this->news->getNewsWithSlug('news');
		
            //$input['parent_id'] = $news->id;
            $input['page_type'] = 1;

            $awards = $this->awards->findAwards($id);
         
		    //print_r($awards);exit;
		 
            $awardsOldIds = explode(',', $awards->document_files);
			 
          //print_r($awardsOldIds);exit;
          
		  
		  
          $this->awards->updateAwards($id, $input);
			
			
		

            LogActivity::addToLog('Latest News updated successfully.');

            $this->menuTypeMaster->deleteTypeMasterUsingMenuId($id);
            /*foreach ($input['type_master'] as $key => $value) {
                $mtminput = [];   
                $mtminput['menu_id'] = $id;
                $mtminput['type_master_id'] = $value;
                $this->menuTypeMaster->addMenuTypeMaster($mtminput);
            }*/

            ////notificationMsg('success',$this->crudMessage('update','Latest News'));
            
			
			return back()->with('success','Awards updated successfully.');
			
			
            return redirect(route('admin.awards.home'));
        }

        return redirect()->back()->withErrors($validator)->withInput();
    }


    public function destroy(Request $request)
       {
		   
        //$news = $this->news->deleteNews($request->id);
        
		
		DB::delete('delete from awards where id = ?',[$request->id]);
		
		
        LogActivity::addToLog('Latest News deleted successfully.');

        ////notificationMsg('success',$this->crudMessage('delete','Latest News'));
        
		
		return back()->with('success','Awards deleted successfully.');
		
        return redirect()->back();
    }


        public function fileStore(Request $request)
    {
        if($request->hasFile('file')){
            $input['files_name'] = ImageUpload::uploadPdf('/upload/file',$request->file('file'));
        }

        $que=DocumentFile::where('name',$input['files_name'])->where('deleted_at',NULL)
                        ->get();

        if($que->count() != 0){
            return response()->json('error',"same File");
        }else{

            $input['name'] = $input['files_name'];
            $input['slug'] = str_slug($input['name']);
            $input['type'] = 1;
            $documentFileData = $this->documentFile->addFile($input);

            return response()->json(['success'=>$documentFileData,'filepath'=>asset('/upload/file/'.$documentFileData->files_name)]);
        }

    }



      public function fileDestroy(Request $request)
    {
        $input = $request->all();

        $path = $input['filePath'];

        $fileName = basename($path); 

        $documentFile = $this->documentFile->findFileUsingName($fileName);
        
        $this->documentFile->deleteFileUsingName($fileName);

        return response()->json(['success'=>'file deleted successfully','id'=>$documentFile->id]);
    }



   
}
