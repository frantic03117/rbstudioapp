<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $title = "List of gallery";
        $query = Gallery::query();
        if ($request->has('category')) {
            $query->where('category', $request->input('category'));
        }
        $items = $query->orderBy('id', 'DESC')->get();
        $res = compact('items', 'title');
        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'message' => $title,
                'data' => $items,
                'success' => 1,
            ]);
        }
        return view('admin.gallery.index', $res);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([

            'category' => 'nullable|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Accepts only image files with max size 2MB
        ]);

        // Handle file upload with custom name
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension(); // Get the file extension
            $filename = 'gallery_' . date('Ymd_His') . '.' . $extension; // Custom name: gallery_YYYYMMDD_HHMMSS.ext
            $file->move(public_path('gallery/'), $filename);
        } else {
            return response()->json([
                'message' => 'Image file is required.',
                'success' => false,
            ], 400);
        }

        // Save gallery data in the database
        $gallery = new Gallery();

        $gallery->category = $validated['category'] ?? null;
        $gallery->image =  'public/gallery/' . $filename;
        $gallery->save();

        // API JSON response
        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'message' => 'Gallery image saved successfully.',
                'data' => $gallery,
                'success' => 1,
            ]);
        }

        // Redirect back with success message for web requests
        return redirect()->route('gallery.index')->with('success', 'Gallery image saved successfully.');
    }



    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Gallery  $gallery
     * @return \Illuminate\Http\Response
     */
    public function show(Gallery $gallery)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Gallery  $gallery
     * @return \Illuminate\Http\Response
     */
    public function edit(Gallery $gallery)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Gallery  $gallery
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Gallery $gallery)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Gallery  $gallery
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Gallery::where('id', $id)->delete();
        return redirect()->back();
    }
}
