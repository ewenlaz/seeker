<?php
namespace Seeker\Standard;

interface ConnectionInterface
{
	public function send($data);
	public function close();
}