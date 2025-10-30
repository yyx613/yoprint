<?php

namespace App\Listeners;

use App\Events\FileUploaded;
use App\Models\Upload;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessUploadedFile implements ShouldQueue
{
    public $tries = 3;
    public $backoff = 5;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(FileUploaded $event): void
    {
        $file = $event->filePath;
        $upload_id = $event->uploadId;

        (new Upload)->processFile($file, $upload_id);
    }

    public function failed(FileUploaded $event, \Throwable $exception): void
    {
        DB::table('upload')->where('id', $event->uploadId)->update([
            'status' => 'failed',
        ]);
    }
}
