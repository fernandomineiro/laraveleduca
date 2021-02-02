<?php

namespace App\Providers;

use Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class DependecyInjectServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $dependecies = config('services-repositories');
        foreach ($dependecies as $dependecy) {
            $this->bindClasses($dependecy);
        }
    }
    
    protected function bindClasses($dependecy) {

        if (is_array($dependecy['injectDependecy'])) {
            return $this->bindClasses($dependecy['injectDependecy']);
        }

        $this->app->bind($dependecy['bind'], function ($app) use($dependecy) {
            $class = $dependecy['bind'];
            if (!empty($dependecy['class'])) {
                $class = $dependecy['class'];
            }
            
            if (empty($dependecy['injectDependecy'])) {
                return new $class();
            }
            
            return new $class($app->make($dependecy['injectDependecy']));
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
  
    }
}
