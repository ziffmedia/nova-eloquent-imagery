# Nova Eloquent Imagery

## Installation & Usage

For the Nova 4 compatible components:

```console
composer require ziffmedia/nova-eloquent-imagery ^2.0
```

For the Nova 3 compatible components

```console
composer require ziffmedia/nova-eloquent-imagery ^1.0
```

### Using The Field

tbd.

### Field Options

tbd.

## Development

To bring up the demo app, do the following:

```console
docker compose up -d
docker compose exec web bash
artisan migrate:fresh --seed
```

Now you may browse to http://localhost:8000/nova

A development user with the following credentials was created:

```console
email: developer@example.com
password: password
```


