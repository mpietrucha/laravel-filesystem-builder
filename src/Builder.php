<?php

namespace Mpietrucha\Laravel\Filesystem;

use Exception;
use Mpietrucha\Support\Types;
use Mpietrucha\Support\Rescue;
use Illuminate\Support\Arr;
use Mpietrucha\Support\File;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Mpietrucha\Support\Concerns\HasFactory;
use Symfony\Component\Finder\SplFileInfo;

class Builder
{
    use HasFactory;

    protected ?string $root = null;

    public function __construct(protected Filesystem|FilesystemAdapter $adapter)
    {
    }

    public function enshure(string|SplFileInfo $file): SplFileInfo
    {
        if (! Types::string($file)) {
            return $file;
        }

        return File::toSplFileInfo($this->path($file), $file);
    }

    public function path(?string $path): string
    {
        return collect([$this->root(), $path])->toDirectory();
    }

    public function root(): ?string
    {
        return $this->root ??= Arr::get(
            Rescue::create(fn () => invade($this->adapter)->config)->call([]), 'root'
        );
    }

    public function assert(?string $path): void
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
