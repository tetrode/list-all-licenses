# instructions

The docker container list-all-licenses will examine the licenses used in the composer file. By default, it uses the 
`composer.lock` to provide this information, so all licenses in the vendor directory will be listed.

Running it is done as follows:

```shell
cat ../composer.lock | docker run -i --rm tetrode/list-all-licenses:latest php public/index.php license:list 'php://stdin'
```

The command above will cat the composer.lock file to a new instance of the list-all-licenses image, running the 
license:list with the php://stdin as input file.

This will output comma separated licensing information to stdout as follows:
```
name,homepage,description,license,version,time
package/name,https://package.home/page/,"Description of the package",LICENSE,license.version.number,YYYY-MM-DDThh:mm:ss+00:00
package/name,https://package.home/page/,"Description of the package",LICENSE,license.version.number,YYYY-MM-DDThh:mm:ss+00:00
```

The list-all-licenses container has the following options:

Debug
:   `-d, --debug`
:   shows debug information, packages that are analyzed

Format
:   `-f, --format [FORMAT]`
:   One of the following is allowed: csv, xml, yaml, json

Columns
:   `-c, --columns [COLUMNS]`
:   columns to be shown in the output. You can select one or more of the following:
:   name,homepage,description,license,version,time
:   no spaces are allowed between the columns

Output
:   `-o, --output [OUTPUT]`
:   output file - TODO how to do this in a container?

TODO

yaml does not look proper

how to run

how to run in docker

docker run --rm -i tetrode/list-all-licenses:latest < scripts/Dockerfile

list no only all licenses but create a page with

* package, link to (local) license
* package, link to (local) license
* package, link to (local) license


Examples:

``` 
/usr/local/Cellar/php@8.2/8.2.24/bin/php scripts/index.php license:list composer.lock
/usr/local/Cellar/php@8.2/8.2.24/bin/php scripts/index.php license:update
```

Run in docker:

```shell

cat ../composer.lock.example | docker run -i --rm tetrode/list-all-licenses:latest php public/index.php license:list 'php://stdin'

```

# TODO

* misschien ook andere velden zodat je source.url of require etc. kan afbeelden?
