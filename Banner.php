<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use DB;

class Banner extends Model
{
    use SoftDeletes, Cachable;
     protected $table = 'banner';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
         'title', 'slug','type','page_img',
		
    ];

    public function menus()
    {
        return $this->belongsTo('App\Menu','menu_id');
    }

    public function addBanner($input)
    {
        return static::create(array_only($input,$this->fillable));
    }

    public function findBanner($id)
    {
        return static::where('id',$id)->first();
    }

    public function getbannerwithnews($menuId)
    {
    return  DB::table('banner')
    ->join('news_papers', 'news_papers.id', '=', 'banner.news_paper_id')
    ->where('banner.menu_id', '=', $menuId)
    ->orderBy('banner.id', 'DESC')
     ->whereNull('banner.deleted_at')
    ->get();

    }

   
 
 
 

     public function getbannerwithmenudata($menuId)
    {
        return static::where('menu_id',$menuId)->get();
    }


    

    // public function getcmswithnews($menuid)
    // {
    //         return static::with(['newspaper'])->where('menu_id',$menuid)->get();
            


    // }
    // public function newspaper(){

    //     return $this->hasMany('App\NewsPaper','id');

    // }



    public function deleteBanner($id)
    {
        return static::where('id',$id)->delete();
    }

    public function deleteBannerUsingMenuId($menuId)
    {
        return static::where('menu_id',$menuId)->delete();
    }

    public function updateBanner($id, $input)
    {
        return static::where('id',$id)->update(array_only($input,$this->fillable));
    }

    public function updateBannerUsingMenuId($menuId, $input)
    {
        return static::where('menu_id',$menuId)->update(array_only($input,$this->fillable));
    }

    public function updateBannerGallery($id, $galleryArray)
    {
        return static::where('id',$id)->update(['gallery'=>$galleryArray]);
    }

    public function getBannerWithMenuId($menu_id,$slug)
    {
        $createdAt = \DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')");

        $data = static::where('menu_id',$menu_id);

        if ($slug == 'Tenders') {
            $data = $data->where('start_date', '>=', date('Y-m-d'))
                        ->where('end_date', '<=', date('Y-m-d'));
        }

        return $data->first();
    }

    public function getLatestBannerUsingType($type)
    {
        return static::where('type',$type)
                    ->orderBy('id','DESC')
                    ->take(3)
                    ->get();
    }



    public function getBannerUsingType($type)
    {
        $data = static::where('type',$type);
        if ($type == 2) {
            $data = $data->orderBy('gallery_date','DESC');
        }elseif ($type == 3) {
         $data = $data->orderBy('gallery_date','DESC');
        }
        return $data->get();
    }

    public function getBannerUsingSlug($slug)
    {
        return static::where('slug',$slug)->first();
    }

    public function getBannerBySearch($input)
    {
        $data = static::select('banner.*')->whereNull('deleted_at');
        if(!empty($input['search'])){
            $data = $data->where(function ($query) use ($input){
                    $query->orWhere('title','like', "%{$input['search']}%");
                    $query->orWhere('description','like', "%{$input['search']}%");

            })->get();
        }else{
            $data = '';
        }
        return $data;
    }

    public function getBannerUsingMenuId($menu_id)
    {
        return static::where('menu_id',$menu_id)->first();
    }

    public function getBannerWithSlugId($id,$slug)
    {
        return static::where('id','!=',$id)
                    ->where('slug',$slug)
                    ->first();
    }
}
