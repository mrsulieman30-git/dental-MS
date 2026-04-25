<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\DentalImage;
use App\Models\ImagingSeries;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ImagingController extends Controller
{
    use ApiResponseTrait;

    public function index(Patient $patient): JsonResponse
    {
        Gate::authorize('view', $patient);

        $series = ImagingSeries::query()
            ->where('patient_id', $patient->id)
            ->withCount('images')
            ->with([
                'taker:id,first_name,last_name',
                'images:id,series_id,file_path,thumbnail_path,image_number',
            ])
            ->latest('taken_at')
            ->get()
            ->map(fn (ImagingSeries $item) => $this->serializeSeries($item, false));

        return $this->success($series);
    }

    public function showSeries(ImagingSeries $series): JsonResponse
    {
        Gate::authorize('view', $series->patient);

        $series->load([
            'taker:id,first_name,last_name',
            'images.aiAnalysis',
        ]);

        return $this->success($this->serializeSeries($series, true));
    }

    public function show(DentalImage $image): JsonResponse
    {
        Gate::authorize('view', $image->patient);

        $image->load(['series', 'aiAnalysis']);

        return $this->success($this->serializeImage($image));
    }

    public function upload(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'appointment_id' => ['nullable', 'exists:appointments,id'],
            'series_type' => ['required', 'in:fmx,bw,pa,pan,ceph,cbct,intraoral_photo,extraoral_photo,other'],
            'name' => ['nullable', 'string', 'max:255'],
            'taken_at' => ['nullable', 'date'],
            'tooth_number' => ['nullable', 'integer', 'between:1,32'],
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['required', 'file', 'max:51200', 'mimes:jpg,jpeg,png,tif,tiff,dcm'],
        ]);

        $patient = Patient::findOrFail((int) $validated['patient_id']);
        Gate::authorize('view', $patient);

        $series = ImagingSeries::create([
            'patient_id' => $patient->id,
            'appointment_id' => $validated['appointment_id'] ?? null,
            'series_type' => $validated['series_type'],
            'name' => $validated['name'] ?? strtoupper(str_replace('_', ' ', $validated['series_type'])) . ' ' . now()->format('Y-m-d H:i'),
            'taken_at' => $validated['taken_at'] ?? now(),
            'taken_by' => $request->user()->id,
            'device_id' => null,
            'notes' => null,
            'is_archived' => false,
        ]);

        foreach ($request->file('files', []) as $index => $file) {
            $extension = strtolower((string) $file->getClientOriginalExtension());
            $format = $this->normalizeFileFormat($extension);
            $filename = sprintf(
                '%02d-%s.%s',
                $index + 1,
                Str::uuid()->toString(),
                $extension
            );

            $storedPath = $file->storeAs(
                sprintf('clinical-imaging/%d/%d', $patient->id, $series->id),
                $filename,
                'public'
            );

            [$width, $height] = $this->extractDimensions($file->getPathname());

            DentalImage::create([
                'series_id' => $series->id,
                'patient_id' => $patient->id,
                'image_type' => $this->resolveImageType($validated['series_type']),
                'tooth_number' => $validated['tooth_number'] ?? null,
                'image_number' => $index + 1,
                'file_path' => $storedPath,
                'thumbnail_path' => $storedPath,
                'file_format' => $format,
                'file_size_bytes' => $file->getSize(),
                'width_px' => $width,
                'height_px' => $height,
                'metadata' => [
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                ],
                'annotations' => [],
                'is_primary_for_claim' => false,
            ]);
        }

        $series->load(['taker:id,first_name,last_name', 'images.aiAnalysis']);

        return $this->success($this->serializeSeries($series, true), 'Images uploaded', 201);
    }

    public function updateAnnotations(Request $request, DentalImage $image): JsonResponse
    {
        Gate::authorize('view', $image->patient);

        $validated = $request->validate([
            'annotations' => ['nullable', 'array'],
        ]);

        $image->update([
            'annotations' => $validated['annotations'] ?? [],
        ]);

        $image->load(['series', 'aiAnalysis']);

        return $this->success($this->serializeImage($image), 'Annotations updated');
    }

    public function shareLink(DentalImage $image): JsonResponse
    {
        Gate::authorize('view', $image->patient);

        $url = URL::temporarySignedRoute(
            'imaging.shared',
            now()->addHours(48),
            ['image' => $image->id]
        );

        return $this->success(['url' => $url], 'Share link generated');
    }

    public function shared(DentalImage $image): BinaryFileResponse
    {
        $path = Storage::disk('public')->path($image->file_path);

        abort_unless(is_file($path), 404);

        return response()->file($path);
    }

    private function serializeSeries(ImagingSeries $series, bool $includeImages): array
    {
        $firstImage = $series->relationLoaded('images')
            ? $series->images->sortBy('image_number')->first()
            : null;

        $payload = [
            'id' => $series->id,
            'patient_id' => $series->patient_id,
            'appointment_id' => $series->appointment_id,
            'series_type' => $series->series_type,
            'name' => $series->name,
            'taken_at' => optional($series->taken_at)->toISOString(),
            'taken_by' => $series->taken_by,
            'device_id' => $series->device_id,
            'notes' => $series->notes,
            'is_archived' => $series->is_archived,
            'images_count' => $series->images_count ?? ($series->relationLoaded('images') ? $series->images->count() : 0),
            'thumbnail_path' => $firstImage ? $this->publicUrl($firstImage->thumbnail_path ?: $firstImage->file_path) : null,
            'provider' => $series->relationLoaded('taker') && $series->taker
                ? [
                    'id' => $series->taker->id,
                    'full_name' => trim($series->taker->first_name . ' ' . $series->taker->last_name),
                ]
                : null,
        ];

        if ($includeImages) {
            $payload['images'] = $series->relationLoaded('images')
                ? $series->images
                    ->sortBy('image_number')
                    ->values()
                    ->map(fn (DentalImage $image) => $this->serializeImage($image))
                    ->all()
                : [];
        }

        return $payload;
    }

    private function serializeImage(DentalImage $image): array
    {
        return [
            'id' => $image->id,
            'series_id' => $image->series_id,
            'patient_id' => $image->patient_id,
            'image_type' => $image->image_type,
            'tooth_number' => $image->tooth_number,
            'image_number' => $image->image_number,
            'file_path' => $this->publicUrl($image->file_path),
            'thumbnail_path' => $this->publicUrl($image->thumbnail_path ?: $image->file_path),
            'file_format' => $image->file_format,
            'file_size_bytes' => $image->file_size_bytes,
            'width_px' => $image->width_px,
            'height_px' => $image->height_px,
            'metadata' => $image->metadata ?? [],
            'annotations' => $image->annotations ?? [],
            'is_primary_for_claim' => (bool) $image->is_primary_for_claim,
            'created_at' => optional($image->created_at)->toISOString(),
            'updated_at' => optional($image->updated_at)->toISOString(),
            'ai_analysis' => $image->relationLoaded('aiAnalysis') && $image->aiAnalysis
                ? [
                    'id' => $image->aiAnalysis->id,
                    'analysis_provider' => $image->aiAnalysis->analysis_provider,
                    'analysis_version' => $image->aiAnalysis->analysis_version,
                    'findings' => $image->aiAnalysis->findings ?? [],
                    'analyzed_at' => optional($image->aiAnalysis->analyzed_at)->toISOString(),
                    'is_accepted_by_provider' => (bool) $image->aiAnalysis->is_accepted_by_provider,
                ]
                : null,
        ];
    }

    private function publicUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    private function resolveImageType(string $seriesType): string
    {
        return match ($seriesType) {
            'intraoral_photo', 'extraoral_photo' => 'photo',
            'cbct' => 'cbct_slice',
            default => 'xray',
        };
    }

    private function normalizeFileFormat(string $extension): string
    {
        return match ($extension) {
            'jpeg' => 'jpg',
            'tif' => 'tiff',
            default => $extension,
        };
    }

    private function extractDimensions(string $path): array
    {
        $dimensions = @getimagesize($path);

        if (!$dimensions) {
            return [null, null];
        }

        return [$dimensions[0] ?? null, $dimensions[1] ?? null];
    }
}
