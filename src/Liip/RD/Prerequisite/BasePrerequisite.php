<?php

namespace Liip\RD\Prerequisite;

abstract class BasePrerequisite {

    abstract public function execute($context);

    abstract public function getOptions();
}