<?php
/* 
namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    // Дополнительный код сервис-провайдера событий...
} */


namespace App\Providers;

use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use App\Listeners\RegisteredUserHandler;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

	/**
	 * The event listener mappings for the application.
	 * 
	 * @return array
	 */
	public function getListen() {
		return $this->listen;
	}
	
	/**
	 * The event listener mappings for the application.
	 * 
	 * @param array $listen The event listener mappings for the application.
	 * @return self
	 */
	public function setListen($listen): self {
		$this->listen = $listen;
		return $this;
	}
}

