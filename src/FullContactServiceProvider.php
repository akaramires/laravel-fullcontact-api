<?php
    /**
     * Created by Elmar <e.abdurayimov@gmail.com> Abdurayimov
     *
     * @copyright (C)Copyright 2016 elmar.eatech.org
     *               Date: 1/19/16
     *               Time: 3:10 PM
     */

    namespace Akaramires\FullContact;

    use Illuminate\Support\ServiceProvider;

    class FullContactServiceProvider extends ServiceProvider
    {
        /**
         * Indicates if loading of the provider is deferred.
         *
         * @var bool
         */
        protected $defer = true;

        /**
         * Bootstrap the application services.
         *
         * @return void
         */
        public function boot()
        {
            $this->publishes([
                __DIR__ . '/../config/fullcontact.php' => config_path('fullcontact.php'),
            ]);
        }

        /**
         * Register the application services.
         *
         * @return void
         */
        public function register()
        {
            $this->mergeConfigFrom(__DIR__ . '/../config/fullcontact.php', 'fullcontact');

            $this->app->singleton('fullcontact', function ($app) {
                $fullcontact = new FullContactPerson($app['config']['fullcontact.apikey']);

                return $fullcontact;
            });
        }

        /**
         * Get the services provided by the provider.
         *
         * @return array
         */
        public function provides()
        {
            return array('fullcontact', 'Akaramires\FullContact\FullContactPerson', 'Akaramires\FullContact\FullContactCompany');
        }
    }