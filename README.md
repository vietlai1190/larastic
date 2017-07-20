# Larastic
A simple library for Elastic Search

## Requirement
You must be running _at least_ Elasticsearch 1.0. All lowerers version *will not work* and are not supported.

## Installation
Add `vietlai/larastic` to `composer.json` file in your project:
```json
vietlai/larastic
```
Run `composer update` to pull down the latest version of Larastic.

Or install it directly from the command line using composer:
```shell
composer require vietlai/larastic
```

## Configuration

Once installed via Composer you need to add the service provider. Do this by adding the following to the 'providers' section of the application config (usually app/config/app.php):
```php
'providers' => [
    ...
    Larastic\LarasticServiceProvider::class,
],
```

You might want to add Larastic\LarasticFacade to class aliases in config/app.php:
```php
'aliases' => [
    ...
	'Elastic' => \Larastic\LarasticFacade::class,
],
```

To customize the configuration file, publish the package configuration using `artisan`:
```php
php artisan vendor:publish --provider="Larastic\LarasticServiceProvider"
```

After you publish the configuration file as suggested above, you may configure ElasticSearch by adding the following to laravel .env file:
```php
ES_HOST=localhost
ES_PORT=9200
ES_USER=es_admin
ES_PASS=es_password
```

## Usage
Here is an example of search, index and delete using Larastic.
```php
class BookController extends Controller
{
    const ES_INDEX = 'books';
    const ES_TYPE = 'books';

    /**
    * Search using Elastic example
    */
    public function index()
    {
        $page = 1;
        $limit = 15;

        $query = \Elastic::query();
        $query->match('A Novel', ['title', 'author']);

        $result = $query->getResult([
            'index' => self::ES_INDEX,
            'type' => self::ES_TYPE
        ], $page, $limit);

        return $result;
    }

    /**
     * Index data to Elastic
     */
    public function mapping()
    {
        $data = [
            0 => (object) ['id' => 1, 'title' => 'The Burial Hour', 'author' => 'Jeffery Deaver', 'published_date' => '12/09/2016'],
            1 => (object) ['id' => 2, 'title' => 'Mangrove Lightning', 'author' => 'Randy Wayne', 'published_date' => '20/10/2016'],
            2 => (object) ['id' => 3, 'title' => 'The Good Assassin: A Novel', 'author' => 'Paul Vidich', 'published_date' => '11/06/2017'],
            3 => (object) ['id' => 4, 'title' => 'With Blood Upon the Sand', 'author' => 'Bradley Beaulieu', 'published_date' => '22/12/2017']
        ];

        $this->indexData($data);
        echo 'Index complete'; die;
    }

    /**
     * Delete index
     */
    public function delete()
    {
        \Elastic::indices()->delete(['index' => self::ES_INDEX]);

        echo 'Delete index: "'.self::ES_INDEX.'" success!';
    }

    /**
     * @param $data
     * @return bool|string
     */
    private function indexData($data)
    {
        try {
            foreach ($data as $dt) {
                $object_id = 'book_' . $dt->id;
                $data_index['id'] = $dt->id;
                $data_index['title'] = $dt->title;
                $data_index['date'] = $dt->date;

                if ($data_index) {
                    \Elastic::index([
                        'index' => self::ES_INDEX,
                        'type' => self::ES_TYPE,
                        'id' => $object_id,
                        'body' => $data_index
                    ]);
                }
            }

            return true;
        } catch (GeneralException $e) {
            \Log::info($e->getMessage());
            return $e->getMessage();
        }
    }
}
```

Enjoy it now ^^!
