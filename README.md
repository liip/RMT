RMT - Release Management Tool
=============================

[![Build Status](https://secure.travis-ci.org/liip/RMT.png?branch=master)](https://travis-ci.org/liip/RMT)

RMT is a simple tool to help releasing new version of your software. You can define the type of version generator you want o use (example: semantic versioning), where you want to store the version (in a changelog file, as a VCS tag, etc…) and a list of action that will be executed and before or after the release of a new version.


Installation
------------

In order to use RMT your project should use [Composer](http://getcomposer.org/) as RMT will be installed as a dev-dependency. Just go on your project root directory and execute:

    php composer.phar require-dev liip/rmt 0.9.*         # lastest beta
    # or
    php composer.phar require-dev liip/rmt dev-master    # lastest unstable

Then you must initialize RMT by running the following command:

    php vendor/liip/rmt/command.php init

This command will create for you a `rd.json` config file and a `RD` executable script in your root folder. For that point you can start using RMT, just execute it:

    ./RD

Once here, the best is to pick one of the configuration example below and to adapt it to your needs.


Usage
-----
Using RMT is very straightforward, you just have to run the command:

    ./RD release

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
* `init` create rd.json config file

Configuration
-------------

All RMT configuration have to be done in the `rd.json`. The file is divided in 5 root elements:

* `vcs`: The type of VCS you are using, can be `git`, `svn` or `none`
* `prerequisites`: A list `[]` of prerequisites that must be matched before starting the release process
* `pre-release-actions`: A list `[]` of actions that will be executed before the release process
* `version-generator`: The generator to use to create a new version (mandatory)
* `version-persister`: The persister to use to store the versions (mandatory)
* `post-release-actions`: A list `[]` of actions that will be executed after the release
* `branch-specific`: A list of config parameters that will be used to override the defaults from specific branches

All the entry of this config (except the `branch-specifc`) are all working the same. You have to specify the class you want to handle the action. There is two syntax available:

* The short one, example: `"version-generator": "simple"` when you have no specific parameter to provide
* The config array, example:  `"version-persister": {"name": "vcs-tag", "tag-prefix": "v_"}` when you have to provide parameters to the class.

### Version generator

Version number generation strategy

* simple: This generator is doing a simple increment (1,2,3...)
* semantic: A generator who implements (Semantic versioning)[http://semver.org]

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

* changelog-update: Update a changelog file
* vcs-commit: Process a VCS commit
* vcs-tag: Tag the last commit
* vcs-publish: Publish the changes (commit and tags)
* composer-update: Update the version number in a composer file

Extend it
---------

RMT is providing a large bunch of existing actions, generator and persister. But if you need, you can create your own. Just create a PHP script in your project, and reference it in the configuration with it's relative path:

    "version-generator": "bin/myOwnGenerator.php"
    
or with parameters:

    "version-persister": {"file": "bin/myOwnGenerator.php", "parameter1": "value1"}


Configuration examples
----------------------
Most of the time, it will be easier for you to pick up and example bellow and to adapt it to your needs.

### No VCS, changelog updater only

```
{
    "version-generator": "semantic",  
    "version-persister": "changelog"
}
```

### Using Git tags, simple versioning and prerequisites
```
{
    "vcs": "git",
    "version-generator": "simple",  
    "version-persister": "vcs-tag",  
    "prerequisites": [
    	"working-copy-check",
    	"display-last-changes"
    ]
}
```

### Using Git tags with prefix, semantic versioning and pushing automatically
```
{
    "vcs": "git",
    "version-generator": "semantic",  
    "version-persister": {
        "type" : "vcs-tag",
        "prefix" : "v_"
    },
    "post-release-actions": [
       "vcs-publish"
    ],
}
```
### Using semantic versioning on master and simple versioning on topic branches
```
{
  "vcs": "git",
  "prerequisites": ["working-copy-check"],
  "version-generator": "simple",
  "version-persister": {"name": "vcs-tag","tag-prefix": "{branch-name}_"},
  "post-release-actions": ["vcs-publish"],
  "branch-specific" : {
    "master": {
      "version-generator": "semantic",
      "version-persister": "vcs-tag",
      "prerequisites": ["working-copy-check", "display-last-changes"],
      "pre-release-actions": [
        {
          "name": "changelog-update",
          "format": "semantic"
        },
        "vcs-commit"
      ]
    }
  }
}
```

Contributing
------------
If you would like to help, to submit one of your action script or just to report a bug: just go on the project page: https://github.com/liip/RMT

Requirements
------------

PHP 5.2
Composer

Authors
-------

Laurent Prodon Liip AG
David Jeanmonod Liip AG

License
-------

RMT is licensed under the MIT License - see the LICENSE file for details
