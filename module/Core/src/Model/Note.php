<?php
/**
 * © Anton Zelenski 2012
 * zelibobla@gmail.com
 *
 */

namespace Core\Model;

class Note extends Entity {

	protected $subject;
	protected $body;
	protected $class = self::NEUTRAL_CLASS;
	protected $is_pinned = 0;
	protected $is_active = 1;

	const ERROR_CLASS = 'error';
	const WARNING_CLASS = 'warning';
	const NEUTRAL_CLASS = 'neutral';
	const SUCCESS_CLASS = 'success';

}