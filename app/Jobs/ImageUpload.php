<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Image;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class ImageUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $imageId;

    // Maximum number of attempts (including the first attempt)
    public $tries = 3; // 2 retries (first attempt + 2 retries)

    // Delay between retries (optional)
    public $backoff = 60; // Retry after 60 seconds (can be changed based on needs)

    // Constructor
    public function __construct($imageId)
    {
        $this->imageId = $imageId;
    }

    public function handle(): void
    {
        \Log::info('Starting image processing for ID: ' . $this->imageId);

        $image = Image::find($this->imageId);
        if (!$image) {
            \Log::warning('Image not found for ID: ' . $this->imageId);
            return;
        }

        $imagePath = $image->image_data;
        \Log::info('Processing image path: ' . $imagePath);

        // Get the image content
        $fileContent = Storage::disk('public')->get($imagePath);

        // Create temporary file for processing
        $tempInputFile = tempnam(sys_get_temp_dir(), 'img_input_');
        file_put_contents($tempInputFile, $fileContent);

        // Create an ImageManager instance with the GD driver
        $manager = new ImageManager(new Driver());

        // Read the image
        $img = $manager->read($tempInputFile);

        // Define the maximum size for either dimension (width or height)
        $maxSize = 1000;

        // Get the current width and height of the image
        $width = $img->width();
        $height = $img->height();

        // Check if the image exceeds the maximum size in either dimension
        if ($width > $maxSize || $height > $maxSize) {
            // Calculate the scale factor to ensure the image fits within the maximum size
            $scaleFactor = min($maxSize / $width, $maxSize / $height);

            // Scale the image dimensions proportionally
            $img->scale(
                width: intval($width * $scaleFactor),
                height: intval($height * $scaleFactor)
            );
        }

        // Save the processed image in a new format (e.g., PNG)
        $tempOutputFile = tempnam(sys_get_temp_dir(), 'img_output_');
        $img->toPng()->save($tempOutputFile);

        // Optimize the processed image
        $optimizerChain = OptimizerChainFactory::create();
        $optimizerChain->optimize($tempOutputFile);

        // Define the output path
        $optimizedImagePath = 'processed_' . basename($imagePath);
        $outputPath = 'images/' . $optimizedImagePath;

        // Save the optimized image back to storage
        Storage::disk('public')->put($outputPath, file_get_contents($tempOutputFile));

        // Clean up temporary files
        @unlink($tempInputFile);
        @unlink($tempOutputFile);

        // Update the database record to reflect that the image has been processed
        $image->image_data = $outputPath;
        $image->processed = true;
        $image->save();

        \Log::info('Image successfully processed: ID=' . $image->id . ', processed=' . $image->processed);
    }
}
