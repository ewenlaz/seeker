<?php
namespace Seeker\Standard;

interface ConnectionInterface
{
    const AUTHED_GUEST = 0;
    const AUTHED_SERVICE = 1;
    const AUTHED_NODE = 2;
    const AUTHED_HARBOR = 4;
    const AUTHED_MASTER = 8;
    const AUTHED_TOOL = 16;

    public function send($data);
    public function close();
    public function setAuthed($flag);
    public function getAuthed();
}