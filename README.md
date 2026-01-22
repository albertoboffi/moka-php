# Moka PHP

Moka PHP is a developer-first PHP library designed to build structured, scalable, and secure REST APIs ☕️.

## Prerequisites

To install and run Moka, your system must meet the following requirements:
* **PHP** `>= 8.1`
* **Composer**

Please refer to the [official Composer documentation](https://getcomposer.org/download/) for installation instructions. For convenience, below is the recommended script for UNIX-like systems:

```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === 'c8b085408188070d5f52bcfe4ecfbee5f727afa458b2573b8eaaf77b3419b0bf2768dc67c86944da1544f06fa544fd47') { echo 'Installer verified'.PHP_EOL; } else { echo 'Installer corrupt'.PHP_EOL; unlink('composer-setup.php'); exit(1); }"
php composer-setup.php
php -r "unlink('composer-setup.php');"
```

## Installation

To install Moka, run:

```bash
composer require albertoboffi/moka-php
```

## Official Documentation

Usage guides, code examples, FAQs, and additional resources are available on the [official website](http://moka-php.netsons.org/).

## Why Moka PHP?

Traditional PHP API development often requires extensive boilerplate for control flow management. Moka PHP eliminates this overhead by abstracting low-level operations such as header management, request parsing, error handling, response structuring, security and other essential API infrastructure services. This streamlined approach ensures a cleaner architecture, enabling you to focus on feature delivery rather than infrastructure plumbing.

## Contributing

Contributions are welcome. Please submit a Pull Request or open an issue to discuss proposed changes.

## License

This project is licensed under the [MIT License](LICENSE).