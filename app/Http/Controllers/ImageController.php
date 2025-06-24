<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Image;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ImageResource;

class ImageController extends Controller
{
    public function index(): JsonResponse
    {
        $images = Image::with('imageable')->get();
        return response()->json(ImageResource::collection($images));
    }
}
