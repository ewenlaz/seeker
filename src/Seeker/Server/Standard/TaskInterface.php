<?php
namespace Seeker\Server\Standard;

interface TaskInterface
{
	public function onTask();
	public function onPipeMessage();
}
