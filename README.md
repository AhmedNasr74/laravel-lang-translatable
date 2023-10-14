# Laravel Lang Translatable


## Introduction

Laravel Lang Translatable is an easy-to-use package To Automatically translate language directory using google translation api.

## Installation

```
composer require nasr/laravel-lang-translatable
```

Publish the package config file using the following command:
```
php artisan vendor:publish --provider="LLT\LaravelLangTranslatableServiceProvider"
```

## Basic Usage

```
php artisan translate:file
```

This will ask you in the terminal what is the file name you want to translate in your into your supported locales in **laravel-lang-translatable config file**

For Example: auth.php

```
php artisan translate:file --all
```
Will translate your default language directory into your supported locales in **laravel-lang-translatable config file**

```
php artisan translate:file --all --path=Modules/YourModuleName/Lang
```

Will translate your default language directory of your custom directory into your supported locales in **laravel-lang-translatable config file**
