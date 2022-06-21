<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AdminController;
use Yajra\Datatables\Datatables;
use App\Banner;
use App\Menu;
use App\ImageUpload;
use App\NewsPaper;
use App\DocumentFile;
use Validator;
use DB;
use App\Helpers\LogActivity;
use Illuminate\Support\Facades\File; 


class BannerController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->banner = new Banner;
        $this->menu = new Menu;
        $this->documentFile = new DocumentFile;
        $this->newsPaper = new NewsPaper;
    }

    public function index(Request $request)
    {
		
		
        if ($request->ajax()) {

            $data = Banner::latest()->get();

            return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('menu', function($row){
                            if(!empty($row->menus->name)){
                                return $row->menus->name;
                            }
                    })
                    ->addColumn('type', function($row){
                           if ($row->type == 0) {
                               $type = '<span class="label label-success">Description</span>';
                           }elseif ($row->type == 1) {
                               $type = '<span class="label label-warning">Image</span>';
                           }elseif ($row->type == 2) {
                               $type = '<span class="label label-info">Gallery</span>';
                           }elseif ($row->type == 3) {
                               $type = '<span class="label label-primary">Video</span>';
                           }
                           return $type;
                    })
                    ->addColumn('action', function($row){
                           $btn = '<a href="'.route('admin.banner.show',[$row->id]).'" class="btn btn-info btn-xs btn-flat" data-toggle="tooltip" data-placement="top" data-original-title="Show"><i class="fa fa-eye"></i></a>';

                           $btn = $btn.' <a href="'. route('admin.banner.edit',[$row->id]) .'" class="btn btn-primary btn-xs btn-flat" data-toggle="tooltip" data-placement="top" data-original-title="Edit"><i class="fa fa-pencil"></i></a>';

                           $btn = $btn.' <button class="btn btn-danger btn-flat btn-xs remove-crud" data-id="'. $row->id .'" data-action="'. route('admin.banner.destroy',$row->id) .'"  data-toggle="tooltip" data-placement="top" data-original-title="Delete"> <i class="fa fa-trash"></i></button>';

                           return $btn;
                    })
                    ->rawColumns(['action','menu','type'])
                    ->make(true);
        }

        return view('admin.banner.index');
    }

    public function create()
    {


        $menuList = $this->menu->getMenuListUsingPageType();		
        $newsPaperList = $this->newsPaper->getNewsPaperList();

        return view('admin.banner.create',compact('menuList','newsPaperList'));
    }

    public function store(Request $request)
    {
        $input = $request->all();
      
       
  
        $validator = Validator::make($input, [
            //'menu_id'=>'required',
            'title'=>'required',
            // 'sort_order'=>'required',
            //'description'=>'required_if:type,0',
            'image_document'=>'required_if:type,1',
            // 'gallery_date'=>'required',
            'primary_image'=>'required_if:type,2',
            // 'video_date'=>'required_if:type,3',
            
            // 'video'=>'required_if:video_upload_type,0,type,3',
            // 'video_link'=>'required_if:video_upload_type,1,type,3',
        ],
        [
            //'menu_id.required' => 'The menu field is required.',
           // 'description.required_if' => 'The description field is required.',
            'image_document.required_if' => 'The image field is required.',
            'gallery_date.required' => 'The gallery date field is required.',
            'primary_image.required_if' => 'The primary image field is required.',
            'video_date.required_if' => 'The video date field is required.',
            // 'video.required_if' => 'The video field is required.',
            // 'video_link.required_if' => 'The video link field is required.',
        ]); 

        if ($validator->passes()) {

            $typeArray = ['1'=>'image_document','2'=>'gallery','3'=>'video'];

            foreach ($typeArray as $key => $value) {
                if($key == $input['type']){
                    if (isset($input[$value])) {
                        $input[$value] = $input[$value];
                    }
                }else{
                    $input[$value] = null;
                }
            }

            if (isset($input['gallery_date'])) {
                $input['gallery_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $input['gallery_date'])->format('Y-m-d');
            }

             $input['status'] = isset($input['status']) ? 0 : 1 ;

            if($request->hasFile('image_document')){
                $file = ImageUpload::upload('/upload/banner',$request->file('image_document'));
                $fileExist = Banner::where('image_document',$file)->first();
                if (!is_null($fileExist)) {
                    return redirect()->back()->withErrors("Image Allready Exist");
                }else{
                    $input['image_document'] = $file;
                }
            }


            if (isset($input['start_date'])) {
                $input['start_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $input['start_date'])->format('Y-m-d');
            }

            if (isset($input['end_date'])) {
                $input['end_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $input['end_date'])->format('Y-m-d');
            }

         


            if (isset($input['news_paper_id'])) {
                $input['news_paper_id'] = implode(',', $input['news_paper_id']);
                $input['publish_article_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $input['publish_article_date'])->format('Y-m-d');
            }

             if(isset($input['primary_image']) && $input['primary_image']!=null)
            {
            $image = $request->file('primary_image');
			
            $input['document_files'] = $image;

            $input['primary_image'] = time().'.'.$image->getClientOriginalName();
            $destinationPath = public_path('/banner');
            $image->move($destinationPath, $input['primary_image']);
            $input['document_files'] = $input['primary_image'];
            
            }

            if(isset($input['page_img']) && $input['page_img']!=null)
            {
            $image = $request->file('page_img');
			
            $input['document_files'] = $image;

            $input['page_img'] = time().'.'.$image->getClientOriginalName();
            $destinationPath = public_path('/banner');
            $image->move($destinationPath, $input['page_img']);
            $input['document_files'] = $input['page_img'];
            
            }








           

            $input['slug'] = str_slug($input['title']);

            $checkSlugExistOrNot = $this->banner->getBannerUsingSlug($input['slug']);
            if (!is_null($checkSlugExistOrNot)) {
                $input['slug'] = $input['slug'].'-'.rand(0,999);
            }

            if($request->hasFile('thumbnail_image')){
                $file = ImageUpload::upload('/upload/banner',$request->file('thumbnail_image'));
                $fileExist = Banner::where('thumbnail_image',$file)->first();
                if (!is_null($fileExist)) {
                    return redirect()->back()->withErrors("Thumbnail Image Allready Exist");
                }else{
                    $input['thumbnail_image'] = $file;
                }
            }


            if (isset($input['fileArray'])) {
                $input['document_files'] = implode(',', $input['fileArray']);
            }

            $this->banner->addBanner($input);

            LogActivity::addToLog('Banner created successfully.');

             return back()->with('success','Banner created successfully.');


            ////notificationMsg('success',$this->crudMessage('add','Banner'));
            
            return redirect(route('admin.banner.home'));
        }

        return redirect()->back()->withErrors($validator)->withInput();
    }

    public function show($id)
    
    {
        $banner = $this->banner->findBanner($id);

        $menuList = $this->menu->getMenuListUsingPageType();

       


        return view('admin.banner.show',compact('banner','menuList'));
    }

    public function edit($id)
    {
        $banner = $this->banner->findBanner($id);

        if (!is_null($banner->gallery_date)) {
            $galleryDate = \Carbon\Carbon::createFromFormat('Y-m-d', $banner->gallery_date)->format('d/m/Y');
        }

        if (!is_null($banner->video_date)) {
            $videoDate = \Carbon\Carbon::createFromFormat('Y-m-d', $banner->video_date)->format('d/m/Y');
        }

        $startDate = '';
        if (!is_null($banner->start_date)) {
            $startDate = \Carbon\Carbon::createFromFormat('Y-m-d', $banner->start_date)->format('d/m/Y');
        }

        $endDate = '';
        if (!is_null($banner->end_date)) {
            $endDate = \Carbon\Carbon::createFromFormat('Y-m-d', $banner->end_date)->format('d/m/Y');
        }

        $publishArticleDate = '';
        if (!is_null($banner->publish_article_date)) {
            $publishArticleDate = \Carbon\Carbon::createFromFormat('Y-m-d', $banner->publish_article_date)->format('d/m/Y');
        }

        $menuList = $this->menu->getMenuListUsingPageType();

        $documentFileIds = explode(',', $banner->document_files);

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

        $newsPaperList = $this->newsPaper->getNewsPaperList();

     

        return view('admin.banner.edit',compact('banner','menuList','startDate','endDate','documentFileArray','newsPaperList','publishArticleDate'));
    }

    public function update($id, Request $request)
    {
        $input = $request->all();
        
        $validator = Validator::make($input, [
            //'menu_id'=>'required',
            'title'=>'required',
            // 'sort_order'=>'required',
            //'description'=>'required_if:type,0',
            // 'gallery_date'=>'required',
            // 'video_date'=>'required_if:type,3',
            //'video'=>'max:50000|mimes:mp4,peg,avi,mpg,wmv,flv,mov',
        ],
        [
           // 'menu_id.required' => 'The menu field is required.',
            //'description.required_if' => 'The description field is required.',
            //'gallery_date.required' => 'The gallery date field is required.',
            //'video_date.required_if' => 'The video date field is required.'
        ]); 

        if ($validator->passes()) {
            if (!isset($input['gallery'])) {
                $input['gallery'] = '';
            }

            $typeArray = ['0'=>'description','1'=>'image_document','2'=>'gallery','3'=>'video'];

            foreach ($typeArray as $key => $value) {
                if($key == $input['type']){
                    if (isset($input[$value])) {
                        $input[$value] = $input[$value];
                    }
                }else{
                    $input[$value] = null;
                }
            }

            if (isset($input['gallery_date'])) {
                $input['gallery_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $input['gallery_date'])->format('Y-m-d');
            }

            $input['status'] = isset($input['status']) ? 0 : 1 ;

            if($request->hasFile('image_document')){
                $file = ImageUpload::upload('/upload/banner',$request->file('image_document'));
                $fileExist = Banner::where('image_document',$file)->first();
                if (!is_null($fileExist)) {
                    return redirect()->back()->withErrors("Image Allready Exist");
                }else{
                    $input['image_document'] = $file;
                }
            }

            if (isset($input['video_date'])) {
                $input['video_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $input['video_date'])->format('Y-m-d');
            }

            if (isset($input['start_date'])) {
                $input['start_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $input['start_date'])->format('Y-m-d');
            }

            if (isset($input['end_date'])) {
                $input['end_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $input['end_date'])->format('Y-m-d');
            }
            
           
            
       

            
			
			
			
			
            if(isset($input['page_img']) && $input['page_img']!=null)
            {
				
			
				
            $image = $request->file('page_img');
			
            $input['document_files'] = $image;

            $input['page_img'] = time().'.'.$image->getClientOriginalName();
            $destinationPath = public_path('/banner');
            $image->move($destinationPath, $input['page_img']);
            $input['document_files'] = $input['page_img'];
		
		   
			}




          
            
          

            if (isset($input['news_paper_id'])) {
                $input['news_paper_id'] = implode(',', $input['news_paper_id']);
            }else{
                $input['news_paper_id'] = null;   
            }
            if (isset($input['publish_article_date'])) {
                $input['publish_article_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $input['publish_article_date'])->format('Y-m-d');
            }

            $input['slug'] = str_slug($input['title']);

            $checkSlugExistOrNot = $this->banner->getBannerWithSlugId($id,$input['slug']);
            if (!is_null($checkSlugExistOrNot)) {
                $input['slug'] = str_slug($input['title']).'-'.rand(0,999);
            }else{
                $input['slug'] = str_slug($input['title']);
            }

            $banner = $this->banner->findBanner($id);
            
            $membershipsOldIds = explode(',', $banner->document_files);
            if (!empty($input['fileArray'])) {
                $mergeArray = array_merge($membershipsOldIds,$input['fileArray']);
                $input['document_files'] = implode(',', $mergeArray);
            }else{
                $input['document_files'] = implode(',', $membershipsOldIds);
            }

            $this->banner->updateBanner($id, $input);

            LogActivity::addToLog('banner updated successfully.');

            ////notificationMsg('success',$this->crudMessage('update','banner'));
            
			
			return back()->with('success','Banner updated successfully.');

			
			
			
            return redirect(route('admin.banner.home'));
        }

        return redirect()->back()->withErrors($validator)->withInput();
    }

     




    public function destroy(Request $request)
    {     
	     DB::delete('delete from banner where id = ?',[$request->id]);
		 
        LogActivity::addToLog('banner deleted successfully.');

        ////notificationMsg('success',$this->crudMessage('delete','banner'));
        
		 
		
		
		
		return back()->with('success','Banner deleted successfully.');

		
		
        return redirect()->back();
    }


 



    public function deleteGalleryImage(Request $request)
    {
        $input = $request->all();

        $banner = $this->banner->findMemberships($input['id']);

        $galleryArray = explode(',', $banner->gallery);

        if (in_array($input['image'], $galleryArray)) {
            $galleryArray = array_diff($galleryArray, array($input['image']));

            $galleryArray = implode(',', $galleryArray);

            $this->banner->updateCmsGallery($input['id'],$galleryArray);

            LogActivity::addToLog('banner gallery image deleted successfully.');

            return response()->json(['success'=>'Image deleted successfully.']);
        }
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
