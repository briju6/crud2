<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class Aboutus extends Model
{
    
    protected $table = 'aboutus';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
                'library_code','library_name','slug','department_id','city_village','taluka','district','state','country','phone_number','mobile_number','email','website_url','principal_name','about_library','sort_description','address','sort_order','is_disable','logo','image','about_dean','dean_image','page_link'
    ];

    /*kaushik Add Faculty */
	 
    public function department()
    {
        return $this->hasOne('App\Department','id');
    }

    public function addAboutus($input)
    {
        return static::create(array_only($input,$this->fillable));
    }
    public function findAboutus($id)
    {
        return static::where('id',$id)->first();
    }
	
	 public function findLibraryMasterCms($id)
    {
        return static::where('id',$id)->first();
    }
	
    public function updateAboutus($id, $input)
    {  
        return static::where('id',$id)->update(array_only($input,$this->fillable));
    }
    public function deleteAboutus($id)
    {
        return static::where('id',$id)->delete();
    }
    public function getAboutus($id)
    {
        return static::where('college_id',$id)->orderBy('sort_order', 'ASC')->get();
    }

     public function getAboutusFront()
    {
        return static::orderBy('sort_order')->where('is_disable',0)->get();
    }

    public function getAboutusUsingSlug($slug)
    {
        return static::where('slug',$slug)->first();
    }
	
	public function getLibraryMasterCmsUsingSlug($slug)
    {
        return static::where('slug',$slug)->first();
    }
	

    public function getAboutusUsingDepartmentId($departmentId)
    {
        return \DB::table("aboutus")
            ->select("aboutus.*")
            ->whereNull('deleted_at')
            ->whereRaw("find_in_set('".$departmentId."',aboutus.department_id)")
            ->orderBy('sort_order')
            ->get();
    }

    public function getAboutusBySearch($input)
    {
        $data = static::select('aboutus.*');

        if(!empty($input['search'])){
            $data = $data->where(function ($query) use ($input){
                    $query->orWhere('library_name','like', "%{$input['search']}%");
                    $query->orWhere('about_library','like', "%{$input['search']}%");
            })->get();
        }else{
            $data = '';
        }
        
        return $data;
    }

    /*kaushik Faculty List select School */
    public function getAboutusList()
    {
        return static::pluck('aboutus_name','id')->all();
    }

}


?>
