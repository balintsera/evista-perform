# Perform

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]


A reverse Form API that builds and processes forms automatically - from markup.


## Install

Via Composer

``` bash
$ composer require evista/perform
```

## Usage

``` php
$formService = new Service($crawler);
// Get form markup from the request to $formMarkup
$form = $formService->transpileForm($formMarkup);
```

Perform is based on a simple concept: build your form in plain ol' html in any template or any frontend like React.js then send it to the server.  The backend will take care of building a form object _from your markup,_ _populate_ it from the request, and run your _validations_.

This differentiate it from all the other PHP form APIs, because there's no need to build any form object on the server side _before_ submission.

Here is an example of a server side form building process:


```php
use Evista\Perform\Service;

// (...)

// Initialize form transpilation service (dependency injection friendly interface)
$formService = new Service($crawler);


$router->addRoute('POST', '/loginform', function (Request $request, Response $response) use($formService) {
    $formMarkup = $request->request->get('serform');
    $form = $formService->transpileForm($formMarkup);

    // Get fields:
    $fields = $form->getFields();

    // Get an input field named 'email'
    $emailField = $form->getField('email');
    
    // Get the field's submitted value
    $emailField->getValue();
    
    // Get attributes, eg. placeholder:
    $placeholder = $emailField->getAttribute('placeholder');

    // Get selected option:
    $selectField = $form->getField('test-select');
    $selected = $selectField->getValue();

    // Get the default selected option (that is selected in markup)
    $defaultSelected = $selectField->getDefaultSelectedOption();

    // Then send some response
    $response = new JsonResponse(['dump'=>(var_export($form, true))]);
    return $response;
});
```

After initializing the form builder call `transpileForm()` to build a Form object from the markup. The there's some helpful class methods to do whatever you have to, for example `getField($name)` to get any field's value.



```php
$formMarkup = $request->request->get('serform');
$form = $formService->transpileForm($formMarkup);
```

The markup arrives with the submitted datas in the 'serform' post parameter. For example, this markup:


```html
<form method="post" action="/login" id="login-form">
    <input type="email" name="email" placeholder="Your email" value="" pattern="^([a-zA-Z0-9_.+-])+@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$">
    <input type="password" name="password" value="">
    <button value="login" id="login-button">Login</button>
</form>

```

There's nothing special in it, exept maybe the HTML5 validations that are attached to the inputs with the pattern attribute. The only tricky moment is the sending of the form's markup in the `serform` parameter via javascipt to enable server side processing.


```javascript
var form = document.getElementById('login-form');
var el = document.getElementById('login-button');
el.addEventListener('click', function (event) {
     console.log('click');
     event.preventDefault();
     var data = form.serialize();
     data += '&serform='+encodeURIComponent(form.outerHTML);
     console.log(data);
     post('/loginform', data, function(response){
       // do something with the response
       // (...)
```



There is a usage example of the package in this [repo](https://github.com/balintsera/evista-perform-example).



## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email balint.sera@gmail.com instead of using the issue tracker.

## Credits

- [Balint Sera][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/evista/perform.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/evista/perform/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/evista/perform.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/evista/perform.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/evista/perform.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/evista/perform
[link-travis]: https://travis-ci.org/evista/perform
[link-scrutinizer]: https://scrutinizer-ci.com/g/evista/perform/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/evista/perform
[link-downloads]: https://packagist.org/packages/evista/perform
[link-author]: https://github.com/balintsera
[link-contributors]: ../../contributors
