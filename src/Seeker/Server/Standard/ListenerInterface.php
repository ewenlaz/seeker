<?php
namespace Seeker\Server\Standard;

interface ListenerInterface
{
    const OPTION_OPEN_LENGTH_CHECK = 'open_length_check';
    const OPTION_PACKAGE_LENGTH_TYPE = 'package_length_type';
    const OPTION_PACKAGE_LENGTH_OFFSET = 'package_length_offset';
    const OPTION_PACKAGE_BODY_OFFSET = 'package_body_offset';
    const OPTION_PACKAGE_MAX_LENGTH = 'package_max_length';

    public function getHost();
    public function getPort();
    public function getType();
    public function getWorker();
    public function getSetting();
}