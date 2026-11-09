<?php

namespace App\Services;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use RuntimeException;
use Throwable;

/**
 * Enregistre les médias d'un produit : un dossier par produit, un sous-dossier par type.
 *
 *   products/{product_id}/featured/{uuid}.webp
 *   products/{product_id}/gallery/{uuid}.webp
 *   products/{product_id}/proofs/{uuid}.webp
 *
 * Disque principal (S3) d'abord ; repli automatique sur le disque local si S3 n'est pas
 * configuré ou si l'écriture échoue. Chaque fichier est retourné avec son disque réel.
 */
class ProductMediaStorage
{
    public const FEATURED = 'featured';
    public const GALLERY  = 'gallery';
    public const PROOFS   = 'proofs';

    /**
     * Optimise (WebP, 1600 px max) puis enregistre le fichier.
     *
     * @return array{disk: string, path: string}
     * @throws RuntimeException si aucun disque n'a pu écrire le fichier
     */
    public function store(UploadedFile $file, string $productId, string $folder, ?int $quality = null): array
    {
        $quality ??= (int) config("media.qualities.{$folder}", config('media.quality', 80));

        [$bytes, $extension, $mime] = $this->encode($file, $quality);

        $path = sprintf(
            '%s/%s/%s/%s.%s',
            trim((string) config('media.root', 'products'), '/'),
            $productId,
            $folder,
            Str::uuid(),
            $extension
        );

        foreach ($this->candidateDisks() as $disk) {
            try {
                $written = Storage::disk($disk)->put($path, $bytes, [
                    'CacheControl' => 'public, max-age=31536000, immutable',
                    'ContentType'  => $mime,
                ]);

                if (! $written) {
                    throw new RuntimeException("put() a retourné false sur le disque « {$disk} ».");
                }

                return ['disk' => $disk, 'path' => $path];
            } catch (Throwable $e) {
                Log::warning('Écriture média produit échouée', [
                    'disk'  => $disk,
                    'path'  => $path,
                    'error' => $e->getMessage(),
                ]);

                if ($disk === $this->primaryDisk()) {
                    $this->markDown($disk);
                }
            }
        }

        throw new RuntimeException("Impossible d'enregistrer le fichier « {$path} » sur aucun disque.");
    }

    /** Supprime un fichier ; n'échoue jamais (un fichier déjà absent ne doit rien casser). */
    public function delete(?string $disk, ?string $path): void
    {
        if (! $path) {
            return;
        }

        try {
            Storage::disk($disk ?: $this->legacyDisk())->delete($path);
        } catch (Throwable $e) {
            Log::warning('Impossible de supprimer le fichier orphelin', [
                'disk'  => $disk,
                'path'  => $path,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /** URL publique d'un fichier, selon le disque où il se trouve. */
    public function url(?string $disk, ?string $path): ?string
    {
        if (! $path) {
            return null;
        }


        // Storage::disk() est typé « Filesystem » (contrat) : url() n'existe que sur
        // FilesystemAdapter. L'annotation lève l'avertissement de l'IDE (P1013).
        /** @var FilesystemAdapter $filesystem */
        $filesystem = Storage::disk($disk ?: $this->legacyDisk());

        return $filesystem->url($path);
    }

    // ─────────────────────────────────────────────────────────────────

    /** @return array{0: string, 1: string, 2: string}  [octets, extension, mime] */
    private function encode(UploadedFile $file, int $quality): array
    {
        try {
            $encoded = Image::read($file)
                ->scaleDown(width: (int) config('media.max_width', 1600))
                ->toWebp(quality: $quality);

            return [(string) $encoded, 'webp', 'image/webp'];
        } catch (Throwable $e) {
            // Repli : fichier d'origine tel quel (ne bloque pas l'admin)
            Log::warning('Optimisation image impossible, fichier brut conservé', [
                'file'  => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
            ]);

            return [
                $file->get(),
                $file->guessExtension() ?: 'jpg',
                $file->getMimeType() ?: 'application/octet-stream',
            ];
        }
    }

    /** @return array<int, string> Disques à essayer, dans l'ordre. */
    private function candidateDisks(): array
    {
        $disks    = [];
        $primary  = $this->primaryDisk();
        $fallback = (string) config('media.fallback_disk', 'public');

        if ($primary && $this->isUsable($primary)) {
            $disks[] = $primary;
        }

        if (! in_array($fallback, $disks, true)) {
            $disks[] = $fallback;
        }

        return $disks;
    }

    private function primaryDisk(): ?string
    {
        $disk = config('media.disk');

        return is_string($disk) && $disk !== '' ? $disk : null;
    }

    private function legacyDisk(): string
    {
        return (string) config('media.legacy_disk', 'public');
    }

    /** Disque configuré ET pas en panne récente. */
    private function isUsable(string $disk): bool
    {
        return $this->isConfigured($disk) && ! Cache::has($this->downKey($disk));
    }

    private function isConfigured(string $disk): bool
    {
        $config = config("filesystems.disks.{$disk}");

        if (! is_array($config)) {
            return false;
        }

        if (($config['driver'] ?? null) === 's3') {
            return filled($config['bucket'] ?? null)
                && filled($config['key'] ?? null)
                && filled($config['secret'] ?? null)
                && (filled($config['region'] ?? null) || filled($config['endpoint'] ?? null))
                && (! config('media.require_url', true) || filled($config['url'] ?? null));
        }

        return true;
    }

    private function markDown(string $disk): void
    {
        Cache::put($this->downKey($disk), true, now()->addSeconds((int) config('media.down_cooldown', 60)));
    }

    private function downKey(string $disk): string
    {
        return "media.disk_down.{$disk}";
    }
}
