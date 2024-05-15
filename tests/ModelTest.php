<?php

beforeEach(function () {
    self::$db = db();
});

test('create', function () {
    command('startci:orm up');
    $db = static::$db;
    xdebug_break();
});

test('up', function () {
    command('startci:orm up');
    $db = static::$db;
    xdebug_break();
});

test('seed', function () {
});

test('connection', function () {
});

test('insert', function () {
});

test('update', function () {
});

test('delete', function () {
});

test('select', function () {
});
