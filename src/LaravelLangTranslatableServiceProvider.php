<?php

namespace LLT;

use Illuminate\Support\ServiceProvider;
use LLT\Commands\TranslateFileCommand;

class LaravelLangTranslatableServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/laravel-lang-translatable.php' => config_path('laravel-lang-translatable.php'),
        ]);
    }

    public function register(): void
    {
        $this->commands([
            TranslateFileCommand::class
        ]);
    }
}
