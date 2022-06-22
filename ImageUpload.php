<?php 
namespace App;

use DB;
use File;
use Image;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class ImageUpload
{
    use Cachable;
    
    public static function removeFile($path)
    {
        if(File::exists(public_path($path))){
            File::delete(public_path($path));
        }
    }
    public static function removeDir($path)
    {
        if(File::isDirectory(public_path($path))){
            File::deleteDirectory(public_path($path));
        }
    }

    public static function upload($path, $image)
    {
        $imageName = $image->getClientOriginalName();
         $filename = pathinfo($image, PATHINFO_FILENAME);
         $filename = str_replace(' ', '-', $filename);
         $extension = $image->extension();
         $imageName = $filename.'_'.str_random(3).time().'.'.$extension;
        
        $image->move(base_path($path),$imageName);
        
        return $imageName;
    }

    public static function uploadProfileImage($path, $image)
    {
        $imageName = $image->getClientOriginalName();
        $filename = pathinfo($image, PATHINFO_FILENAME);
        $filename = str_replace(' ', '-', $filename);
        $extension = $image->extension();
        $imageName = $filename.'_'.str_random(3).time().'.'.$extension;
        
        $image->move(base_path($path),$imageName);
        
        return $imageName;
    }

    public static function uploadPdf($path, $image)
    {
        $file = $image->getClientOriginalName();
        $filename = pathinfo($file, PATHINFO_FILENAME);
        $filename = str_replace(' ', '-', $filename);
        $extension = $image->extension();
        $imageName = $filename.'_'.str_random(3).time().'.'.$extension;
        $image->move(base_path($path),$file);
        
        return $file;
    }

    public static function uploadThumbnail($orgPath, $path, $image, $width, $height)
    {
        return Image::make(public_path($orgPath).$image)->resize($width, $height)->save(public_path($path).$image);
    }

    public static function uploadReduce($path, $image)
    {
        $imageName = time().'.'.$image->getClientOriginalExtension();
        $ext = $image->getClientOriginalExtension();

        if($ext == 'png'){
            $read_from_path = $image->getPathName();
            $save_to_path = public_path($path.'/'.$imageName);

            $compressed_png_content = ImageUpload::compress_png($read_from_path);
            file_put_contents($save_to_path, $compressed_png_content);
            
        }else{
            $image->move(public_path($path),$imageName);
        }

        Image::make(public_path($path).$imageName)->resize(700, null, function ($constraint) {
                $constraint->aspectRatio();
            })->save();

        return $imageName;
    }


}
