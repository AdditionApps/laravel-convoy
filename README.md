## Laravel Convoy

Note - we're still kicking the tyres on this package and it's not recommended for use in production just yet.

**Track the progress of related jobs on a queue.**

Want to give users real-time updates on the progress of a set of queued tasks?  

Need to trigger an email or log some data once a set of jobs has completed?  

Laravel Convoy offers a configurable, expressive API to group a set of queued jobs or mailables and then track this group as it is processed by your queue workers by listening for convoy events.

---

### Installation

This package requires PHP 7.2 and Laravel 6.0 or higher.

Laravel Convoy can be installed through Composer:

`composer require additionapps/laravel-convoy`

The package will automatically register itself.

To publish the config file to `config/convoy.php` run:

```
php artisan vendor:publish --provider=“AdditionApps\Convoy\ConvoyServiceProvider”
```

---

### Quick start

Add the `JoinsConvoy` trait to jobs that you would like to be able to track in a convoy.

Once the trait is in place you can establish and track a convoy as in the example below:

```
// Somewhere in your codebase e.g. a controller

$products = Product::all(); // returns 100 products

Convoy::notifyEvery(5)
    ->track(function($convoy) use ($products){
		
        $products->each(function($product){
            $job = new MyExampleJob($product)
            dispatch($job->onConvoy($convoy))
        });

    });
```

When this code runs, an event will be fired every 5 jobs that are processed by your queue workers until all jobs are processed at which point a final convoy complete event will be fired.  These events will receive information about the current status of the convoy.  

You can respond to these events however you wish using regular Laravel event listeners

---

### Making ‘queueables’ convoy-ready

Add the `JoinsConvoy` trait to your jobs and mailables:

```
use AdditionApps\Convoy\Traits\JoinsConvoy;
//...

class MyExampleJob
{
    use JoinsConvoy, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function handle(){ //... }
}
```

If you prefer to dispatch jobs using the `MyExampleJob::dispatch()` syntax then you should also ensure that you use the `DispatchesToConvoy` trait instead of the regular `Dispatchable` trait that you would normally use in a standard Laravel job class.  E.g.

```
use AdditionApps\Convoy\Traits\JoinsConvoy;
use AdditionApps\Convoy\Traits\DispatchesToConvoy;
//...

class MyExampleJob
{
    use JoinsConvoy, DispatchesToConvoy, InteractsWithQueue, Queueable, SerializesModels;
    
    public function handle(){ //... }
}
```

---

### Configuring and launching a convoy

Laravel Convoy uses a fluent API to configure each convoy.  You are free to set various options on your convoy before launching it using the `track` method.  For example:

```
// Somewhere in your codebase e.g. a controller

$products = Product::all(); // returns 100 products

Convoy::notifyEveryPercent(20)
    ->onUpdateFire(MyOwnConvoyUpdatedEvent::class)
    ->onCompleteFire(MyOwnConvoyCompletedEvent::class)
    ->track(function($convoy) use ($products){
		
        $products->each(function($product){
            $job = new MyExampleJob($product)
            dispatch($job->onConvoy($convoy))
        });

    });
```

For more information on the various configuration options see Convoy methods.

The `track` method should come last as this is where the convoy is released onto the queue.

---

### Caveats

You should ensure that you do not place any code that has side effects  in the callback function passed to the `track` method.  For example do *not* do something like the following:

```
Convoy::track(function($convoy)){
    $user = User::create();
    $job = new MyExampleJob($user);
    dispatch($job->onConvoy($convoy));
});
```

In order to build a ‘manifest’ for each convoy this code is effectively run twice.  The first is a ‘dry-run’ in which the code in your queued items is not evaluated but any other non-queueable code is, so in the example above you would end up with two users.  This also applies to notifications which are currently not supported by this package.

To be clear - your jobs themselves can produce as many side effects as required - just don't run any non-job code in the closure that causes them.

---

### Handling convoy events

Depending on how you configure your convoy, you’ll have a number of events that you can listen for.

If you have configured a convoy to notify on progress per X jobs processed or per X percent of jobs processed, the convoy will, by default, fire the following event `AdditionApps\Convoy\Events\ConvoyUpdated`.

This event will receive an instance of `AdditionApps\Convoy\DataTransferObjects\ConvoyData` in its constructor which will give you access to information about the current state of the convoy.

You are free to create event listeners in your application to respond to these events as they are raised.

As a common use-case for Laravel Convoy is to broadcast convoy updates to the UI, you can also specify a custom event that will be fired instead of the default one.  Your custom event should also accept an instance of `ConvoyData` in its constructor:

```
class CustomConvoyUpdatedEvent
{

    public $convoy;

    public function __construct(ConvoyData $convoy)
    {
        $this->convoy = $convoy;
    }

    public function broadcastOn()
    {
        //...
    }
    
}

```

Once all the jobs in a convoy have been processed (either successfully run or marked as failed), the convoy will, by default, fire a `AdditionApps\Convoy\Events\ConvoyCompleted` event.  This event is functionally the same as the `ConvoyUpdated` event.

Again, you are free to specify a custom event that should fire when the convoy is complete.  This follows the same approach as in the example above.

---

### Convoy event properties

When responding to a convoy event, you will have access to a property on the event called `convoy` which will be an instance of `AdditionApps\Convoy\DataTransferObjects\ConvoyData`.  This object has the following public properties that you can use however makes sense for your application:

#### `id`

`string` - the UUID of the convoy - mostly for internal use.

#### `manifest`
`\Illuminate\Support\Collection` - a collection of UUID for each ‘member’ of the convoy - mostly for internal use.

#### `config`
`array` - an array of configuration options for the convoy

####`total`
`int` - the total number of items in the convoy prior to the convoy being launched

####`totalProcessed` 
`int` - the total number of jobs that have been processed (either successfully or failed)

####`percentProcessed` 
`float` - the percentage of jobs that have been processed  (either successfully or failed) expressed as a decimal 

####`totalCompleted`
`int` - the total number of jobs that have been successfully handled

####`totalFailed`
`int` - the total number of jobs in the convoy marked as failed

####`startedAt`
`\Illuminate\Support\Carbon` - the date and time that the convoy started

---

### Convoy methods

When setting up a convoy you can make use of the following methods:

#### `notifyEvery`

Specify that a convoy update event should fire per X jobs processed (either completed successfully or failed):

```
Convoy::notifyEvery(5)
```

#### `notifyEveryPercent`

Specify that a convoy update event should fire X percent of jobs processed (either completed successfully or failed):

```
Convoy::notifyEveryPercent(10)
```

#### `onUpdateFire`

Specify a custom event class that will be fired when using `notifyPerJobs` or `notifyPercent`:

```
Convoy::onUpdateFire(MyCustomEvent::class)
```

#### `onCompleteFire`

Specify a custom event class that will be fired when the convoy is complete:

```
Convoy::onCompleteFire(MyCustomEvent::class)
```

#### `track`

Define a closure within which you dispatch any queued jobs, notifications or mailable that you want to be part of this convoy:

```
Convoy::track(function($convoy){
	dispatch(...)
})
```

---

#### Testing

Run tests with:

```
vendor/bin/phpunit
```

#### Contribution guide

Please see CONTRIBUTING for details

#### Changelog

Please see  CHANGELOG  for more information what has changed recently.

#### Credits

* John Wyles
* All Contributors

#### License

The MIT License (MIT). Please see LICENSE for more information.
