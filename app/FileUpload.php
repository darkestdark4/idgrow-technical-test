<?php

namespace App;

use Illuminate\Support\Facades\Storage;

trait FileUpload
{
    public function handleFile($rootFolder, $type, $newFile, $oldNameFile = null)
    {
        $imageFile = null;

        if ($type == 'new') {
            // add file
            if ($newFile) {
                $image = $newFile;
                $imageUploadedPath = $image->store($rootFolder, 'public');
                $imageFile = basename($imageUploadedPath);
            }
        } else if ($type == 'update') {
            if ($newFile) {
                // delete the old file first
                $this->handleFile($rootFolder, 'delete', null, $oldNameFile);

                // add the new file then
                $imageFile = $this->handleFile($rootFolder, 'new', $newFile);
            }
        } else if ($type == 'delete') {
            // delete file
            if ($oldNameFile) {
                Storage::disk('public')->delete($rootFolder . '/' . $oldNameFile);
            }
        }

        return $imageFile;
    }
}
