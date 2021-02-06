<?php

use Illuminate\Support\Facades\Storage;

/**
 * [addImages description]
 * @param [string] $type  Name of the model in kebab case. Make it singular (e.g. product-variant, customer-detail)
 * @param [object/array] $r     [The Request object. It contains the images]
 * @param [Model instance] $model [Model!!!]
 */
function addImages ($type, $r, $model) {
    $existingImagesCount = $model->images($type)->count();
    foreach ($r->file as $key => $image) {
        $proceed = true;

        if ($proceed) {
            $uploadedImage = uploadImageFile($image);
            $imageData = [
                'title' => (isset($r->file_title)) ? $r->file_title[$key] : null,
                'alt' => (isset($r->file_alt)) ? $r->file_alt[$key] : null,
                'sequence' => (isset($r->file_sequence)) ? $r->file_sequence[$key] : $existingImagesCount + 1,
                'path' => $uploadedImage->path,
                'path_resized' => $uploadedImage->path_resized,
                'category' => (isset($r->file_category)) ? $r->file_category[$key] : null,
                'type' => $type
            ];
            $model->uploadImage($imageData);
        }
    }
}

function updateImages ($type, $r, $model) {
    $existingImagesCount = $model->images($type)->count();

    if ($r->file_id) {
        foreach ($r->file_id as $key => $image_id) {
            if ($image_id == 0) { # if new image, upload this
                $image = $r->file[$key];
                $uploadedImage = uploadImageFile($image);
                $imageData = [
                    'title' => (isset($r->file_title)) ? $r->file_title[$key] : null,
                    'alt' => (isset($r->file_alt)) ? $r->file_alt[$key] : null,
                    'sequence' => (isset($r->file_sequence)) ? $r->file_sequence[$key] : $existingImagesCount + 1,
                    'path' => $uploadedImage->path,
                    'path_resized' => $uploadedImage->path_resized,
                    'category' => (isset($r->file_category)) ? $r->file_category[$key] : null,
                    'type' => $type
                ];
                $model->uploadImage($imageData);
            } else { # if old image
                if (isset($r->file[$key])) { # if a new image is selected
                    # update the old image data
                    $existingImage = App\Models\Image::where('id', $image_id)->first();
                    $uploadedImage = uploadImageFile($r->file[$key], $existingImage->path, $existingImage->path_resized);
                    $existingImage->update([
                        'title' => (isset($r->file_title)) ? $r->file_title[$key] : null,
                        'alt' => (isset($r->file_alt)) ? $r->file_alt[$key] : null,
                        'sequence' => (isset($r->file_sequence)) ? $r->file_sequence[$key] : $existingImagesCount + 1,
                        'path' => $uploadedImage->path,
                        'path_resized' => $uploadedImage->path_resized
                    ]);
                } else { # if no new image is selected
                    $existingImage = App\Models\Image::where('id', $image_id)->first();
                    $existingImage->update([
                        'title' => (isset($r->file_title)) ? $r->file_title[$key] : null,
                        'alt' => (isset($r->file_alt)) ? $r->file_alt[$key] : null,
                        'sequence' => (isset($r->file_sequence)) ? $r->file_sequence[$key] : $existingImagesCount + 1,
                    ]);
                }
            }
        }
    }
}

function uploadImageFile ($file, $oldFilePath = null, $oldFilePathResized = null) {
    $disk = 'public';

    # delete the old file if it exists
    if ($oldFilePath != null) {
        Storage::disk($disk)->delete("uploads/$oldFilePath");
    }
    if ($oldFilePathResized != null) {
        Storage::disk($disk)->delete("uploads/$oldFilePathResized");
    }

    $filenameWithExtension = $file->getClientOriginalName();
    $extension = $file->getClientOriginalExtension();
    $filename = pathinfo($filenameWithExtension, PATHINFO_FILENAME);
    $timeNow = time();
    $filenameToStore = $filename . '_' . $timeNow . '.' . $extension;

    # if the file is svg or gif, directly upload it and stop the function immediately by returning the path names
    if ($extension == 'svg' || $extension == 'gif') {
        Storage::disk($disk)->put("uploads/$filenameToStore", file_get_contents($file), [
            'visibility' => 'public',
            'ContentType' => ($extension == 'svg') ? 'image/svg+xml' : 'image/gif'
        ]);

        $toReturn = (object) [
            'path' => $filenameToStore,
            'path_resized' => $filenameToStore,
        ];

        return $toReturn;
    }

    $unresizedFile = Image::make($file->getRealPath())->interlace()->encode($extension, 80)->orientate();
    Storage::disk($disk)->put("uploads/$filenameToStore", $unresizedFile->getEncoded(), 'public');

    # upload resized file
    $resizedFile = Image::make($file->getRealPath())->resize(750, 750, function ($c) {
        $c->aspectRatio();
        $c->upsize();
    })->interlace()->encode($extension, 80)
    ->orientate();

    $filenameToStore_resized = $filename . '_' . $timeNow . '_thumbnail.' . $extension;
    Storage::disk($disk)->put("uploads/$filenameToStore_resized", $resizedFile->getEncoded(), 'public');

    $toReturn = (object) [
        'path' => $filenameToStore,
        'path_resized' => $filenameToStore_resized,
    ];

    return $toReturn;
}

function fileIsImage ($file) {
    $result = false;
    if (@is_array(getimagesize($file))){
        $result = true;
    }

    return $result;
}