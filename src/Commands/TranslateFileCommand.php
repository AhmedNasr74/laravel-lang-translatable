<?php

namespace LLT\Commands;

use Brick\VarExporter\ExportException;
use Brick\VarExporter\VarExporter;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Stichoza\GoogleTranslate\Exceptions\LargeTextException;
use Stichoza\GoogleTranslate\Exceptions\RateLimitException;
use Stichoza\GoogleTranslate\Exceptions\TranslationRequestException;
use Stichoza\GoogleTranslate\GoogleTranslate;


class TranslateFileCommand extends Command
{
    protected $signature = 'translate:file {--all=no} {--path=}';

    protected $description = 'Translate localization files';

    protected string $defaultLocale;
    private bool $success = true;

    /**
     * @throws LargeTextException
     * @throws ExportException
     * @throws RateLimitException
     * @throws TranslationRequestException
     */
    public function handle(): void
    {
        $this->defaultLocale = config('app.locale', 'en');

        if ($this->option('all') == 'no') {
            $file_name = $this->ask('Type translation file name?');
            if (!$file_name) {
                $this->error('File Name is required');
                return;
            }
            $this->translateFile($file_name);
        } else {
            $dir_path = $this->langDir() . '/' . $this->defaultLocale . '/*.php';
            $files = glob($dir_path);
            foreach ($files as $file) {
                $fileName = File::name($file);
                $this->translateFile($fileName . '.php');
                $this->info("File [$fileName].php translated");
            }
        }
        if ($this->success) {
            $this->output->success('Translation files are generated!');
        }
    }

    /**
     * @throws LargeTextException
     * @throws RateLimitException
     * @throws TranslationRequestException
     * @throws ExportException
     */
    public function translateFile($file_name): void
    {
        $full_path = $this->langDir() . '/' . $this->defaultLocale . '/' . $file_name;

        if (!File::exists($full_path)) {
            $this->fail();
            $this->error("File [$file_name] Not Found!");
            return;
        }

        $array = Arr::dot(File::getRequire($full_path));
        $locales = collect(config('laravel-lang-translatable.locales'))
            ->filter(fn($local) => $local != $this->defaultLocale);

        foreach ($locales as $idx => $local) {
            $translated_array = [];
            if ($idx != 0) {
                sleep(3);
            }
            foreach ($array as $k => $v) {
                if (empty($v)) {
                    $translated_array[$k] = is_array($v) ? [] : '';
                    continue;
                }
                $v = Str::of($v)->trim()->replace(PHP_EOL, ' ');
                $translated_array[$k] = $this->translateWord($v, $local);
            }
            $translated_array = Arr::undot($translated_array);
            $this->createTranslatedFile($translated_array, $local, $file_name);
        }
    }

    /**
     * @throws ExportException
     */
    private function createTranslatedFile(array $translated_array, string $local, string $file_name): void
    {
        $file_content = "<?php\n\nreturn " .
            VarExporter::export($translated_array, VarExporter::TRAILING_COMMA_IN_ARRAY) .
            ';' . PHP_EOL;

        $local_dir = $this->langDir() . '/' . $local;

        File::ensureDirectoryExists($local_dir);

        File::put($local_dir . '/' . $file_name, $file_content);
    }

    /**
     * @throws LargeTextException
     * @throws RateLimitException
     * @throws TranslationRequestException
     */
    private function translateWord($term, $target): string
    {
        $str = Str::of($term);
        $placeholders = [];
        $new_str = '';

        foreach ($str->explode(' ') as $term) {
            if (Str::of($term)->startsWith(':')) {
                $new_str .= '____ ';
                $placeholders[] = $term;
            } else {
                $new_str .= $term . ' ';
            }
        }

        $new_str = Str::of(GoogleTranslate::trans($new_str, $target, $this->defaultLocale));
        foreach ($placeholders as $placeholder) {
            $new_str = $new_str->replaceFirst('____', $placeholder);
        }
        return $new_str->toString();
    }


    public function langDir(): bool|array|string
    {
        $pathOption = $this->option('path');
        return empty($pathOption) ? lang_path() : base_path($pathOption);
    }

    private function fail(): void
    {
        $this->success = false;
    }
}