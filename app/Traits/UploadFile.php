<?php

namespace App\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image;


trait UploadFile
{
    public function uploadImage(UploadedFile $file, $folder, $width = 200, $height = 200, $get_full_path = false)
    {
        $path = $this->checkFolderIsExists($folder);
        $name = $file->hashName();
        $file->move($path, $name);
        return $get_full_path ? $this->getPath($folder, $name) : $name;
    }

    /**
     * creatOurFolderPath , it's function just to create custom global folder like our need
     *
     * @param  string $folder
     * @return string
    */
    protected function checkFolderIsExists($folder)
    {
        $path =  public_path($this->getPath($folder));

        if (!File::exists($path))
            File::makeDirectory($path, 0777, true);

        return $path;
    }

    protected function getPath($folder, $file_name = '')
    {
        return "uploads/$folder/$file_name";
    }
}
