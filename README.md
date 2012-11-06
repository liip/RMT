RMT - Release Management Tool
=============================

[![Build Status](https://secure.travis-ci.org/liip/RMT.png?branch=master)](http://travis-ci.org/liip/RMT)

Installation / Usage
--------------------

## 1. Clone the project:   
`git clone git@github.com:liip/RMT.git`
and move it to your vendor/external directory

## 2. Run the following command to init your release configuration 
`vendor/RMT/command.php init`

## 3. Preferences
Answer the asked questions: 
* Choose your favorite persistance method (vcs or changelog), see the doc
* Choose your favorite version generation method (simple or semantic), see the doc

## 4. Run your first RD command
`RD release`
and see your changelog or git tag updated!

## 5. Where to go from here
* edit the rd.json file that you find at the root of your project
 * if you use git as vcs add 2 prerequisites command in the "prerequisites" array:
`"prerequisites": [
   "working-copy-check",
   "display-last-changes"
]`  
This will check that your local repository is clean before anything and display your last changes
 * if you use git as vcs add this post release action  
`"post-release-actions": [
   "vcs-publish"
]`  
This will push your new tag

## 6. Custom actions
You can define your own actions by creating a new php class which extends Liip/RD/Action


Configuration
-------------

## Available commands

### RD release
update your changelog/vcs tag using the method defined in your config as "version-generator"

### RD version
reads current version

### RD init
interactive command to create the base configuration for your project

## Existing Prerequisite actions

### working-copy-check
check that you don't have any local changes before tagging  
* only available for version-persister = vcs

### display-last-changes
display your last changes before tagging  
* only available for version-persister = vcs

## Post release actions

### vcs-publish
push your new tag  
* only available for version-persister = vcs


Example
-------

## Configuration examples

### Using a changelog, and the simple method
* changelog is updated at each new version, following the "simple" pattern: 1, 2, 3, etc...
```
{
    "version-generator": "simple",  
    "version-persister": "changelog",  
    "prerequisites": [],  
    "post-release-actions": [],
}
```

### Using Git tags, and the semantic method
* 1 tag created for each new version, following the pattern "major.minor.draft": 1.0.0, 1.1.0, etc.. 
```
{
    "version-generator": "semantic",  
    "version-persister": "vcs",  
    "prerequisites": [],  
    "post-release-actions": [],
    "vcs": "git"
}
```

### Using Git tags, the semantic method with prefix, and pushing the tag automatically
* 1 tag created for each new version, using a prefix: v1.0.0, v1.1.0, etc..
```
{
    "version-generator": "semantic",  
    "version-persister": {
        "type" : "vcs",
        "prefix" : "v"
    },
    "prerequisites": [],  
    "post-release-actions": [
       "vcs-publish"
    ],
    "vcs": "git"
}
```
Note that each parameter can be either a String (like "vcs") either an object (like {type: vcs, prefix: v}) if you need to specify option to the constructor of the class

### Using Git tags, the semantic method, and adding some prerequisites
* 1 tag created for each new version, 1.0.0, v1.1.0, etc..
* displays your last changes
* check that your repo has no local change before tagging
```
{
    "version-generator": "semantic",  
    "version-persister": "vcs"
    "prerequisites": [
        "working-copy-check",
        "display-last-changes"
    ],  
    "post-release-actions": [],
    "vcs": "git"
}
```

See all pre and post actions in the [commands and options page](Options)

Contributing
------------

Requirements
------------

PHP 5.3+

Authors
-------

Laurent Prodon Liip AG
David Jeanmonod Liip AG

License
-------

RMT is licensed under the MIT License - see the LICENSE file for details
