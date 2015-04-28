RMT - Release Management Tool
=============================

[![Build Status](https://secure.travis-ci.org/liip/RMT.png?branch=master)](https://travis-ci.org/liip/RMT)
[![Latest Stable Version](https://poser.pugx.org/liip/RMT/version.png)](https://packagist.org/packages/liip/RMT)
[![Total Downloads](https://poser.pugx.org/liip/RMT/d/total.png)](https://packagist.org/packages/liip/RMT)
[![License](https://poser.pugx.org/liip/rmt/license.svg)](https://packagist.org/packages/liip/rmt)

RMT is a handy tool to help releasing new version of your software. You can define the type
of version generator you want to use (example: semantic versioning), where you want to store
the version (in a changelog file, as a VCS tag…) and a list of action that can be
executed before or after the release of a new version.


Installation
------------
### Option 1: as a dependency to your project
In order to use RMT your project should use [Composer](http://getcomposer.org/) as RMT will be
installed as a dev-dependency. Just go to your project root directory and execute:

    composer require --dev liip/rmt:1.*

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

### Option 2: composer global installation

You can add liip/RMT to your global composer.json and have it available globally for all your projects

Just run the following command:

`composer global require "liip/rmt"`

Make sure you have ~/.composer/vendor/bin/ in your PATH.

### Option 3: as a phar file
RMT can be installed through [phar-composer](https://github.com/clue/phar-composer/), which needs to be [installed](https://github.com/clue/phar-composer/#install) for that. phar-composer is a useful tool that allows you to create runable phar files from composer packages.

if you have phar-composer installed, you can run:

    sudo phar-composer install liip/RMT

and have phar-composer build and install the phar file to your $PATH, which then allows you to run it simply as `rmt` from command line or you can run

    phar-composer build liip/RMT

and copy the resulting phar manually where you need it (either set the phar as executable `chmod +x rmt.phar` and execute it directly `./rmt.phar` or  run it by invoking it through PHP `php rmt.phar`.

For the usage substitute RMT with what ever variant you have decided to use.



Usage
-----
Using RMT is very straightforward, just run the command:

    ./RMT release

RMT will then do the following tasks:

1. Execute the prerequisites checks
2. Ask the user to answers potentials questions
3. Execute the pre-release actions
4. Release
    * Generate a new version number
    * Persist the new version number
5. Execute the post-release actions

Here is an output example:

![screenshot](https://github.com/liip/RMT/raw/master/docs/output-example.png "First stable for RMT")


### Additional commands

The `release` command is the main behavior of the tool, but some extra commands are available:

* `current` will show your project current version number (alias version)
* `changes` display the changes that will be incorporated in the next release
* `config` display the current config (already merged)
* `init` create (or reset) the .rmt.yml config file


Configuration
-------------

All RMT configurations have to be done in the `.rmt.yml`. The file is divided in 5 root elements:

* `vcs`: The type of VCS you are using, can be `git`, `svn` or `none`
* `prerequisites`: A list `[]` of prerequisites that must be matched before starting the release process
* `pre-release-actions`: A list `[]` of actions that will be executed before the release process
* `version-generator`: The generator to use to create a new version (mandatory)
* `version-persister`: The persister to use to store the versions (mandatory)
* `post-release-actions`: A list `[]` of actions that will be executed after the release

All the entry of this config are working the same. You have to specify the class you want to handle the action. Example:

    version-generator: "simple"`
    version-persister:
       vcs-tag:
           tag-prefix: "v_"

RMT also support JSON config, but we recommend you to use YML.

### Branch specific config

Something you want to use a different release strategy according to the VCS branch, for example, you want to add a entry into a CHANGELOG only in the `master` branch. To do so, you have to place your default config into a root element named `_default`. Then you can override parts is this default config for the branch `master`. Example:

    _default:
        version-generator: "simple"
        version-persister: "vcs-tag"
    master:
        pre-release-actions: [changelog-update]

You can use the command ```RMT config``` To see the merge result between _default and your current branch

### Version generator

Build-in version number generation strategy

* simple: This generator is doing a simple increment (1,2,3...)
* semantic: A generator which implements [Semantic versioning](http://semver.org)
    * Option `allow-label` (boolean): To allow adding a label on a version (such as -beta, -rcXX)  (default: *false*)
    * Option `type`: to force the version type
    * Option `label`: to force the label

    The two forced option could be very useful if you decide that a given branch is dedicated to the next beta of a
    given version. So just force the label to beta and all release are going to be beta increments

### Version persister

Class in charge of saving/retrieving the version number

* vcs-tag: Save the version as a VCS tag
* changelog: Save the version in the changelog file

### Prerequisite actions

Prerequisite actions are executed before the interactive part

* `working-copy-check`: check that you don't have any VCS local changes
* `display-last-changes`: display your last changes
* `tests-check`: run the project test suite
  * Option `command`: command to run (default: *phpunit*)
  * Option `expected_exit_code`: expected return code (default: *0*)
* `composer-json-check`: run a validate on the composer.json
  * Option `composer`: how to run composer (default: *php composer.phar*)
* `composer-stability-check`: will check if the composer.json is set to the right minimum-stability
  * Option `stability`: the stability that should be set in the minimum-stability field (default: *stable*)
* `composer-security-check`: run the composer.lock against https://security.sensiolabs.org/ to check for known vulnerabilities in the dependencies

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

Extend it
---------

RMT is providing some existing actions, generators and persisters. But if you need, you can create your own. Just create a PHP script in your project, and reference it in the configuration with it's relative path:

    version-generator: "bin/myOwnGenerator.php"

or with parameters:

    version-persister:
        name: "bin/myOwnGenerator.php"
        parameter1: value1

As an example, you can look at the script [/bin/UpdateApplicationVersionCurrentVersion.php](https://github.com/liip/RMT/blob/master/bin/UpdateApplicationVersionCurrentVersion.php) configured [here](https://github.com/liip/RMT/blob/master/.rmt.yml#L9).


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
If you would like to help, to submit one of your action script or just to report a bug: just go on the project page: https://github.com/liip/RMT

Requirements
------------

PHP 5.3
Composer

Authors
-------

* Laurent Prodon Liip AG
* David Jeanmonod Liip AG
* and [others contributors](https://github.com/liip/RMT/graphs/contributors)

License
-------

RMT is licensed under the MIT License - see the LICENSE file for details
