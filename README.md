RMT - Release Management Tool
=============================

[![Build Status](https://secure.travis-ci.org/liip/RMT.png?branch=master)](https://travis-ci.org/liip/RMT)
[![Latest Stable Version](https://poser.pugx.org/liip/RMT/version.png)](https://packagist.org/packages/liip/RMT)
[![Total Downloads](https://poser.pugx.org/liip/RMT/d/total.png)](https://packagist.org/packages/liip/RMT)
[![License](https://poser.pugx.org/liip/rmt/license.svg)](https://packagist.org/packages/liip/rmt)

RMT is a handy tool to help releasing new versions of your software. You can define the type
of version generator you want to use (e.g. semantic versioning), where you want to store
the version (e.g. in a changelog file or as a VCS tag) and a list of actions that should be
executed before or after the release of a new version.

Installation
------------
### Option 1: As a development dependency of your project
In order to use RMT in your project you should use [Composer](http://getcomposer.org/) to install it
as a dev-dependency. Just go to your project's root directory and execute:

    composer require --dev liip/rmt

Then you must initialize RMT by running the following command:

    php vendor/liip/rmt/command.php init

This command will create a `.rmt.yml` config file and a `RMT` executable script in your project's
root folder. You can now start using RMT by executing:

    ./RMT

Once there, your best option is to pick one of the [configuration examples](#configuration-examples) below
and adapt it to your needs.

If you are using a versioning tool, we recommend to add both Composer files (`composer.json`
and `composer.lock`), the RMT configuration file(`.rmt.yml`) and the `RMT` executable script
to it. The `vendor` directory should be ignored as it is populated when running
`composer install`.

### Option 2: As a global Composer installation

You can add RMT to your global composer.json and have it available globally for all your projects. Therefor
just run the following command:

    composer global require liip/rmt

Make sure you have `~/.composer/vendor/bin/` in your $PATH.

### Option 3: As a Phar file
RMT can be installed through [phar-composer](https://github.com/clue/phar-composer/), which needs to be [installed](https://github.com/clue/phar-composer/#install) therefor. This useful tool allows you to create runnable Phar files from Composer packages.

If you have phar-composer installed, you can run:

    sudo phar-composer install liip/RMT

and have phar-composer build and install the Phar file to your $PATH, which then allows you to run it simply as `rmt` from the command line or you can run

    phar-composer build liip/RMT

and copy the resulting Phar file manually to where you need it (either make the Phar file executable via `chmod +x rmt.phar` and execute it
directly `./rmt.phar` or run it by invoking it through PHP via `php rmt.phar`.

For the usage substitute RMT with whatever variant you have decided to use.

### Option 4: As Drifter role
If your are using https://github.com/liip/drifter for your project, you just need three step
* Activate the `rmt` role
* Re-run the provisionning `vagrant provision`
* Init RMT for your project `php /home/vagrant/.config/composer/vendor/liip/rmt/RMT`

Usage
-----
Using RMT is very straightforward, just run the command:

    ./RMT release

RMT will then execute the following tasks:

1. Execute the prerequisites checks
2. Ask the user to answers potentials questions
3. Execute the pre-release actions
4. Release
    * Generate a new version number
    * Persist the new version number
5. Execute the post-release actions

Here is an example output:

![screenshot](https://github.com/liip/RMT/raw/master/docs/output-example.png "First stable for RMT")


### Additional commands

The `release` command provides the main behavior of the tool, additional some extra commands are available:

* `current` will show your project current version number (alias version)
* `changes` display the changes that will be incorporated in the next release
* `config` display the current config (already merged)
* `init` create (or reset) the .rmt.yml config file


Configuration
-------------

All RMT configurations have to be done in `.rmt.yml`. The file is divided in six root elements:

* `vcs`: The type of VCS you are using, can be `git`, `svn` or `none`
    * For `git` VCS you can use the two following options `sign-tag` and `sign-commit` if you want to GPG sign your release
* `prerequisites`: A list `[]` of prerequisites that must be matched before starting the release process
* `pre-release-actions`: A list `[]` of actions that will be executed before the release process
* `version-generator`: The generator to use to create a new version (mandatory)
* `version-persister`: The persister to use to store the versions (mandatory)
* `post-release-actions`: A list `[]` of actions that will be executed after the release

All entries of this config work the same. You have to specify the class you want to handle the action. Example:

    version-generator: "simple"`
    version-persister:
       vcs-tag:
           tag-prefix: "v_"

RMT also support JSON configs, but we recommend using YAML.

### Branch specific config

Sometimes you want to use a different release strategy according to the VCS branch, e.g. you want to add CHANGELOG entries only in the `master` branch. To do so, you have to place your default config into a root element named `_default`, then you can override parts of this default config for the
branch `master`. Example:

    _default:
        version-generator: "simple"
        version-persister: "vcs-tag"
    master:
        pre-release-actions: [changelog-update]

You can use the command `RMT config` to see the merge result between _default and your current branch.

### Version generator

Build-in version number generation strategies.

* simple: This generator is doing a simple increment (1,2,3...)
* semantic: A generator which implements [Semantic versioning](http://semver.org)
    * Option `allow-label` (boolean): To allow adding a label on a version (such as -beta, -rcXX)  (default: *false*)
    * Option `type`: to force the version type
    * Option `label`: to force the label

    The two forced option could be very useful if you decide that a given branch is dedicated to the next beta of a
    given version. So just force the label to beta and all release are going to be beta increments.

### Version persister

Class in charge of saving/retrieving the version number.

* vcs-tag: Save the version as a VCS tag
    * Option `tag-pattern`: Allow to provide a regex that all tag must match. This allow for example to release a version 1.X.X in a specific branch and to release a 2.X.X in a separate branch
    * Option `tag-prefix`: Allow to prefix all VCS tag with a string. You can have a numeric versionning but generation tags such as `v_2.3.4`. As a bonus you can use a specific placeholder: `{branch-name}` that will automatically inject the current branch name in the tag. So use, simple generation and `tag-prefix: "{branch-name}_"` and it will generate tag like `featureXY_1`, `featureXY_2`, etc...

* changelog: Save the version in the changelog file
    * Option `location`: Changelog file name an location (default: *CHANGELOG*)

### Prerequisite actions

Prerequisite actions are executed before the interactive part.

* `working-copy-check`: check that you don't have any VCS local changes
  * Option `allow-ignore`: allow the user to skip the check when doing a release with `--ignore-check`
* `display-last-changes`: display your last changes
* `tests-check`: run the project test suite
  * Option `command`: command to run (default: *phpunit*)
  * Option `timeout`: the number of seconds after which the command times out (default: *60.0*)
  * Option `expected_exit_code`: expected return code (default: *0*)
* `composer-json-check`: run a validate on the composer.json
  * Option `composer`: how to run composer (default: *php composer.phar*)
* `composer-stability-check`: will check if the composer.json is set to the right minimum-stability
  * Option `stability`: the stability that should be set in the minimum-stability field (default: *stable*)
* `composer-security-check`: run the composer.lock against https://security.sensiolabs.org/ to check for known vulnerabilities in the dependencies
* `command`: Execute a system command
    * Option `cmd` The command to execute
    * Option `live_output` boolean, do we display the command output? (default: *true*)
    * Option `stop_on_error` boolean, do we break the release process on error? (default: *true*)

### Actions

Actions can be used for pre or post release parts.

* `changelog-update`: Update a changelog file. This action is further configured
  to use a specific formatter.
    * Option `format`: *simple*, *semantic* or *addTop*  (default: *simple*)
    * Option `file`: path from .rmt.yml to changelog file (default: *CHANGELOG*)
    * Option `dump-commits`: write all commit messages since the last release into the
      changelog file (default: *false*)
    * Option `insert-at`: only for addTop formatter: Number of lines to skip from the
      top of the changelog file before adding the release number (default: *0*)
    * Option `exclude-merge-commits`: Exclude merge commits from the changelog (default: *false*)
* `vcs-commit`: commit all files of the working copy (only use it with the
  `working-copy-check` prerequisite)
    * Option `commit-message`: specify a custom commit message. %version% will be replaced by the current / next version strings.
* `vcs-tag`: Tag the last commit
* `vcs-publish`: Publish the changes (commits and tags)
* `composer-update`: Update the version number in a composer file
* `update-version-class`: Update the version constant in a class file.
    * Option `class`: path to class to be updated, or fully qualified class name of the class containing the version constant
    * Option `pattern`: optional, use to specify the string replacement pattern in your
      version class. %version% will be replaced by the current / next version strings.
      For example you could use `const VERSION = '%version%';`. If you do not specify
      this option, every occurrence of the version string in the file will be replaced.
* `build-phar-package`: Builds a Phar package of the current project whose filename depends on the 'package-name' option and the deployed version: [package-name]-[version].phar
    * Option `package-name`: the name of the generate package
    * Option `destination`: the destination directory to build the package into. If prefixed with a slash, is considered absolute, otherwise relative to the project root.
    * Option `excluded-paths`: a regex of excluded paths, directly passed to the [Phar::buildFromDirectory](http://php.net/manual/en/phar.buildfromdirectory.php) method. Ex: `/^(?!.*cookbooks|.*\.vagrant|.*\.idea).*$/im`
    * Option `metadata`: an array of metadata describing the package. Ex author, project. Note: the release version is added by default but can be overridden here. 
    * Option `default-stub-cli`: the default stub for CLI usage of the package.
    * Option `default-stub-web`: the default stub for web application usage of the package.
* `command`: Execute a system command
    * Option `cmd` The command to execute
    * Option `live_output` boolean, do we display the command output? (default: *true*)
    * Option `stop_on_error` boolean, do we break the release process on error? (default: *true*)

Extend it
---------

RMT is providing some existing actions, generators, and persisters. If needed you can add your own by creating a PHP script in your project, and referencing it in the configuration via it's relative path:

    version-generator: "bin/myOwnGenerator.php"

Example with injected parameters:

    version-persister:
        name: "bin/myOwnGenerator.php"
        parameter1: value1

As an example, you can look at the script [/bin/UpdateApplicationVersionCurrentVersion.php](https://github.com/liip/RMT/blob/master/bin/UpdateApplicationVersionCurrentVersion.php) configured [here](https://github.com/liip/RMT/blob/master/.rmt.yml#L9).

*WARNING:* As the key `name` is used to define the name of the object, you cannot have a parameter named `name`.


Configuration examples
----------------------
Most of the time, it will be easier for you to pick up an example below and adapt it to your needs.

### No VCS, changelog updater only

    version-generator: semantic
    version-persister: changelog

### Using Git tags, simple versioning and prerequisites

    vcs: git
    version-generator: simple
    version-persister: vcs-tag
    prerequisites: [working-copy-check, display-last-changes]
    
### Using Git tags, simple versioning and prerequisites, and gpg sign commit and tags

    vcs: 
      name: git
      sign-tag: true
      sign-commit: true
    version-generator: simple
    version-persister: vcs-tag
    prerequisites: [working-copy-check, display-last-changes]


### Using Git tags with prefix, semantic versioning and pushing automatically

    vcs: git
    version-generator: semantic
    version-persister:
        name: vcs-tag
        tag-prefix : "v_"
    post-release-actions: [vcs-publish]

### Using semantic versioning on master and simple versioning on topic branches

    _default:
        vcs: git
        prerequisites: [working-copy-check]
        version-generator: simple
        version-persister:
            name: vcs-tag
            tag-prefix: "{branch-name}_"
        post-release-actions: [vcs-publish]

    # This entry allow to override some parameters for the master branch
    master:
        prerequisites: [working-copy-check, display-last-changes]
        pre-release-actions:
            changelog-update:
                format: semantic
                file: CHANGELOG.md
                dump-commits: true
            update-version-class:
                class: Doctrine\ODM\PHPCR\Version
                pattern: const VERSION = '%version%';
            vcs-commit: ~
        version-generator: semantic
        version-persister: vcs-tag

Contributing
------------
If you would like to help, by submitting one of your action scripts, generators, or persisters. Or just by reporting a bug just go to the project page [https://github.com/liip/RMT](https://github.com/liip/RMT).

Requirements
------------

PHP 5.3
Composer

Authors
-------

* Laurent Prodon Liip AG
* David Jeanmonod Liip AG
* Peter Petermann Gameforge 4D GmbH
* Gilles Crettenand Liip AG
* and [others contributors](https://github.com/liip/RMT/graphs/contributors)

License
-------

RMT is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.
