<?php

use BernskioldMedia\LaravelFormArchitect\Exceptions\InvalidFormFieldConfigurationException;
use BernskioldMedia\LaravelFormArchitect\Forms\Fields\Input;
use BernskioldMedia\LaravelFormArchitect\Forms\Fields\Select;
use BernskioldMedia\LaravelFormArchitect\Forms\Fields\Textarea;
use BernskioldMedia\LaravelFormArchitect\Forms\Fieldset;
use BernskioldMedia\LaravelFormArchitect\Forms\Form;
use Illuminate\Support\Facades\Blade;

it('collects validation rules keyed by the property name', function () {
    $form = Form::make([
        Input::make('email', 'Email')->email()->rules('required', 'email'),
        Input::make('name')->wired('profile.name')->rules(['required', 'max:50']),
        Textarea::make('notes'),
    ]);

    expect($form->getRules())->toBe([
        'email' => ['required', 'email'],
        'profile.name' => ['required', 'max:50'],
    ]);
});

it('serializes fields with their type-specific data', function () {
    $input = Input::make('age', 'Age')
        ->number()
        ->min(18)
        ->max(120)
        ->required()
        ->withMeta(['data-test' => 'age']);

    expect($input->toArray())->toMatchArray([
        'data-test' => 'age',
        'id' => 'age',
        'name' => 'age',
        'label' => 'Age',
        'type' => 'number',
        'min' => 18,
        'max' => 120,
        'step' => null,
        'required' => true,
        'visible' => true,
    ]);

    expect(Input::make('title')->toArray())->not->toHaveKey('min');
});

it('only allows min, max and step on number inputs', function (string $method) {
    Input::make('title')->text()->{$method}(1);
})->with(['min', 'max', 'step'])->throws(InvalidFormFieldConfigurationException::class);

it('uses the multiselect component for multiple selects', function () {
    $select = Select::make('tags')->options(collect(['a' => 'A']));

    expect($select->viewComponent())->toBe('form.select')
        ->and($select->multiple()->viewComponent())->toBe('form.multiselect')
        ->and($select->toArray()['options'])->toBe(['a' => 'A']);
});

it('json encodes a form with nested fieldsets', function () {
    $form = Form::make([
        Fieldset::make('Contact', [Input::make('phone')->tel()])->columns(2),
    ]);

    $json = json_decode(json_encode($form), true);

    expect($json['fields'][0])->toMatchArray(['label' => 'Contact', 'columns' => 2])
        ->and($json['fields'][0]['fields'][0])->toMatchArray(['name' => 'phone', 'type' => 'tel']);
});

it('renders a form through the configured blade components', function () {
    $form = Form::make([
        Input::make('email')->email()->maxlength(100)->required()->fullwidth(),
        Fieldset::make('Details', [
            Select::make('country')->options(['se' => 'Sweden', 'no' => 'Norway']),
        ])->columns(2),
    ])->withMeta(['action' => '/subscribe']);

    $html = Blade::render('<x-form-architect::form :form="$form" />', ['form' => $form]);

    expect($html)
        ->toContain('<form action="/subscribe">')
        ->toContain('<div class="field" data-fullwidth >')
        ->toContain('type="email"')
        ->toContain('maxlength="100"')
        ->toContain('required="required"')
        ->toContain('<fieldset data-columns="2"><legend>Details</legend>')
        ->toContain('<select name="country"><option value="se">Sweden</option><option value="no">Norway</option></select>');
});
