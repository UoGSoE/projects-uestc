<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        view()->composer('home', function ($view) {
            $view->with('helloWords', [
                'Mirëdita', 'Ahalan', 'Parev', 'Zdravei ', 'Nei Ho', 'Dobrý den', 'Goddag', 'Goede dag, Hallo',
                'Hello', 'Saluton', 'Hei', 'Bonjour', 'Guten Tag', "Gia'sou", 'Aloha', 'Shalom', 'Namaste',
                'Jó napot', 'Halló', 'Halo', 'Aksunai', 'Dia dhuit', 'Salve', 'Kon-nichiwa', 'An-nyong Ha-se-yo',
                'Salve', 'Ni hao', 'Hallo', "Dzien' dobry", 'Olá', 'Bunã ziua', 'Zdravstvuyte', 'Hola', 'Jambo',
                'Hej', 'Sa-wat-dee', 'Merhaba', 'Vitayu', 'Xin chào', 'Hylo; Sut Mae?', 'Sholem Aleychem', 'Sawubona'
            ]);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
