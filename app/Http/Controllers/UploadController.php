<?php

namespace App\Http\Controllers;

use App\Events\FileUploaded;
use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UploadController extends Controller
{
    public function index() {
        $histories = DB::table('upload')->orderBy('created_at', 'desc')->get();
        for ($i=0; $i < count($histories); $i++) { 
            $histories[$i]->created_at = date('d M Y, H:i A', strtotime($histories[$i]->created_at));
        }
        return view('welcome', ['histories' => $histories]);
    }

    public function fileUpload(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'file' => 'required|file',
        ]);

        try {
            DB::beginTransaction();

            // Handle the uploaded file
            if ($request->file('file')->isValid()) {
                $path = $request->file('file')->store('uploads');

                // Store uploded file details in DB
                $upload_id = DB::table('upload')->insertGetId([
                    'file_name' => $request->file('file')->getClientOriginalName(),
                    'file_path' => $path,
                    'created_at' => now(),
                ]);
    
                // Trigger file uploaded event to process the file asynchronously
                FileUploaded::dispatch($path, $upload_id);
    
                DB::commit();

                return response()->json([
                    'message' => 'File uploaded successfully',
                    'path' => $path,
                    'upload_id' => $upload_id,
                    'created_at' => date('d M Y, H:i A', strtotime(now())),
                ], 200);
            }

            DB::rollBack();
    
            return response()->json([
                'message' => 'File upload failed',
            ], 400);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);
            return response()->json([
                'message' => 'File upload failed',
            ], 500);
        }
    }

    public function poll(Request $request)
    {
        $uploads = DB::table('upload')->get();

        return response()->json([
            'uploads' => $uploads,
        ], 200);
    }
}
