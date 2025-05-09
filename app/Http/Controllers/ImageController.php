<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Jobs\ProcessImageUpload;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use ZipArchive;
use App\Jobs\ImageUpload;
use Illuminate\Container\Attributes\DB;
use Illuminate\Support\Facades\DB as FacadesDB;

class ImageController extends Controller
{
    // Verwerkt het uploaden van een afbeeldingsbestand
    public function store(Request $request)
    {
        // Validate the uploaded files
        $request->validate([
            'image.*' => 'required|image|max:2048', // Validate image and max size of 2MB
        ]);

        $uploadCount = 0;

        // Check if the request contains image files
        if ($request->hasFile('image') && is_array($request->file('image'))) {
            // Loop through each uploaded file
            foreach ($request->file('image') as $file) {
                try {
                    // Store the file in the 'public' disk (this will store it in storage/app/public/images)
                    $path = $file->store('images', 'public');

                    // Create a new image record in the database

                    $batchId = uniqid('batch_');
                    foreach ($request->file('image') as $file) {
                        Image::create([
                            'image_data' => null,
                            'processed'  => false,
                            'batch_id'   => $batchId,
                            'error'      => $e->getMessage(),
                        ]);
                    }
                    // Dispatch the job to process the image (resize, optimize, etc.)
                    ProcessImageUpload::dispatch($image->id);

                    // Increment the count of successfully uploaded images
                    $uploadCount++;
                } catch (\Exception $e) {
                    // Log any errors that occur during the file upload
                    \Log::error('Error uploading image: ' . $e->getMessage());
                }
            }

            // After processing all images, redirect with a success message
            return redirect()
                ->route('home')
                ->with('success', "$uploadCount foto(s) succesvol geüpload.");
        }

        // If no images are uploaded, redirect with an error message
        return redirect('/create')
            ->with('error', 'Je kan alleen foto’s uploaden');
    }


    // Gepagineerde weergave van verwerkte afbeeldingen
    public function show()
    {
        $images = Image::where('processed', true)->cursorPaginate(9);
        // $images = Image::cursorPaginate(9);

        return view('index', ['images' => $images]);
    }

    // Download alle verwerkte afbeeldingen als geoptimaliseerde, met watermerk toegevoegde ZIP
    public function downloadZip($id, $timestamp)
    {
        // Haal alle (meestal één) afbeeldingen op die overeenkomen met id + processed + exact created_at UNIX-timestamp

        $images = Image::where('batch_id', $batchId)
            ->where('processed', true)
            ->get();

        if ($images->isEmpty()) {
            return redirect()->back()->with('error', 'Geen foto gevonden.');
        }

        // Bereid geoptimaliseerde map voor
        $optDir = storage_path('app/public/images/optimized');
        if (! is_dir($optDir)) {
            mkdir($optDir, 0755, true);
        }

        // Maak een ZIP-bestand aan

        // Zet UNIX-timestamp om in een leesbare string
        $datum = Carbon::createFromTimestamp($timestamp)
            ->format('d-m-Y_H-i-s');    // bijv. "23-04-2025_10-09-06"

        // Gebruik onderstrepingstekens (geen apostrofs) voor bestandssystemenveiligheid
        $zipName  = "fotos_van_{$datum}.zip";

        $zipPath = storage_path("app/public/images/{$zipName}");
        $zip     = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            return redirect()->back()->with('error', 'Zippen gefaald.');
        }

        // ImageManager + watermerk
        $manager   = new ImageManager(new Driver());
        $watermark = storage_path('app/public/images/watermark.png');

        foreach ($images as $imgRow) {
            $orig = storage_path("app/public/{$imgRow->image_data}");
            if (! file_exists($orig)) {
                continue;
            }

            $img = $manager
                ->read($orig)
                ->scale(width: 300);

            if (file_exists($watermark)) {
                $img->place($watermark);
            }

            $filename      = pathinfo($imgRow->image_data, PATHINFO_FILENAME) . '.png';
            $optimizedPath = "{$optDir}/{$filename}";
            $img->toPng()->save($optimizedPath);

            $zip->addFile($optimizedPath, "images/optimized/{$filename}");
        }

        $zip->close();

        return response()
            ->download($zipPath, $zipName)
            ->deleteFileAfterSend(true);
    }

    public function downloadImg($id, $timestamp)
    {
        // Get all images that match id + processed + exact created_at UNIX-timestamp
        $images = Image::where('id', $id)
            ->where('processed', true)
            ->whereRaw('UNIX_TIMESTAMP(created_at) = ?', [$timestamp])
            ->get();

        if ($images->isEmpty()) {
            return redirect()->back()->with('error', 'Geen foto gevonden.');
        }

        // Prepare optimized directory
        $optDir = storage_path('app/public/images/optimized');
        if (! is_dir($optDir)) {
            mkdir($optDir, 0755, true);
        }

        // Get the first image from the result
        $imgRow = $images->first();
        $orig = storage_path("app/public/{$imgRow->image_data}");

        if (! file_exists($orig)) {
            return redirect()->back()->with('error', 'Originele foto niet gevonden.');
        }

        // ImageManager + watermark
        $manager = new ImageManager(new Driver());
        $watermark = storage_path('app/public/images/watermark.png');

        $img = $manager
            ->read($orig)
            ->scale(width: 300);

        if (file_exists($watermark)) {
            $img->place($watermark);
        }

        // Format timestamp in a readable string
        $datum = Carbon::createFromTimestamp($timestamp)
            ->format('d-m-Y_H-i-s');

        // Create filename for the downloaded image
        $filename = pathinfo($imgRow->image_data, PATHINFO_FILENAME) . '.png';
        $downloadName = "foto_van_{$datum}.png";
        $optimizedPath = "{$optDir}/{$filename}";

        // Save the processed image
        $img->toPng()->save($optimizedPath);

        // Dispatch the job for further processing
        ImageUpload::dispatch($imgRow->id);

        return response()
            ->download($optimizedPath, $downloadName)
            ->deleteFileAfterSend(true);
    }
}
