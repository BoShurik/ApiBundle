# ApiBundle

[![Build Status](https://travis-ci.com/BoShurik/ApiBundle.svg?branch=master)](https://travis-ci.com/BoShurik/ApiBundle)

Set of useful services for building API

## Installation

#### Composer

``` bash
$ composer require boshurik/api-bundle
```

If you are using [symfony/flex][1] it is all you need to do

#### Register the bundle

``` php
<?php
// config/bundles.php

return [
    //...
    \BoShurik\ApiBundle\BoShurikApiBundle => ['all' => true],
];

```

## Usage

#### ArgumentResolver

```php
class SomeController
{
    public function someAction(SomeModel $model)
    {
        // $model - validated object   
    }
}
```

#### Serializer

```php
/**
 * @var SomeEntity $entity
 */
$data = $this->serializer->normalize($entity); // $entity->getId() value
$entity = $this->serializer->denormalize('some-id', SomeEntity::class); // SomeEntity instant  
```

#### ValidationException

```php
$violations = $this->validator->validate($model);
if ($violations->count() > 0) {
    throw new ValidationException($violations);
}
```

[1]: https://flex.symfony.com