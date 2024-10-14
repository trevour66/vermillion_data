<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use Tests\TestCase;

class FileDownloadTest extends TestCase
{

    public function test_file_download_test(): void
    {
        $file = fopen(storage_path('test_files/test_file.csv'), "r") or die("Unable to open file!");
        $fileContent = fread($file, filesize(storage_path('/test_files/test_file.csv')));
        fclose($file);

        $seed = uniqid('', true);
        $seed = hash('md2', $seed);

        $file_path = "test_" . $seed . "_file.csv"; // storage_path('app/public/test_file.csv');

        Storage::put("public/" . $file_path, $fileContent);

        $response = $this->get('/download-file?file_path=' . $file_path);

        $response->assertStatus(200);
    }
}
