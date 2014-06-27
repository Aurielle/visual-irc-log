# Visual IRC logs
CLI tool to generate pretty graphical representations of IRC log files and provide some additional information.
Written in PHP and Nette Framework and as a part of PV219 (Webdesign seminar) @ Masaryk University.

Currently supported log file formats:

- Supybot

## Installation
- Install all necessary dependencies by Composer
- Create `log` and `out` directories and make them writable
- Advised, but not needed: create `res` directory and put your source log files there

## Usage
This tool runs from the command line. Here's a sample syntax:
```php parse.php supybot res/#chan.2014-06-27.log```

First argument is the format of your logfile, second is path to the logfile. If your path contains spaces, enclose it with quotes.

Running this command will create a html file in `out` directory. This file loads every library from the CDN and doesn't require any additional files to display properly.
So it can be easily distributed or made public.

## Demo
Check out this example parsed log [here](https://dl.dropboxusercontent.com/u/158898/%23sunsub.2014-06-19.html).

## Extending for another log file format
To enable parsing of other log formats, you need to write your own parser. The sample and currently only working one is located in app/Providers directory.
Your provider must implement the `Aurielle\VisualLog\Providers\IProvider` interface and return an instance of a `Aurielle\VisualLog\Log\Log` object. To enable
its usage for parsing, it needs to be added to `$allowedProviders` array in `parse.php`.

Log files are typically parsed with string functions or regular expressions. When creating the `Log` object, you have to fill it with log `Line`s. If your log file
doesn't contain one or more of the provided log line types, simply leave it out. If, on the other hand, contains some additional information not covered by these types,
let me please know via issues.

## License
BSD 3-clause. Please let me know if you build upon my work. I'd be happy to see any improvements, bug fixes, issues or pull requests and integrate them back into this repository.