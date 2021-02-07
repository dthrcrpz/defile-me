<?php

use Illuminate\Support\Facades\Storage;

function uploadFile ($file, $oldFilePath = null, $oldFilePathResized = null) {
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

    # if the file is svg or gif, it is not compressable by intervention so directly upload it and stop the function immediately by returning the paths
    $otherAcceptedExtentions = ['svg', 'gif', 'mp4'];
    if (in_array($extension, $otherAcceptedExtentions)) {
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