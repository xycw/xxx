<?php
class message_stack {
	private $_messages;
	function __construct()
	{
		$this->_messages = array();
		if (isset($_SESSION['messages']) && $_SESSION['messages']) {
			$this->messages = $_SESSION['messages'];
			$_SESSION['messages']= '';
    	}
	}
	
	function add($class, $message, $type='error')
	{
		$message = trim($message);
		if (strlen($message) > 0) {
			switch ($type) {
				case 'error':
					$message = '<p class="error-msg">' . $message . '</p>';
					$this->messages[$class][] = $message;
				break;
				case 'success':
					$message = '<p class="success-msg">' . $message . '</p>';
					$this->messages[$class][] = $message;
				break;
				case 'warning':
					$message = '<p class="warning-msg">' . $message . '</p>';
					$this->messages[$class][] = $message;
				break;
				case 'note':
					$message = '<p class="note-msg">' . $message . '</p>';
					$this->messages[$class][] = $message;
				break;
				default:
					$message = '<p class="error-msg">' . $message . '</p>';
					$this->messages[$class][] = $message;
				break;
			}
		}
	}
	
	function add_session($class, $message, $type='error') {
		$message = trim($message);
		if (strlen($message) > 0) {
			switch ($type) {
				case 'error':
					$message = '<p class="error-msg">' . $message . '</p>';
					$_SESSION['messages'][$class][] = $message;
				break;
				case 'success':
					$message = '<p class="success-msg">' . $message . '</p>';
					$_SESSION['messages'][$class][] = $message;
				break;
				case 'warning':
					$message = '<p class="warning-msg">' . $message . '</p>';
					$_SESSION['messages'][$class][] = $message;
				break;
				case 'note':
					$message = '<p class="note-msg">' . $message . '</p>';
					$_SESSION['messages'][$class][] = $message;
				break;
				default:
					$message = '<p class="error-msg">' . $message . '</p>';
					$_SESSION['messages'][$class][] = $message;
				break;
			}
		}
	}
	
	function reset()
	{
		$this->messages = array();
	}
	
	function output($class)
	{
		$output = '<div class="messages">';
		foreach ($this->messages[$class] as $messages) {
			$output .= $messages;
		}
		$output .= '</div>';
		return $output;
	}
	
	function size($class)
	{
		$count = 0;
		if (isset($this->messages[$class])) {
			$count = count($this->messages[$class]);
		}
		return $count;
	}
}
