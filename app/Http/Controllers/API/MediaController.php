<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function index (Request $r) {
        $medias = Media::where('user_id', $r->user()->id)
        ->orderByDesc('created_at')
        ->get();

        return response([
            'medias' => $medias
        ]);
    }

    public function store (Request $r) {
    	$validator = \Validator::make($r->all(), [
            'type' => 'required',
            'file' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'errors' => $validator->errors()->all()
            ], 400);
        }

        # validate temporary id
        $tempIdExists = Media::where('temporary_id')->exists();
        if ($tempIdExists) {
            return response([
                'errors' => 'Invalid temporary id'
            ], 400);
        }

        $uploaded = uploadFile($r->file);

        $user = $r->user();

        $media = Media::create([
        	'user_id' => $user->id,
        	'type' => $r->type,
        	'path' => $uploaded->path,
        	'path_resized' => $uploaded->path_resized,
            'temporary_id' => $r->temporary_id
        ]);

        return response([
        	'media' => $media
        ]);
    }

    public function show (Media $media) {
        return response([
            'media' => $media
        ]);
    }

    public function destroy (Media $media) {
        $media->delete();

        return response([
            'message' => 'File has been deleted'
        ]);
    }
}
