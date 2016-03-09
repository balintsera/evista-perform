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

This differentiates it from all the other PHP form APIs, because there's no need to build any form object on the server side _before_ submission.

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

    // Get files and handle them (multiple/single file upload)
    $fileField = $form->getField('files');
    $uploadedFiles = $fileField->getFiles();
    foreach ($uploadedFiles as $uploadedFile) {
        // Check real file type:
        $realType = $uploadedFile->getRealType(); // eg. image/png

        $userAddedName = $uploadedFile->getUserName;

        // Move the file to its final destination
        $uploadedFile->moveToDestination($destination = '/var/uploads/');

        // Get safe file name
        $safeBaseName = $uploadedFile->getSafeName(); // no extension

        // Get the original extension from filename
        $userExtension = $uploadedFile->getUserExtension();
    }

     // Check validity
    if (!$form->isValid()) {
        // All errors can be spotted in the fields
        foreach ($form->getFields() as $field) {
            if (!$field->isValid()) {
                $validationErrors[] = $field->getErrors();
            }
        }
    }

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
<script src="/assets/bundle.js"></script>

<form method="post" action="/multiple-file-uploads" id="login-form">
    <p>
        <label for="email-1">Email: </label>
        <input type="email" name="email" placeholder="Your email" value="" pattern="^([a-zA-Z0-9_.+-])+@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$" id="email-1">
    </p>
        <label for="passwd-1">Pass: </label>
        <input type="password" name="password" value="" id="passwd-1">
    </p>
    <p>
        <label for="desc-1">Longer description: </label>
    </p>
    <p>
        <textarea name="test_textarea" id="desc-1"></textarea>
    </p>
    <p>
        <select name="test-select">
            <option value="volvo">Volvo</option>
            <option value="saab" selected>Saab</option>
            <option value="mercedes">Mercedes</option>
            <option value="audi">Audi</option>
        </select>
    </p>
    <p>
        <input type="file" multiple name="files[]">
    </p>
    <p>
        <input type="submit" value="Submit" class="btn btn-primary">
    </p>
</form>

<script>
  (function() {
    var form = document.getElementById('login-form');
    form.onsubmit = function() {

      // Three params: DOM, success callback and error callback:
      Perform.submit(
        form,
        function success(result) {
          console.log('mysuccess', result);
          var response = JSON.parse(result.target.response);
          var dumper = document.getElementById('dumper');
          dumper.innerHTML = response.dump;
        },
        function error(error) {
          console.log('myError', error);
        }
      );

      return false;
    };
  })();
</script>

```

There's a javascript file in `assets/bundle.js` that sends the form's data via POST to the form's destination (action parameter) via a global object called Perform.  



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
- [Zsolt Schutzbach](https://github.com/succli)
- [All Contributors][link-contributors]
- [Evista Creative Agency](http://evista-agency.com)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/evista/perform.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/balintsera/evista-perform/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/evista/perform.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/evista/perform.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/evista/perform.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/evista/perform
[link-travis]: https://travis-ci.org/balintsera/evista-perform
[link-scrutinizer]: https://scrutinizer-ci.com/g/evista/perform/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/evista/perform
[link-downloads]: https://packagist.org/packages/evista/perform
[link-author]: https://github.com/balintsera
[link-contributors]: ../../contributors
