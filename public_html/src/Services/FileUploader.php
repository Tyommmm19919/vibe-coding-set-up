<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\String\Slugger\SluggerInterface;

final class FileUploader
{
    public const KIND_IMAGE = 'image';
    public const KIND_AUDIO = 'audio';
    public const KIND_DOCUMENT = 'document';

    private const TYPES = [
        self::KIND_IMAGE => [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ],
        self::KIND_AUDIO => [
            'audio/webm' => 'webm',
            'video/webm' => 'webm',
            'audio/ogg' => 'ogg',
            'audio/wav' => 'wav',
            'audio/x-wav' => 'wav',
            'audio/mpeg' => 'mp3',
            'audio/mp4' => 'm4a',
        ],
        self::KIND_DOCUMENT => [
            'application/pdf' => 'pdf',
            'text/plain' => 'txt',
        ],
    ];

    public function __construct(
        private readonly string $targetDirectory,
        private readonly SluggerInterface $slugger,
    ) {
    }

    public function upload(UploadedFile $file, string $kind = self::KIND_IMAGE): string
    {
        if (!$file->isValid()) {
            throw new BadRequestHttpException('The uploaded file is invalid.');
        }

        $allowed = self::TYPES[$kind] ?? null;
        if ($allowed === null) {
            throw new \InvalidArgumentException('Unknown upload kind.');
        }

        $size = $file->getSize();
        if ($size === false || $size <= 0 || $size > 30 * 1024 * 1024) {
            throw new BadRequestHttpException('The uploaded file is too large.');
        }

        $mime = (string) $file->getMimeType();
        $extension = $allowed[$mime] ?? null;
        if ($extension === null) {
            throw new BadRequestHttpException('Unsupported file format.');
        }

        if (!is_dir($this->targetDirectory) && !mkdir($directory = $this->targetDirectory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException('Could not create the upload directory.');
        }

        $base = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safe = trim((string) $this->slugger->slug($base)) ?: 'file';
        $name = $safe . '-' . bin2hex(random_bytes(8)) . '.' . $extension;

        $file->move($this->targetDirectory, $name);

        return $name;
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}
