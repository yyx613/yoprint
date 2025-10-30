<?php

namespace App\Models;

use App\Events\FileUploaded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Upload extends Model
{
    use HasFactory;

    public function processFile($filePath, $upload_id)
    {
        try {
            Log::info('Processing uploaded file: '.$filePath.' with upload ID: '.$upload_id);

            DB::beginTransaction();

            $headers = [];

            // Read file content
            $content = $this->readCSV(storage_path('app/'.$filePath));
            for ($i = 0; $i < count($content); $i++) {
                if ($i == 0) {
                    $headers = array_map(function ($value) {
                        return str_replace('#', '', strtolower($value));
                    }, $content[$i]);

                    continue;
                }
                // Insert data into database
                $batch_data = [];
                for ($j = 0; $j < count($headers); $j++) {
                    $batch_data[$headers[$j]] = $content[$i][$j] == null ? null : $clean = mb_convert_encoding($content[$i][$j], 'UTF-8', 'UTF-8');
                }

                if (! isset($batch_data['unique_key'])) {
                    throw new \Exception('Unique key missing in the uploaded file data.');
                }
                DB::table('upload_data')->updateOrInsert(
                    ['unique_key' => $batch_data['unique_key']],
                    $batch_data
                );
                // Update upload status
                if ($i == count($content) - 1) {
                    DB::table('upload')->where('id', $upload_id)->update([
                        'status' => 'completed',
                    ]);
                }
            }

            Log::info('File processed successfully: '.$filePath.' with upload ID: '.$upload_id);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            Log::error('Error processing file: '.$filePath.' - '.$th->getMessage());
        }
    }

    public function readCSV($csvFile, $delimiter = ',')
    {
        $file_handle = fopen($csvFile, 'r');
        $data = [];
        while (($row = fgetcsv($file_handle, null, $delimiter)) !== false) {
            $data[] = $row;
        }
        fclose($file_handle);

        return $data;
    }
}
