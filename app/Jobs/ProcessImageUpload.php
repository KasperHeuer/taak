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

class ProcessImageUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $imageId;

    public $tries = 3;
    public $backoff = 60;

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

        if (!Storage::disk('public')->exists($imagePath)) {
            \Log::error('Image file does not exist at path: ' . $imagePath);
            return;
        }

        $fileContent = Storage::disk('public')->get($imagePath);

        $tempInputFile = tempnam(sys_get_temp_dir(), 'img_input_');
        if (file_put_contents($tempInputFile, $fileContent) === false) {
            \Log::error('Failed to write image content to temporary input file');
            return;
        }

        $manager = new ImageManager(new Driver());

        try {
            $img = $manager->read($tempInputFile);
        } catch (\Exception $e) {
            \Log::error('Failed to read image: ' . $e->getMessage());
            @unlink($tempInputFile);
            return;
        }

        $maxSize = 1000;
        $width = $img->width();
        $height = $img->height();

        if ($width > $maxSize || $height > $maxSize) {
            $scaleFactor = min($maxSize / $width, $maxSize / $height);
            $img->resize(
                intval($width * $scaleFactor),
                intval($height * $scaleFactor)
            );
        }

        $tempOutputFile = tempnam(sys_get_temp_dir(), 'img_output_');
        try {
            $img->toPng()->save($tempOutputFile);
        } catch (\Exception $e) {
            \Log::error('Failed to save processed image: ' . $e->getMessage());
            @unlink($tempInputFile);
            return;
        }

        $optimizerChain = OptimizerChainFactory::create();
        $optimizerChain->optimize($tempOutputFile);

        $optimizedImagePath = 'processed_' . basename($imagePath);
        $outputPath = 'images/' . $optimizedImagePath;

        if (Storage::disk('public')->put($outputPath, file_get_contents($tempOutputFile))) {
            @unlink($tempInputFile);
            @unlink($tempOutputFile);

            $image->image_data = $outputPath;
            $image->processed = true;
            $image->save();

            \Log::info('Image successfully processed and saved: ' . $outputPath);
        } else {
            \Log::error('Failed to save processed image to storage: ' . $outputPath);
        }
    }
}
