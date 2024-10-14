<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class XLSXFileUploadProcessTest extends TestCase
{
    public function test_xlsx_file_upload_process_test(): void
    {
        $file = fopen(storage_path('test_files/test_file.xlsx'), "r") or die("Unable to open file!");
        $fileContent = fread($file, filesize(storage_path('/test_files/test_file.xlsx')));
        fclose($file);

        $file = UploadedFile::fake()->createWithContent('test_file.xlsx', $fileContent);

        $response = $this->postJson('/upload-file', [
            'file' => $file
        ]);

        $response->assertStatus(200);
    }
}
