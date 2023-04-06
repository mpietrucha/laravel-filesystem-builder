<?php

namespace Mpietrucha\Laravel\Filesystem;

use Exception;
use Symfony\Component\Finder\SplFileInfo;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Collection;
use Mpietrucha\Support\Concerns\HasFactory;
use Mpietrucha\Support\Types;
use Illuminate\Support\Facades\File;

class Builder
{
    use HasFactory;

    protected ?string $root = null;

    public function __construct(protected Filesystem|FilesystemAdapter $adapter)
    {
    }

    public function files(?string $path): Collection
    {
        return collect($this->adapter->files($path))->map($this->build(...));
    }

    public function allFiles(?string $path): Collection
    {
        $this->assertPath($path);

        return collect($this->adapter->allFiles($path))->map($this->build(...));
    }

    protected function build(string|SplFileInfo $file): SplFileInfo
    {
        if (! Types::string($file)) {
            return $file;
        }

        return new SplFileInfo(
            collect([$this->root(), $file])->toDirectory(), File::dirname($file), $file
        );
    }

    protected function root(): ?string
    {
        $config = invade($this->adapter)->config;

        return $this->root ??= Arr::get($config, 'root');
    }

    protected function assertPath(?string $path): void
    {
        if (! $this->adapter instanceOf Filesystem) {
            return;
        }

        if (! Types::null($path)) {
            return;
        }

        throw new Exception('Path cannot be empty in Filesystem.');
    }
}
