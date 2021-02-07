<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function index () {

    }

    public function store (Request $r) {
    	$validator = \Validator::make($r->all(), [
            'type' => 'required',
            'file' => 'required'
        ]);

        if ($validator->fails()) {
            return response([
                'errors' => $validator->errors()->all()
            ], 400);
        }

        $uploaded = uploadFile($r->file);

        $user = $r->user();

        $media = Media::create([
        	'user_id' => $user->id,
        	'type' => $r->type,
        	'path' => $uploaded->path,
        	'path_resized' => $uploaded->path_resized
        ]);

        return response([
        	'media' => $media
        ]);
    }
}
