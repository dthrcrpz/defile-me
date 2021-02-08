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
            'file' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'errors' => $validator->errors()->all()
            ], 400);
        }

        $uploaded = uploadFile($r->file);
        $user = $r->user();
        $type = (fileIsImage($r->file)) ? 'image' : 'video';

        $media = Media::create([
        	'user_id' => $user->id,
        	'type' => $type,
        	'path' => $uploaded->path,
        	'path_resized' => $uploaded->path_resized,
            'temporary_id' => str_random(15)
        ]);

        return response([
        	'media' => $media
        ]);
    }

    public function show (Media $media, Request $r) {
        $user = $r->user();
        if ($media->user_id != $user->id) {
            return response([
                'errors' => [
                    'You do not have permissions to access this media'
                ]
            ], 403);
        }

        return response([
            'media' => $media
        ]);
    }

    public function destroy (Media $media, Request $r) {
        $user = $r->user();
        if ($media->user_id != $user->id) {
            return response([
                'errors' => [
                    'You do not have permissions to access this media'
                ]
            ], 403);
        }

        $media->delete();

        return response([
            'message' => 'File has been deleted'
        ]);
    }
}
