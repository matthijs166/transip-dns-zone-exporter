# TransIP DNS Zone Exporter

The **TransIP DNS Zone Exporter** is a PHP command-line tool for exporting DNS zone data from a TransIP domain. This tool was created to make it easier to transfer a zone to another DNS provider, such as Cloudflare. It can also be used to backup your DNS zone data.

## Prerequisites

To use this tool, you will need to have [Composer](https://getcomposer.org/) and [PHP](https://www.php.net/) installed on your system.

## Installation

1. Clone this repository to your local machine.
2. Navigate to the root directory of the project in your terminal.
3. Run the following command to install the dependencies:

```sh
composer install
```

## Configuration

Before running the tool, you will need to set your TransIP username, domain, and API key in the `config.php` file. 


## Usage

To run the tool, use the following command in your terminal:

```sh
php index.php
```

After the command has completed, you should see a file with the name `[domain name].zone` in the root directory of the project. This file contains your DNS zone data in BIND format.

## Conclusion

Using the **TransIP DNS Zone Exporter** can save you a lot of time when transferring your DNS zone to another provider or backing up your data. I hope you find this tool useful!