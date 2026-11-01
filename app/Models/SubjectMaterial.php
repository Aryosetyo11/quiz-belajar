<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['title', 'description', 'type', 'video_url', 'external_url', 'file_path', 'original_file_name', 'file_size'])]
class SubjectMaterial extends Model
{
    public function isVideo(): bool
    {
        return $this->type === 'video';
    }

    public function videoEmbedUrl(): ?string
    {
        if (! $this->isVideo() || ! is_string($this->video_url)) {
            return null;
        }

        $host = strtolower((string) parse_url($this->video_url, PHP_URL_HOST));
        $path = trim((string) parse_url($this->video_url, PHP_URL_PATH), '/');
        $videoId = null;

        if (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtube-nocookie.com', 'www.youtube-nocookie.com'], true)) {
            parse_str((string) parse_url($this->video_url, PHP_URL_QUERY), $query);
            $videoId = $query['v'] ?? (str_starts_with($path, 'embed/') ? substr($path, 6) : null);
            $host = 'www.youtube-nocookie.com';
        } elseif (in_array($host, ['youtu.be', 'www.youtu.be'], true)) {
            $videoId = $path;
            $host = 'www.youtube-nocookie.com';
        } elseif (in_array($host, ['vimeo.com', 'www.vimeo.com'], true)) {
            $videoId = $path;
            $host = 'player.vimeo.com';
            $path = 'video/'.$videoId;
        }

        if (! is_string($videoId) || ! preg_match('/^[A-Za-z0-9_-]{6,20}$/', $videoId)) {
            return null;
        }

        return $host === 'player.vimeo.com'
            ? 'https://'.$host.'/'.$path
            : 'https://'.$host.'/embed/'.$videoId;
    }

    public function isExternalLink(): bool
    {
        return $this->type === 'link';
    }

    public function isPdf(): bool
    {
        return ! $this->isVideo()
            && ! $this->isExternalLink()
            && strtolower(pathinfo((string) $this->original_file_name, PATHINFO_EXTENSION)) === 'pdf';
    }

    public function externalEmbedUrl(): ?string
    {
        if (! $this->isExternalLink() || ! is_string($this->external_url)) {
            return null;
        }

        $host = strtolower((string) parse_url($this->external_url, PHP_URL_HOST));
        $path = (string) parse_url($this->external_url, PHP_URL_PATH);

        if ($host === 'drive.google.com') {
            $videoId = null;

            if (preg_match('#^/file/d/([A-Za-z0-9_-]+)(?:/|$)#', $path, $matches) === 1) {
                $videoId = $matches[1];
            } else {
                parse_str((string) parse_url($this->external_url, PHP_URL_QUERY), $query);
                $videoId = $query['id'] ?? null;
            }

            return is_string($videoId) && preg_match('/^[A-Za-z0-9_-]+$/', $videoId) === 1
                ? 'https://drive.google.com/file/d/'.$videoId.'/preview'
                : null;
        }

        if ($host === 'docs.google.com'
            && preg_match('#^/presentation/d/([A-Za-z0-9_-]+)(?:/|$)#', $path, $matches) === 1) {
            return 'https://docs.google.com/presentation/d/'.$matches[1].'/embed?start=false&loop=false&delayms=3000';
        }

        return null;
    }

    public function canvaDesignId(): ?string
    {
        if (! $this->isExternalLink() || ! is_string($this->external_url)) {
            return null;
        }

        $host = strtolower((string) parse_url($this->external_url, PHP_URL_HOST));
        $path = (string) parse_url($this->external_url, PHP_URL_PATH);

        if (! in_array($host, ['canva.com', 'www.canva.com'], true)
            || preg_match('#^/design/([A-Za-z0-9_-]+)(?:/|$)#', $path, $matches) !== 1) {
            return null;
        }

        return $matches[1];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
