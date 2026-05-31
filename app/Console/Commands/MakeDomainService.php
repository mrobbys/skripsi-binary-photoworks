<?php

namespace App\Console\Commands;

use Getsolaris\LaravelMakeService\MakeService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;

#[Signature('make:domain-service {domain : The domain name (e.g., Auth, Studio, Booking)} {name : The service class name} {--i : Create a service interface}')]
#[Description('Create a new service class in a domain folder')]
class MakeDomainService extends MakeService
{
    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        $domain = $this->argument('domain');

        return $rootNamespace.'\Domains\\'.$domain.'\Services';
    }

    /**
     * Get the desired class name from the input.
     */
    protected function getNameInput(): string
    {
        return $this->argument('name');
    }

    /**
     * Force the interface option to always be false to prevent interface creation.
     *
     * @param  string|null  $key
     * @return string|array|bool|null
     */
    public function option($key = null)
    {
        if ($key === 'i') {
            return false;
        }

        return parent::option($key);
    }
}
