# Symfony bridge for Event Sourcing and CQRS

Library providing interfaces and implementations for event-sourced applications for Symfony Applications.

This package is the symfony adapter of [Neos.EventSourcing](https://github.com/neos/Neos.EventSourcing) (which was created
for the Neos/Flow framework).

### Demo

Check out the symfony demo Repository:

https://github.com/Inchie/eventsourcing.git

## Getting started

In your symfony application, install this package and neos/event-sourcing via composer:

```shell script
composer require neos/event-sourcing-symfony-bridge neos/event-sourcing
```

### Setting up a Doctrine Event Store

Since there could be multiple Event Stores simultaneously in one application, this package comes without a pre-configured "default" store.
It is just a matter of a couple of lines of YAML to configure a custom store:

*config/packages/neos_eventsourcing.yaml:*
```yaml
neos_eventsourcing:
  stores:
    'blog.events':
      eventTableName: blog_events
    'user.events':
      eventTableName: user_events
```

Set the charset in the doctrine config to utf8mb4 by adding the following lines.

*config/packages/doctrine.yaml:*
```yaml
doctrine:
    dbal:
        connections:
            default:
                url: '%env(resolve:DATABASE_URL)%'

                # IMPORTANT: You MUST configure your server version,
                # either here or in the DATABASE_URL env var (see .env file)
                server_version: '5.7'

                default_table_options:
                    charset: utf8mb4
                    collate: utf8mb4_unicode_ci
```

Add the following to bundles.php:
```
Neos\EventSourcing\SymfonyBridge\NeosEventSourcingBundle::class => ['all' => true],
```

To make use of the newly configured Event Store one more step is required in order to finish the setup (in this case to create the corresponding database table):

```shell script
php bin/console eventsourcing:store-setup
```

### Writing events

<details><summary>Example event: <i>BlogWasCreated.php</i></summary>

```php
class BlogWasCreated implements DomainEventInterface
{
    /**
     * @var BlogIdentifier
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var UserIdentifier
     */
    private $author;

    public function __construct(
        BlogIdentifier $id,
        string $name,
        UserIdentifier $author
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->author = $author;
    }

    public function getId(): BlogIdentifier
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAuthor(): UserIdentifier
    {
        return $this->author;
    }
}
```
</details>

```php
<?php
$uuid = $this->blogRepository->nextIdentity();
$event = new BlogWasCreated(
    $uuid,
    $command->getName(),
    $command->getAuthorIdentifier()
);

$stream = StreamName::fromString('some-stream');
$this->eventStore->commit($stream, DomainEvents::withSingleEvent(
    $event
));
```

### Reading events

```php
<?php
$streamName = StreamName::fromString('some-stream');
$eventStream = $this->eventStore->load(StreamName::fromString($streamName))
```

### Reacting to events

In order to react upon new events you'll need an event listener:

```php
<?php
class BlogListProjector implements ProjectorInterface, EventSubscriberInterface
{
    private $blogRepository;

    public function __construct(BlogRepository $blogRepository)
    {
        $this->blogRepository = $blogRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            // NOTE!!! you always have to use "when*" namings, as otherwise, the EventListenerInvoker
            // will not properly call the right methods here.

            // we only use the EventSubscriber from symfony to figure out which listeners should be called.
            BlogWasCreated::class => ['whenBlogWasCreated']
        ];
    }

    public function whenBlogWasCreated(BlogWasCreated $event, RawEvent $rawEvent)
    {
        
    }
```

The `when*()` methods of classes implementing the `EventSubscriberInterface` and `ProjectorInterface` will be invoked whenever a corresponding event is committed to the Event Store.

NOTE!!! You always have to use "when*" namings, as otherwise, the EventListenerInvoker
will not properly call the right methods here.

### Replay projection

With the following command you can rebuild a projection.

```php
bin/console eventsourcing:projection-replay eventListenerClassName eventStoreContainerId
```

### Events & event listeners
The Neos EventSourcing package comes with its own events and event listeners
implementation. We cannot use this implementation for several reasons 
in the symfony context.

To get the listeners (subscribers in symfony) for an event we call 
the symfony event dispatcher (in the SymfonyEventPublisher). 

```php
$listeners = $this->eventDispatcher->getListeners($eventClassName);
```

The listeners are handled by the InternalCatchUpEventListenerCommand.
This command uses the (Neos EventSourcing) EventListenerInvoker to 
call the listeners method name. 

The specialty about this is that the EventSourcing package uses the 
"when*" namings. For that reason the listeners method names have 
to start with when* prefix too (@see Reacting to events).

### All configuration options in `neos_eventsourcing.yml`

- `stores`
  - `[name of event store]`
    - `eventTableName`: database table name to use as event storage (required)
    - `storage`: which storage engine to use for persisting events. A class name, by default: `Neos\EventSourcing\EventStore\Storage\Doctrine\DoctrineEventStorage`
    - `eventPublisherTransport`: Class name. How the asychronity between event store and projection is implemented. By default,
      `Neos\EventSourcing\SymfonyBridge\Transport\ConsoleCommandTransport` is used, but also `Neos\EventSourcing\SymfonyBridge\Transport\MessengerTransport`
      is possible.


## Internal Implementation

How is this package constructed? We try to give an overview here:

### composer.json

We replace `neos/flow` and `flowpack/jobqueue-common` to ensure these are not installed.

### 
