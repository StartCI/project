<p align="center">
<img src="https://raw.githubusercontent.com/StartCI/StartCI/main/logo%20ci.png" />
</p>

# StartCI - CodeIgniter 4 Enhancement Project

### Table of Contents

1. [Introduction](#introduction)
2. [Requirements](#requirements)
3. [Installation](#installation)
4. [Usage](#usage)
<!-- 5. [Contributing](#contributing)
6. [Support](#support)
7. [StartCI Documentation](#startci-documentation) -->

## Introduction

Welcome to the documentation of the "StartCI" project - a powerful extension for the CodeIgniter 4 framework!

## Requirements

Before you start using "StartCI," make sure you have the following prerequisites:

- PHP 8.1 or higher
- CodeIgniter 4 installed in your development environment
- Composer installed to manage project dependencies


### About the Project

"StartCI" is an advanced library designed to enhance the capabilities of the renowned PHP framework, CodeIgniter 4. Developed to provide an efficient and versatile development experience, this project offers a collection of functions, libraries, and additional resources to take your web applications to new heights.

### Installation

To install the "StartCI" project, you need to add the following configuration to your `composer.json` file:

```json
"repositories": [
    {
        "type": "git",
        "url": "https://github.com/StartCI/project.git",
        "reference": "main"
    }
]
```

After adding the repository, you can run the following command in your project's root directory:

```bash
composer require startci/project:dev-main
```
To initialize the "StartCI" project and generate the necessary files and configurations, run the following command in your project's root directory:

```bash
php spark startci:init
```

### Usage


It seems like you are describing a custom implementation of the "table" method with various functionalities. Unfortunately, as of my knowledge cutoff in September 2021, there is no built-in method like this in CodeIgniter 4. However, it is possible that such a method might have been added or implemented in a custom library or extension.

Let's clarify the examples you provided:

```php
table('test')->create([
    'field' => 'text'
]);
// Creates the 'test' table with a column named 'field' of type 'text'
table('test')->def();
// Returns an array with the definition of the 'test' table like ['field' => null]
table('test')->rs();
// Returns an array with all the rows from the 'test' table as objects
table('test')->first();
// Returns the first row from the 'test' table as an object like {field: 'value'}
```

#### More Examples
For additional examples and usage details, check out the "test" folder in the "StartCI" project repository. There, you can find practical examples and demonstrations of various functionalities provided by "StartCI."
