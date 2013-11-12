RMT - Release Management Tool
=============================

[![Build Status](https://secure.travis-ci.org/liip/RMT.png?branch=master)](https://travis-ci.org/liip/RMT)

RMT is a simple tool to help releasing new version of your software. You can define the type
of version generator you want to use (example: semantic versioning), where you want to store
the version (in a changelog file, as a VCS tag…) and a list of action that will be
executed and before or after the release of a new version.


Installation
------------

In order to use RMT your project should use [Composer](http://getcomposer.org/) as RMT will be
installed as a dev-dependency. Just go to your project root directory and execute:

    php composer.phar require --dev liip/rmt 0.9.*         # latest beta
    # or
    php composer.phar require --dev liip/rmt dev-master    # latest unstable

Then you must initialize RMT by running the following command:

    php vendor/liip/rmt/command.php init

This command will create a `.rmt.yml` config file and a `RMT` executable script in your
root folder. You can now start using RMT by executing:

    ./RMT

Once here, your best option is to pick one of the [configuration examples](#configuration-examples) below
and to adapt it to your needs.

If you are using a versioning tool, we recommend to add both composer files (`composer.json`
and `composer.lock`), the RMT configuration file(`.rmt.yml`) and the `RMT` executable script
to it. The `vendor` directory should be ignored since it is populated simply by running
`composer install`

Usage
-----
Using RMT is very straightforward, just run the command:

    ./RMT release

RMT will then do the following tasks:

* Execute the prerequisites checks
* Ask the user to answers potentials questions
* Generate a new version number
* Execute the pre-release actions
* Persist the new version number
* Execute the post-release actions

### Additional commands

The `release` command is the main behavior of the tool, but some extra commands are available:

* `current` will show your project current version number (alias version)
* `init` create .rmt.yml config file

Configuration
-------------

All RMT configurations have to be done in the `.rmt.yml`. The file is divided in 5 root elements:

* `vcs`: The type of VCS you are using, can be `git`, `svn` or `none`
* `prerequisites`: A list `[]` of prerequisites that must be matched before starting the release process
* `pre-release-actions`: A list `[]` of actions that will be executed before the release process
* `version-generator`: The generator to use to create a new version (mandatory)
* `version-persister`: The persister to use to store the versions (mandatory)
* `post-release-actions`: A list `[]` of actions that will be executed after the release
* `branch-specific`: A list of config parameters that will be used to override the defaults from specific branches

All the entry of this config (except the `branch-specific`) are all working the same. You have to specify the class you want to handle the action. There is two syntax available:

* The short one, example: `"version-generator": "simple"` when you have no specific parameter to provide
* The config array, example:  `"version-persister": {"name": "vcs-tag", "tag-prefix": "v_"}` when you have to provide parameters to the class.

### Version generator

Version number generation strategy

* simple: This generator is doing a simple increment (1,2,3...)
* semantic: A generator which implements [Semantic versioning](http://semver.org)

### Version persister

Class is charged of saving/retrieving the version number

* vcs-tag: Save the version as a VCS tag
* changelog: Save the version in the changelog file

### Prerequisite actions

Prerequisite actions are executed before the interactive part.

* working-copy-check: Check that you don't have any VCS local changes before release
* display-last-changes: display your last changes before release

### Actions

Actions can be used for pre or post release parts.

* changelog-update: Update a changelog file. This action is further configured
  to use a specific formatter.
    * format: *simple*|semantic|addTop
    * file: path from .rmt.yml to changelog file, default 'CHANGELOG'
    * dump-commits: *false*|true - whether to write all commit messages since
      the last release into the changelog file.
    * insert-at: Only for addTop formatter: Number of lines to skip from the
      top of the changelog file before adding the release number.
* vcs-commit: Process a VCS commit
* vcs-tag: Tag the last commit
* vcs-publish: Publish the changes (commit and tags)
* composer-update: Update the version number in a composer file
* update-version-class: Update the version constant in a class file.
    * **class**: fully qualified class name of the class containing the version constant
    * **pattern**: optional, use to specify the string replacement pattern in your
      version class. %version% will be replaced by the current / next version strings.
      For example you could use `const VERSION = '%version%';`. If you do not specify
      this option, every occurrence of the version string in the file will be replaced.

Extend it
---------

RMT is providing a large bunch of existing actions, generator and persister. But if you need, you can create your own. Just create a PHP script in your project, and reference it in the configuration with it's relative path:

    version-generator: "bin/myOwnGenerator.php"

or with parameters:

    version-persister:
        name: "bin/myOwnGenerator.php"
        parameter1: value1


Configuration examples
----------------------
Most of the time, it will be easier for you to pick up and example bellow and to adapt it to your needs.

### No VCS, changelog updater only

    version-generator: semantic
    version-persister: changelog

### Using Git tags, simple versioning and prerequisites

    vcs: git
    version-generator: simple
    version-persister: vcs-tag
    prerequisites: [working-copy-check, display-last-changes]

### Using Git tags with prefix, semantic versioning and pushing automatically

    vcs: git
    version-generator: semantic
    version-persister:
        name: vcs-tag
        prefix : "v_"
    post-release-actions: [vcs-publish]

### Using semantic versioning on master and simple versioning on topic branches

    vcs: git
    prerequisites: [working-copy-check]
    version-generator: simple
    version-persister:
        name: vcs-tag
        tag-prefix: "{branch-name}_"
    post-release-actions: [vcs-publish]
    branch-specific:
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
If you would like to help, to submit one of your action script or just to report a bug: just go on the project page: https://github.com/liip/RMT

Requirements
------------

PHP 5.3
Composer

Authors
-------

* Laurent Prodon Liip AG
* David Jeanmonod Liip AG

License
-------

RMT is licensed under the MIT License - see the LICENSE file for details
