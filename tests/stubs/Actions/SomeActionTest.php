<?php

use App\Actions\SomeAction;

uses()->group('actions');

it('', function (): void {
    (new SomeAction())->execute();
});
