# Symfony bridge for Event Sourcing and CQRS (Flow Framework)

Library providing interfaces and implementations for event-sourced applications. 

## Getting started

Install this package via composer:

```shell script
composer require neos/event-sourcing-symfony-bridge
```

### Setting up an Event Store

Since there could be multiple Event Stores simultaneously in one application, this package comes without a pre-configured "default" store.
It is just a matter of a couple of lines of YAML to configure a custom store:

*config/packages/neos_eventsourcing.yaml:*
```yaml
neos_eventsourcing:
  stores:
    'blog.events':
      eventTableName: blog_events
      listenerClassNames: []
    'user.events':
      eventTableName: user_events
      listenerClassNames: []
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
* @var string
*/
private $name;

    /**
     * @var UserIdentifier
     */
    private $author;

    /**
     * @var string
     */
    private $streamName;

    /**
     * BlogWasCreated constructor.
     * @param string $name
     * @param UserIdentifier $author
     * @param string $streamName
     */
    public function __construct(
        string $name,
        UserIdentifier $author,
        string $streamName
    )
    {
        $this->name = $name;
        $this->author = $author;
        $this->streamName = $streamName;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return UserIdentifier
     */
    public function getAuthor(): UserIdentifier
    {
        return $this->author;
    }

    /**
     * @return string
     */
    public function getStreamName(): string
    {
        return $this->streamName;
    }
}
```
</details>

```php
<?php
$event = new BlogWasCreated(
    $command->getName(),
    $command->getAuthorIdentifier(),
    $streamName
);

$stream = StreamName::fromString($streamName);

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

We only use the EventSubscriber from symfony to figure out which listeners should be called.