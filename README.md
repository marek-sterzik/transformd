# TransforMD

TransforMD is an easy tool how to "magically" enable php-based webservers to understand markdown syntax and display it as a regular html page.

## Requirements

The tool is currently tested on this setup:

* Apache 2 with `mod_rewrite` enabled.
* PHP >= 7.3

No other tools are necessary, it will just magically work. The tool may be also used for other setups, but there the configuration is not yet completely described.

## Quick start

* Clone the repository
* Build the TransforMD binary by invoking `bin/build`. (some tools are required, they will be described later)
* Copy the resulting binary `transformd.php` and the `.htaccess` files from the package root to the webserver's root (or any directory).
* Your webserver is automatically translating markdown files to html in the directory subtree where you have copied the files.


## Customization

Customization of the output is available. Currently only a few options are changable, but much more options should be changable in the future. To customize,
the installation, just create a file named `transformd.yaml` and put it in the same directory where `transformd.php` (and also `.htaccess`) is.

Configuration will be described later.
